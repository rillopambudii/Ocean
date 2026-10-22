<?php

namespace Tests\Feature;

use App\Models\ActionItem;
use App\Models\MasterLocation;
use App\Models\User;
use App\Models\VesselContract;
use App\Models\VesselSurvey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationTrackerTest extends TestCase
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

    private function user(string $role): User
    {
        return User::factory()->create(['role' => $role]);
    }

    private function action(array $overrides = []): ActionItem
    {
        return ActionItem::create(array_merge([
            'master_location_id' => $this->vessel->id,
            'title' => 'Docking material follow-up',
            'department' => 'Technical',
            'supporting_department' => 'Purchasing',
            'priority' => 'High',
            'target_date' => today()->subDay(),
            'status' => 'Open',
        ], $overrides));
    }

    public function test_department_views_show_led_and_supported_actions(): void
    {
        $this->action();
        $this->action(['title' => 'Certificate document follow-up', 'department' => 'Operation', 'supporting_department' => null]);

        $user = $this->user('Admin Operation');

        $this->actingAs($user)->get('/admin/follow-up')
            ->assertOk()
            ->assertSee('Weekly Action Tracker')
            ->assertSee('Docking material follow-up')
            ->assertSee('Certificate document follow-up');

        // Purchasing hanya mendukung aksi Technical.
        $this->actingAs($user)->get('/admin/follow-up/purchasing')
            ->assertOk()
            ->assertSee('Procurement Follow-up')
            ->assertSee('Docking material follow-up')
            ->assertDontSee('Certificate document follow-up');

        $this->actingAs($user)->get('/admin/follow-up/operation')
            ->assertOk()
            ->assertSee('Certificate document follow-up')
            ->assertDontSee('Docking material follow-up')
            ->assertSee('1 action belum selesai');

        $this->actingAs($user)->get('/admin/follow-up/unknown')->assertNotFound();
    }

    public function test_create_returns_to_department_view_and_closing_sets_date(): void
    {
        $user = $this->user('Admin QHSE');

        $this->actingAs($user)->get('/admin/action-items/create?back=/admin/follow-up/technical')
            ->assertOk()
            ->assertViewHas('defaultDepartment', 'Technical');

        $payload = [
            'back' => '/admin/follow-up/technical',
            'master_location_id' => $this->vessel->id,
            'title' => 'Equipment inspection & repair',
            'department' => 'Technical',
            'priority' => 'Medium',
            'status' => 'Open',
        ];

        $this->actingAs($user)->post('/admin/action-items', $payload)->assertRedirect('/admin/follow-up/technical');

        $action = ActionItem::firstOrFail();
        $this->assertSame(sprintf('TEC-%04d', $action->id), $action->code());

        $this->actingAs($user)->put("/admin/action-items/{$action->id}", ['status' => 'Closed'] + $payload);
        $this->assertTrue($action->fresh()->actual_date->isToday());
    }

    public function test_back_url_cannot_redirect_elsewhere(): void
    {
        $this->actingAs($this->user('Admin QHSE'))->post('/admin/action-items', [
            'back' => 'https://evil.example.com',
            'master_location_id' => $this->vessel->id,
            'title' => 'x',
            'department' => 'QHSE',
            'priority' => 'Low',
            'status' => 'Open',
        ])->assertRedirect('/admin/follow-up');
    }

    public function test_supporting_department_must_differ_from_lead(): void
    {
        $this->actingAs($this->user('Admin QHSE'))->post('/admin/action-items', [
            'master_location_id' => $this->vessel->id,
            'title' => 'x',
            'department' => 'QHSE',
            'supporting_department' => 'QHSE',
            'priority' => 'Low',
            'status' => 'Open',
        ])->assertSessionHasErrors('supporting_department');
    }

    public function test_contract_status_boundaries(): void
    {
        $contract = fn (string $start, int $daysToEnd) => new VesselContract([
            'start_date' => $start,
            'end_date' => today()->addDays($daysToEnd),
        ]);

        $past = today()->subMonth()->toDateString();

        $this->assertSame('Expired', $contract($past, -1)->status());
        $this->assertSame('Due ≤30 days', $contract($past, 30)->status());
        $this->assertSame('Due 31–90 days', $contract($past, 90)->status());
        $this->assertSame('Active', $contract($past, 91)->status());
        $this->assertSame('Upcoming', $contract(today()->addDay()->toDateString(), 200)->status());
    }

    public function test_hire_status_summary_uses_current_next_contract_and_docking(): void
    {
        $user = $this->user('Admin Operation');

        $this->actingAs($user)->post('/admin/vessel-contracts', [
            'master_location_id' => $this->vessel->id,
            'charterer' => 'Charterer A',
            'start_date' => today()->subMonth()->toDateString(),
            'end_date' => today()->addDays(20)->toDateString(),
        ])->assertRedirect('/admin/vessel-contracts');

        VesselContract::create([
            'master_location_id' => $this->vessel->id,
            'charterer' => 'Charterer B',
            'start_date' => today()->addMonth(),
            'end_date' => today()->addYear(),
        ]);

        VesselSurvey::create([
            'master_location_id' => $this->vessel->id,
            'survey_type' => 'Docking',
            'planned_date' => '2026-12-15',
        ]);

        $response = $this->actingAs($user)->get('/admin/vessel-contracts')->assertOk();

        $row = $response->viewData('fleet')->first();
        $this->assertSame('Charterer A', $row['current']->charterer);
        $this->assertSame('Charterer B', $row['next']->charterer);
        $response->assertSee('On hire')->assertSee('Due ≤30 days')->assertSee('15-12-2026');
    }

    public function test_admin_qhse_cannot_manage_contracts(): void
    {
        $this->actingAs($this->user('Admin QHSE'))->get('/admin/vessel-contracts')->assertForbidden();
    }
}
