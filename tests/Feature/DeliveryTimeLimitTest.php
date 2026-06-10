<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\DeliverySlot;
use App\Models\DeliveryZone;
use App\Models\DeliveryNeighborhood;
use App\Services\DeliveryService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryTimeLimitTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_delivery_slots_respect_prep_time_and_cutoff(): void
    {
        $deliveryService = app(DeliveryService::class);

        // 1. Setup default settings
        Setting::updateOrCreate(['key' => 'timezone'], ['value' => 'Europe/Istanbul', 'group' => 'ecommerce']);
        Setting::updateOrCreate(['key' => 'prep_time_value'], ['value' => '120', 'group' => 'ecommerce']);
        Setting::updateOrCreate(['key' => 'prep_time_unit'], ['value' => 'minutes', 'group' => 'ecommerce']);
        Setting::updateOrCreate(['key' => 'same_day_delivery_active'], ['value' => 'true', 'group' => 'ecommerce']);
        Setting::updateOrCreate(['key' => 'delivery_cutoff_time'], ['value' => '23:59', 'group' => 'ecommerce']);

        // 2. Setup mock delivery slots in DB
        DeliverySlot::query()->delete();
        $slot1 = DeliverySlot::create(['name' => '09:00 - 12:00', 'start_time' => '09:00', 'end_time' => '12:00', 'capacity' => 10, 'is_active' => true]);
        $slot2 = DeliverySlot::create(['name' => '12:00 - 15:00', 'start_time' => '12:00', 'end_time' => '15:00', 'capacity' => 10, 'is_active' => true]);
        $slot3 = DeliverySlot::create(['name' => '15:00 - 18:00', 'start_time' => '15:00', 'end_time' => '18:00', 'capacity' => 10, 'is_active' => true]);
        $slot4 = DeliverySlot::create(['name' => '18:00 - 21:00', 'start_time' => '18:00', 'end_time' => '21:00', 'capacity' => 10, 'is_active' => true]);

        // 3. Mock the current time to Europe/Istanbul 13:30
        Carbon::setTestNow(Carbon::create(date('Y'), date('m'), date('d'), 13, 30, 0, 'Europe/Istanbul'));

        // Current time: 13:30. Prep time: 2 hours. Limit: 15:30.
        // Slot 1 (09:00) starts before 15:30 -> Not available
        // Slot 2 (12:00) starts before 15:30 -> Not available
        // Slot 3 (15:00) starts before 15:30 -> Not available
        // Slot 4 (18:00) starts after 15:30 -> Available

        $slots = $deliveryService->getAvailableSlotsForDate(date('Y-m-d'));

        $slotsByName = collect($slots)->keyBy('name');

        $this->assertFalse($slotsByName['09:00 - 12:00']['is_available']);
        $this->assertFalse($slotsByName['12:00 - 15:00']['is_available']);
        $this->assertFalse($slotsByName['15:00 - 18:00']['is_available']);
        $this->assertTrue($slotsByName['18:00 - 21:00']['is_available']);

        // 4. Check validation
        $zone = DeliveryZone::create(['city' => 'Diyarbakır', 'district' => 'Sur-Test', 'is_active' => true]);
        $neighborhood = DeliveryNeighborhood::create(['delivery_zone_id' => $zone->id, 'name' => 'Cami Kebir Mah. Test', 'is_active' => true]);

        // Validate slot 1 (should fail)
        $val1 = $deliveryService->validateDelivery('Sur-Test', 'Cami Kebir Mah. Test', date('Y-m-d'), '09:00 - 12:00');
        $this->assertFalse($val1['valid']);

        // Validate slot 4 (should pass)
        $val4 = $deliveryService->validateDelivery('Sur-Test', 'Cami Kebir Mah. Test', date('Y-m-d'), '18:00 - 21:00');
        $this->assertTrue($val4['valid']);

        // Clean up mock time
        Carbon::setTestNow();
    }
}
