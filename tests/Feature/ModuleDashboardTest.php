<?php

namespace Tests\Feature;

use App\Models\Crew;
use App\Models\DamageReport;
use App\Models\MasterLocation;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Models\VesselContract;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    public function test_every_module_dashboard_renders_with_data(): void
    {
        $vessel = MasterLocation::create(['area' => 'Kalimantan', 'location' => 'Balikpapan', 'name' => 'OPS ASTRID', 'type' => 'Vessel']);

        VesselContract::create(['master_location_id' => $vessel->id, 'charterer' => 'Pertamina', 'start_date' => today()->subYear(), 'end_date' => today()->addDays(20)]);
        DamageReport::create(['master_location_id' => $vessel->id, 'reported_date' => today(), 'title' => 'Pompa bocor', 'priority' => 'High', 'status' => 'Open']);
        Crew::create(['master_location_id' => $vessel->id, 'name' => 'Budi', 'rank' => 'AB', 'sign_on_date' => today()->subMonths(5), 'sign_off_date' => today()->addDays(10)]);
        PurchaseRequest::create(['master_location_id' => $vessel->id, 'title' => 'Filter oli', 'category' => 'Spare Part', 'request_date' => today(), 'status' => 'Diajukan']);

        $admin = $this->user('Super Admin');

        $expected = [
            'qhse' => 'QHSE Dashboard',
            'operation' => 'Pertamina',
            'technical' => 'Pompa bocor',
            'crewing' => 'Budi',
            'purchasing' => 'Filter oli',
        ];

        foreach ($expected as $module => $text) {
            $this->actingAs($admin)->get("/admin/dashboards/{$module}")->assertOk()->assertSee($text);
        }
    }

    public function test_access_follows_module_role(): void
    {
        $operation = $this->user('Admin Operation');
        $qhse = $this->user('Admin QHSE');

        $this->actingAs($operation)->get('/admin/dashboards/operation')->assertOk();
        $this->actingAs($operation)->get('/admin/dashboards/qhse')->assertForbidden();
        $this->actingAs($operation)->get('/admin/dashboards/crewing')->assertForbidden();

        $this->actingAs($qhse)->get('/admin/dashboards/qhse')->assertOk();
        $this->actingAs($qhse)->get('/admin/dashboards/purchasing')->assertForbidden();

        $this->actingAs($qhse)->get('/admin/dashboards/unknown')->assertNotFound();
    }
}
