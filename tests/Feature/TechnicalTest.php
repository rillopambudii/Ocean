<?php

namespace Tests\Feature;

use App\Models\DamageReport;
use App\Models\MasterLocation;
use App\Models\User;
use App\Models\VesselSurvey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TechnicalTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'Super Admin']);
    }

    private function vessel(): MasterLocation
    {
        return MasterLocation::create([
            'area' => 'Kalimantan',
            'location' => 'Balikpapan',
            'name' => 'OPS AVIOR',
            'type' => 'Vessel',
        ]);
    }

    public function test_survey_status(): void
    {
        $survey = fn (array $attributes) => new VesselSurvey($attributes);

        $this->assertSame('Selesai', $survey(['planned_date' => today()->subYear(), 'actual_date' => today()])->status());
        $this->assertSame('Terlambat', $survey(['planned_date' => today()->subDay()])->status());
        $this->assertSame('Segera', $survey(['planned_date' => today()->addDays(30)])->status());
        $this->assertSame('Terjadwal', $survey(['planned_date' => today()->addDays(31)])->status());
    }

    public function test_super_admin_can_schedule_survey(): void
    {
        $admin = $this->admin();
        $vessel = $this->vessel();

        $this->actingAs($admin)->post('/admin/vessel-surveys', [
            'master_location_id' => $vessel->id,
            'survey_type' => 'Docking',
            'planned_date' => today()->subDays(3)->toDateString(),
        ])->assertRedirect('/admin/vessel-surveys');

        $this->actingAs($admin)->get('/admin/vessel-surveys')
            ->assertOk()
            ->assertSee('OPS AVIOR')
            ->assertSee('Terlambat')
            ->assertSee('1 jadwal terlambat');
    }

    public function test_survey_type_must_be_known(): void
    {
        $this->actingAs($this->admin())->post('/admin/vessel-surveys', [
            'master_location_id' => $this->vessel()->id,
            'survey_type' => 'Asal',
            'planned_date' => '2026-11-01',
        ])->assertSessionHasErrors('survey_type');
    }

    public function test_closing_damage_report_defaults_closed_date_and_reopening_clears_it(): void
    {
        $admin = $this->admin();
        $vessel = $this->vessel();

        $payload = [
            'master_location_id' => $vessel->id,
            'reported_date' => today()->subDays(2)->toDateString(),
            'title' => 'Genset 2 bocor oli',
            'priority' => 'High',
            'status' => 'Open',
        ];

        $this->actingAs($admin)->post('/admin/damage-reports', $payload)->assertRedirect('/admin/damage-reports');

        $report = DamageReport::firstOrFail();
        $this->assertNull($report->closed_date);

        $this->actingAs($admin)->put("/admin/damage-reports/{$report->id}", ['status' => 'Closed'] + $payload);
        $this->assertTrue($report->fresh()->closed_date->isToday());

        $this->actingAs($admin)->put("/admin/damage-reports/{$report->id}", $payload);
        $this->assertNull($report->fresh()->closed_date);

        $this->actingAs($admin)->get('/admin/damage-reports')
            ->assertOk()
            ->assertSee('Genset 2 bocor oli');
    }

    public function test_non_super_admin_is_forbidden(): void
    {
        $user = User::factory()->create(['role' => 'Admin Operation']);

        $this->actingAs($user)->get('/admin/vessel-surveys')->assertForbidden();
        $this->actingAs($user)->get('/admin/damage-reports')->assertForbidden();
    }
}
