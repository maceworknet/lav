<?php

namespace App\Services;

use App\Models\DeliveryZone;
use App\Models\DeliveryNeighborhood;
use App\Models\DeliverySlot;
use App\Models\Order;
use Carbon\Carbon;

class DeliveryService
{
    /**
     * Get all active delivery zones and their active neighborhoods.
     */
    public function getActiveZones(): \Illuminate\Database\Eloquent\Collection
    {
        return DeliveryZone::where('is_active', true)
            ->with(['neighborhoods' => function ($query) {
                $query->where('is_active', true)->orderBy('name');
            }])
            ->orderBy('district')
            ->get();
    }

    /**
     * Calculate delivery fee details for a specific neighborhood and subtotal.
     */
    public function calculateDeliveryFee(int $neighborhoodId, float $subtotal): array
    {
        $neighborhood = DeliveryNeighborhood::with('zone')->find($neighborhoodId);

        if (!$neighborhood || !$neighborhood->is_active) {
            return [
                'base_fee' => 0.00,
                'fee' => 0.00,
                'discount_amount' => 0.00,
                'campaign_applied' => false,
                'campaign_name' => null,
                'customer_message' => null,
                'remaining_amount_for_campaign' => 0.00
            ];
        }

        // 1. Get base fee: Neighborhood fee, fallback to Zone base fee
        $baseFee = (float) $neighborhood->delivery_fee;
        if ($baseFee <= 0 && $neighborhood->zone) {
            $baseFee = (float) $neighborhood->zone->base_delivery_fee;
        }

        $finalFee = $baseFee;
        $discountAmount = 0.00;
        $campaignApplied = false;
        $campaignName = null;
        $customerMessage = null;
        $remainingAmountForCampaign = 0.00;

        // 2. Query campaigns that match
        $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
        $timezone = $settings['timezone'] ?? 'Europe/Istanbul';
        $now = Carbon::now($timezone);
        $campaigns = \App\Models\DeliveryFeeCampaign::where('is_active', true)
            ->where(function ($q) use ($neighborhood) {
                $q->where('delivery_neighborhood_id', $neighborhood->id)
                  ->orWhere(function ($sq) use ($neighborhood) {
                      $sq->whereNull('delivery_neighborhood_id')
                         ->where('delivery_zone_id', $neighborhood->delivery_zone_id);
                  })
                  ->orWhere(function ($sq) {
                      $sq->whereNull('delivery_neighborhood_id')
                         ->whereNull('delivery_zone_id');
                  });
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now);
            })
            ->orderBy('min_cart_total', 'desc')
            ->get();

        // Check for applied campaign
        $appliedCampaign = null;
        foreach ($campaigns as $camp) {
            if ($subtotal >= (float) $camp->min_cart_total) {
                $appliedCampaign = $camp;
                break;
            }
        }

        if ($appliedCampaign) {
            $campaignApplied = true;
            $campaignName = $appliedCampaign->name;
            $customerMessage = $appliedCampaign->customer_message;

            if ($appliedCampaign->type === 'free_delivery') {
                $finalFee = 0.00;
                $discountAmount = $baseFee;
            } elseif ($appliedCampaign->type === 'fixed_fee') {
                $fixedFee = (float) $appliedCampaign->fixed_delivery_fee;
                $finalFee = $fixedFee;
                $discountAmount = max(0.00, $baseFee - $fixedFee);
            } elseif ($appliedCampaign->type === 'discount') {
                $val = (float) $appliedCampaign->discount_value;
                if ($appliedCampaign->discount_type === 'percent') {
                    $discountAmount = round(($baseFee * $val) / 100, 2);
                } else {
                    $discountAmount = min($baseFee, $val);
                }
                $finalFee = max(0.00, $baseFee - $discountAmount);
            }
        } else {
            // No campaign met the subtotal requirement. Find the one with the smallest min_cart_total that is > subtotal.
            $nextCampaign = $campaigns->where('min_cart_total', '>', $subtotal)->sortBy('min_cart_total')->first();
            if ($nextCampaign) {
                $remainingAmountForCampaign = (float) $nextCampaign->min_cart_total - $subtotal;
                if ($nextCampaign->customer_message) {
                    $customerMessage = $nextCampaign->customer_message;
                } else {
                    $customerMessage = "Sepetinize " . number_format($remainingAmountForCampaign, 2) . " TL değerinde ürün ekleyerek kurye ücretini avantajlı yapabilirsiniz!";
                }
            }
        }

        // Neighborhood free delivery fallback if no campaign overrode it
        if (!$campaignApplied && !is_null($neighborhood->free_delivery_threshold)) {
            $threshold = (float) $neighborhood->free_delivery_threshold;
            if ($subtotal >= $threshold) {
                $finalFee = 0.00;
                $discountAmount = $baseFee;
                $campaignApplied = true;
                $campaignName = "Mahalle Ücretsiz Teslimat Limiti";
            } else {
                $rem = $threshold - $subtotal;
                if ($remainingAmountForCampaign <= 0 || $rem < $remainingAmountForCampaign) {
                    $remainingAmountForCampaign = $rem;
                    $customerMessage = "Sepetinize " . number_format($rem, 2) . " TL değerinde ürün ekleyerek kurye ücretini ücretsiz yapabilirsiniz!";
                }
            }
        }

