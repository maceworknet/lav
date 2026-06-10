<?php

namespace Tests\Feature;

use App\Models\DeliveryZone;
use App\Models\DeliveryNeighborhood;
use App\Models\DeliveryFeeCampaign;
use App\Services\DeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryCampaignTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_delivery_fee_calculations_and_campaigns(): void
    {
        $deliveryService = app(DeliveryService::class);

        // 1. Create Zone and Neighborhoods using unique names
        $zone = DeliveryZone::create([
            'city' => 'Diyarbakır',
            'district' => 'Sur-Test',
            'base_delivery_fee' => 100.00,
            'is_active' => true
        ]);

        $neighborhoodA = DeliveryNeighborhood::create([
            'delivery_zone_id' => $zone->id,
            'name' => 'Dokuzçeltik Mah. Test',
            'delivery_fee' => 150.00,
            'is_active' => true
        ]);

        $neighborhoodB = DeliveryNeighborhood::create([
            'delivery_zone_id' => $zone->id,
            'name' => 'Cami Kebir Mah. Test',
            'delivery_fee' => 0.00, // should fallback to zone base fee (100.00)
            'is_active' => true
        ]);

        // 2. Test neighborhood fee overriding zone fee, and fallback
        $feeA = $deliveryService->calculateDeliveryFee($neighborhoodA->id, 500.00);
        $this->assertEquals(150.00, $feeA['fee']);

        $feeB = $deliveryService->calculateDeliveryFee($neighborhoodB->id, 500.00);
        $this->assertEquals(100.00, $feeB['fee']);

        // 3. Create Free Delivery Campaign above 3000 TL
        $campaignFree = DeliveryFeeCampaign::create([
            'name' => '3000 TL Üzeri Ücretsiz Teslimat',
            'delivery_zone_id' => $zone->id,
            'type' => 'free_delivery',
            'min_cart_total' => 3000.00,
            'is_active' => true
        ]);

        // Under min cart total
        $calcUnder = $deliveryService->calculateDeliveryFee($neighborhoodA->id, 2500.00);
        $this->assertEquals(150.00, $calcUnder['fee']);
        $this->assertEquals(500.00, $calcUnder['remaining_amount_for_campaign']);
        $this->assertFalse($calcUnder['campaign_applied']);

        // Over min cart total
        $calcOver = $deliveryService->calculateDeliveryFee($neighborhoodA->id, 3200.00);
        $this->assertEquals(0.00, $calcOver['fee']);
        $this->assertEquals(150.00, $calcOver['discount_amount']);
        $this->assertTrue($calcOver['campaign_applied']);

        // 4. Create Fixed Delivery Fee Campaign above 1000 TL (sets delivery fee to 30 TL)
        $campaignFixed = DeliveryFeeCampaign::create([
            'name' => '1000 TL Üzeri Sabit Kurye Ücreti',
            'delivery_zone_id' => $zone->id,
            'type' => 'fixed_fee',
            'min_cart_total' => 1000.00,
            'fixed_delivery_fee' => 30.00,
            'is_active' => true
        ]);

        $calcFixed = $deliveryService->calculateDeliveryFee($neighborhoodA->id, 1500.00);
        $this->assertEquals(30.00, $calcFixed['fee']);
        $this->assertEquals(120.00, $calcFixed['discount_amount']); // 150 - 30
        $this->assertTrue($calcFixed['campaign_applied']);

        // 5. Create Percentage Discount Delivery Campaign above 500 TL (50% off)
        $campaignDiscount = DeliveryFeeCampaign::create([
            'name' => '500 TL Üzeri %50 Kurye İndirimi',
            'delivery_zone_id' => $zone->id,
            'type' => 'discount',
            'min_cart_total' => 500.00,
            'discount_type' => 'percent',
            'discount_value' => 50.00,
            'is_active' => true
        ]);

        $calcDiscount = $deliveryService->calculateDeliveryFee($neighborhoodA->id, 600.00);
        $this->assertEquals(75.00, $calcDiscount['fee']); // 150 * 50%
        $this->assertEquals(75.00, $calcDiscount['discount_amount']);
        $this->assertTrue($calcDiscount['campaign_applied']);
    }
}
