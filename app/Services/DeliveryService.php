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
     * Calculate delivery fee for a specific neighborhood and subtotal.
     */
    public function calculateDeliveryFee(int $neighborhoodId, float $subtotal): float
    {
        $neighborhood = DeliveryNeighborhood::find($neighborhoodId);

        if (!$neighborhood || !$neighborhood->is_active) {
            return 0.00;
        }

        // Check if order subtotal qualifies for free delivery
        if (!is_null($neighborhood->free_delivery_threshold) && $subtotal >= (float) $neighborhood->free_delivery_threshold) {
            return 0.00;
        }

        return (float) $neighborhood->delivery_fee;
    }

    /**
     * Get available delivery slots for a specific date.
     */
    public function getAvailableSlotsForDate(string $dateString): array
    {
        $targetDate = Carbon::parse($dateString)->startOfDay();
        $today = Carbon::today();

        // 1. If target date is in the past, no slots are available
        if ($targetDate->lt($today)) {
            return [];
        }

        $allSlots = DeliverySlot::where('is_active', true)->orderBy('start_time')->get();
        $availableSlots = [];

        foreach ($allSlots as $slot) {
            $isAvailable = true;

            // 2. Cutoff time check for same-day delivery
            if ($targetDate->equalTo($today) && $slot->cutoff_time) {
                $currentTime = Carbon::now('Europe/Istanbul');
                $cutoffTime = Carbon::parse($slot->cutoff_time);
                
                // Set cutoff date/time to today for comparison
                $cutoffDateTime = Carbon::today()->setTime($cutoffTime->hour, $cutoffTime->minute, $cutoffTime->second);

                if ($currentTime->gt($cutoffDateTime)) {
                    $isAvailable = false; // Past cutoff time for today
                }
            }

            // 3. Capacity check
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