        return [
            'base_fee' => $baseFee,
            'fee' => $finalFee,
            'discount_amount' => $discountAmount,
            'campaign_applied' => $campaignApplied,
            'campaign_name' => $campaignName,
            'customer_message' => $customerMessage,
            'remaining_amount_for_campaign' => $remainingAmountForCampaign
        ];
    }

    /**
     * Get available delivery slots for a specific date.
     */
    public function getAvailableSlotsForDate(string $dateString): array
    {
        $settings = \App\Models\Setting::pluck('value', 'key')->toArray();
        $timezone = $settings['timezone'] ?? 'Europe/Istanbul';
        $prepValue = (int) ($settings['prep_time_value'] ?? 120);
        $prepUnit = $settings['prep_time_unit'] ?? 'minutes';
        $sameDayActive = filter_var($settings['same_day_delivery_active'] ?? true, FILTER_VALIDATE_BOOLEAN);
        $cutoffTimeSetting = $settings['delivery_cutoff_time'] ?? '18:00';

        $targetDate = Carbon::parse($dateString)->setTimezone($timezone)->startOfDay();
        $today = Carbon::now($timezone)->startOfDay();

        // 1. If target date is in the past, no slots are available
        if ($targetDate->lt($today)) {
            return [];
        }

        // 2. Same day delivery check
        if (!$sameDayActive && $targetDate->equalTo($today)) {
            return [];
        }

        // 3. Cutoff time check for today
        if ($targetDate->equalTo($today) && $cutoffTimeSetting) {
            $cutoff = Carbon::now($timezone)->setTimeFromTimeString($cutoffTimeSetting);
            if (Carbon::now($timezone)->gt($cutoff)) {
                return [];
            }
        }

        // Calculate preparation time limit
        $prepTimeLimit = Carbon::now($timezone);
        if ($prepUnit === 'minutes') {
            $prepTimeLimit->addMinutes($prepValue);
        } elseif ($prepUnit === 'hours') {
            $prepTimeLimit->addHours($prepValue);
        } elseif ($prepUnit === 'days') {
            $prepTimeLimit->addDays($prepValue);
        }

        $allSlots = DeliverySlot::where('is_active', true)->orderBy('start_time')->get();
        $availableSlots = [];

        foreach ($allSlots as $slot) {
            $isAvailable = true;

            // 4. Cutoff time check for same-day delivery
            if ($targetDate->equalTo($today) && $slot->cutoff_time) {
                $cutoffTime = Carbon::parse($slot->cutoff_time);
                $cutoffDateTime = $today->copy()->setTime($cutoffTime->hour, $cutoffTime->minute, $cutoffTime->second);

                if (Carbon::now($timezone)->gt($cutoffDateTime)) {
                    $isAvailable = false; // Past cutoff time for today
                }
            }

            // 5. Preparation time check
            if ($isAvailable) {
                $slotStartParsed = Carbon::parse($slot->start_time);
                $slotStartDateTime = $targetDate->copy()->setTime($slotStartParsed->hour, $slotStartParsed->minute, $slotStartParsed->second);

                if ($slotStartDateTime->lt($prepTimeLimit)) {
                    $isAvailable = false; // Too close to current time + preparation time
                }
            }

            // 6. Capacity check
            if ($isAvailable) {
                $activeOrdersCount = Order::where('delivery_date', $targetDate->toDateString())
                    ->where('delivery_slot', $slot->name)
                    ->whereNotIn('status', ['pending_payment', 'payment_failed', 'cancelled', 'refunded'])
                    ->count();

                if ($activeOrdersCount >= $slot->capacity) {
                    $isAvailable = false; // Slot is full
                }
            }

            $availableSlots[] = [
                'id' => $slot->id,
                'name' => $slot->name,
                'start_time' => $slot->start_time,
                'end_time' => $slot->end_time,
                'is_available' => $isAvailable,
                'remaining_capacity' => max(0, $slot->capacity - ($activeOrdersCount ?? 0)),
            ];
        }

        return $availableSlots;
    }

    /**
     * Validate all delivery parameters.
     */
    public function validateDelivery(string $district, string $neighborhoodName, string $dateString, string $slotName): array
    {
        // Check zone
        $zone = DeliveryZone::where('district', $district)
            ->where('is_active', true)
            ->first();

        if (!$zone) {
            return ['valid' => false, 'message' => 'Geçersiz veya aktif olmayan teslimat ilçesi.'];
        }

        // Check neighborhood
        $neighborhood = DeliveryNeighborhood::where('delivery_zone_id', $zone->id)
            ->where('name', $neighborhoodName)
            ->where('is_active', true)
            ->first();

        if (!$neighborhood) {
            return ['valid' => false, 'message' => 'Belirtilen mahalle bu ilçe sınırlarında bulunamadı.'];
        }

        // Check date
        $targetDate = Carbon::parse($dateString)->startOfDay();
        if ($targetDate->lt(Carbon::today())) {
            return ['valid' => false, 'message' => 'Teslimat tarihi geçmiş bir tarih olamaz.'];
        }

        // Check slots
        $slots = $this->getAvailableSlotsForDate($dateString);
        $slotFound = false;
        $slotAvailable = false;

        foreach ($slots as $slot) {
            if ($slot['name'] === $slotName) {
                $slotFound = true;
                $slotAvailable = $slot['is_available'];
                break;
            }
        }

        if (!$slotFound) {
            return ['valid' => false, 'message' => 'Belirtilen teslimat saat aralığı mevcut değil.'];
        }

        if (!$slotAvailable) {
            return ['valid' => false, 'message' => 'Seçilen saat aralığı kapasite doluluğu veya sipariş zaman aşımı (cutoff) nedeniyle kapalıdır.'];
        }

        return [
            'valid' => true,
            'neighborhood' => $neighborhood,
            'zone' => $zone
        ];
    }
}
