<?php

namespace Tests\Feature;

use App\Models\AssuranceProgram;
use App\Models\MasterLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AssuranceProgramTest extends TestCase
{
    use RefreshDatabase;

    private User $qhse;

    private MasterLocation $vessel;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $this->qhse = User::factory()->create(['role' => 'Admin QHSE']);

        $this->vessel = MasterLocation::create([
            'area' => 'Kalimantan',
            'location' => 'Balikpapan',
            'name' => 'OPS AORA',
            'type' => 'Vessel',
        ]);
    }

    public function test_status_and_days_to_due(): void
    {
        $program = fn (array $attributes) => new AssuranceProgram($attributes);

        $overdue = $program(['planned_date' => today()->subDays(35)]);
        $this->assertSame(-35, $overdue->daysToDue());
        $this->assertSame('Overdue', $overdue->status());

        $this->assertSame('Done', $program(['planned_date' => today()->subDays(35), 'actual_date' => today()])->status());
        $this->assertSame('Due Soon', $program(['planned_date' => today()->addDays(30)])->status());
        $this->assertSame('Open', $program(['planned_date' => today()->addDays(31)])->status());
    }

    public function test_admin_qhse_can_record_assurance_with_evidence(): void
    {
        $this->actingAs($this->qhse)->post('/admin/assurance-programs', [
            'master_location_id' => $this->vessel->id,
            'assurance_type' => 'ISM Code SMC',
            'planned_date' => today()->subDays(3)->toDateString(),
            'remarks' => 'Due for intermediate, 3rd anniversary',
            'nc_open' => 2,
            'nc_closed' => '',
            'evidence' => UploadedFile::fake()->create('smc.pdf', 50, 'application/pdf'),
        ])->assertRedirect('/admin/assurance-programs');

        $program = AssuranceProgram::firstOrFail();
        $this->assertSame(0, $program->nc_closed);
        Storage::disk('local')->assertExists($program->evidence_path);

        $this->actingAs($this->qhse)->get('/admin/assurance-programs')
            ->assertOk()
            ->assertSee('OPS AORA')
            ->assertSee('ISM Code SMC')
            ->assertSee('1 assurance overdue')
            ->assertSee('Total NC open: 2');

        $this->actingAs($this->qhse)->get("/admin/assurance-programs/{$program->id}/evidence")->assertOk();
    }

    public function test_status_filter(): void
    {
        AssuranceProgram::create(['master_location_id' => $this->vessel->id, 'assurance_type' => 'Internal Audit', 'planned_date' => today()->subDay(), 'remarks' => 'terlambat']);
        AssuranceProgram::create(['master_location_id' => $this->vessel->id, 'assurance_type' => 'Internal Audit', 'planned_date' => today()->subDay(), 'actual_date' => today(), 'remarks' => 'sudah selesai']);

        $this->actingAs($this->qhse)->get('/admin/assurance-programs?status=Done')
            ->assertOk()
            ->assertSee('sudah selesai')
            ->assertDontSee('terlambat');
    }

    public function test_unknown_type_is_rejected(): void
    {
        $this->actingAs($this->qhse)->post('/admin/assurance-programs', [
            'master_location_id' => $this->vessel->id,
            'assurance_type' => 'Asal',
            'planned_date' => '2026-11-01',
        ])->assertSessionHasErrors('assurance_type');
    }

    public function test_admin_operation_is_forbidden(): void
    {
        $user = User::factory()->create(['role' => 'Admin Operation']);

        $this->actingAs($user)->get('/admin/assurance-programs')->assertForbidden();
    }
}
