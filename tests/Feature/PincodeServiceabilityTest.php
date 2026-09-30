<?php

namespace Tests\Feature;

use App\Models\ServiceablePincode;
use App\Models\User;
use App\Services\Pincode\PincodeService;
use App\Services\Shipping\CourierServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PincodeServiceabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_rejects_invalid_pincode_format_with_422(): void
    {
        // Single digit, letters, too short, too long — all invalid
        $invalidPincodes = ['0', 'ABCDEF', '12345', '1234567', '012345', ''];

        foreach ($invalidPincodes as $pin) {
            $res = $this->getJson('/api/pincode/check?pincode=' . urlencode($pin));
            $res->assertStatus(422);
            $res->assertJson([
                'success'     => false,
                'serviceable' => false,
            ]);
        }
    }

    /** @test */
    public function it_returns_cached_result_from_serviceable_pincodes_table(): void
    {
        // Seed a fresh cached entry (last checked 1 day ago — within the 7-day TTL)
        ServiceablePincode::create([
            'pincode'          => '600001',
            'city'             => 'Chennai',
            'state'            => 'Tamil Nadu',
            'is_serviceable'   => true,
            'is_cod_available' => true,
            'estimated_days'   => 3,
            'courier_name'     => 'Delhivery Surface',
            'last_checked_at'  => now()->subDay(),
        ]);

        // Mock courier so if it IS called the test would blow up (ensuring cache is used)
        $mockCourier = Mockery::mock(CourierServiceInterface::class);
        $mockCourier->shouldNotReceive('checkServiceability');
        $this->app->instance(CourierServiceInterface::class, $mockCourier);

        $res = $this->getJson('/api/pincode/check?pincode=600001');

        $res->assertStatus(200);
        $res->assertJson([
            'success'        => true,
            'pincode'        => '600001',
            'serviceable'    => true,
            'cod_available'  => true,
            'estimated_days' => 3,
            'city'           => 'Chennai',
            'state'          => 'Tamil Nadu',
            'courier_name'   => 'Delhivery Surface',
        ]);
        $res->assertJsonStructure(['eta_date', 'message']);
    }

    /** @test */
    public function it_falls_back_gracefully_when_courier_api_is_unreachable(): void
    {
        // No cached entry, courier API throws an exception
        $mockCourier = Mockery::mock(CourierServiceInterface::class);
        $mockCourier->shouldReceive('checkServiceability')
            ->once()
            ->andThrow(new \RuntimeException('Shiprocket API unavailable'));
        $this->app->instance(CourierServiceInterface::class, $mockCourier);

        $res = $this->getJson('/api/pincode/check?pincode=110001');

        // Fallback returns success with 4-day default ETA
        $res->assertStatus(200);
        $res->assertJson([
            'success'        => true,
            'serviceable'    => true,
            'cod_available'  => true,
            'estimated_days' => 4,
        ]);
    }

    /** @test */
    public function it_returns_cod_availability_toggle_from_courier_api(): void
    {
        $mockCourier = Mockery::mock(CourierServiceInterface::class);
        $mockCourier->shouldReceive('checkServiceability')
            ->once()
            ->andReturn([
                'serviceable'    => true,
                'cod_available'  => false,   // COD not available
                'estimated_days' => 5,
                'courier_name'   => 'BlueDart Air',
                'city'           => 'Mumbai',
                'state'          => 'Maharashtra',
            ]);
        $this->app->instance(CourierServiceInterface::class, $mockCourier);

        $res = $this->getJson('/api/pincode/check?pincode=400001');

        $res->assertStatus(200);
        $res->assertJson([
            'success'        => true,
            'serviceable'    => true,
            'cod_available'  => false,
            'estimated_days' => 5,
            'courier_name'   => 'BlueDart Air',
        ]);

        // Verify it was cached in the DB
        $this->assertDatabaseHas('serviceable_pincodes', [
            'pincode'          => '400001',
            'is_cod_available' => false,
            'is_serviceable'   => true,
        ]);
    }
}
