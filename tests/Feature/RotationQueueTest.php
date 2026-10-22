<?php

namespace Tests\Feature;

use App\Models\Crew;
use App\Models\MasterLocation;
use App\Models\User;
use App\Models\VesselCertificate;
use App\Models\VesselSurvey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RotationQueueTest extends TestCase
{
    use RefreshDatabase;

    private MasterLocation $vessel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vessel = MasterLocation::create([
            'area' => 'Kalimantan',
            'location' => 'Balikpapan',
            'name' => 'OPS ASTRID',
            'type' => 'Vessel',
        ]);
    }

    private function certificate(string $name, int $days): void
    {
        VesselCertificate::create([
            'master_location_id' => $this->vessel->id,
            'certificate_name' => $name,
            'issued_date' => today()->subYear(),
            'expired_date' => today()->addDays($days),
            'status' => 'Valid',
            'year' => today()->year,
        ]);
    }

    public function test_renewal_queue_orders_and_prioritises_items(): void
    {
        $this->certificate('Load Line', -5);
        $this->certificate('Safety Radio', 60);
        $this->certificate('Far Future', 200);

        VesselSurvey::create([
            'master_location_id' => $this->vessel->id,
            'survey_type' => 'Annual Survey',
            'planned_date' => today()->addDays(10),
        ]);
        VesselSurvey::create([
            'master_location_id' => $this->vessel->id,
            'survey_type' => 'Docking',
            'planned_date' => today()->addDays(5),
            'actual_date' => today(),
        ]);

        $response = $this->actingAs(User::factory()->create(['role' => 'Admin Operation']))
            ->get('/admin/renewal-queue')
            ->assertOk()
            ->assertSeeInOrder(['Load Line', 'Annual Survey', 'Safety Radio'])
            ->assertDontSee('Far Future');

        $queue = $response->viewData('queue');
        $this->assertSame(['Overdue', 'Urgent', 'Plan'], $queue->pluck('priority')->all());
    }

    public function test_crew_rotation_shows_onboard_and_joining_crew_only(): void
    {
        $crew = fn (string $name, ?int $on, ?int $off) => Crew::create([
            'master_location_id' => $this->vessel->id,
            'name' => $name,
            'rank' => 'AB',
            'sign_on_date' => $on === null ? null : today()->addDays($on),
            'sign_off_date' => $off === null ? null : today()->addDays($off),
        ]);

        $crew('Signed Off', -200, -10);
        $crew('Relief Soon', -150, 20);
        $crew('Long Stay', -30, 120);
        $crew('New Joiner', 7, 180);

        $this->actingAs(User::factory()->create(['role' => 'Super Admin']))
            ->get('/admin/crew-rotation')
            ->assertOk()
            ->assertSee('OPS ASTRID')
            ->assertSeeInOrder(['Relief Soon', 'Perlu Relief', 'Long Stay', 'Onboard', 'New Joiner', 'Akan Join'])
            ->assertDontSee('Signed Off');
    }

    public function test_crew_rotation_is_super_admin_only(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'Admin Operation']))
            ->get('/admin/crew-rotation')
            ->assertForbidden();
    }
}
