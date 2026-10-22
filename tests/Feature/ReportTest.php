<?php

namespace Tests\Feature;

use App\Models\Crew;
use App\Models\DamageReport;
use App\Models\MasterLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private MasterLocation $astrid;

    private MasterLocation $bima;

    protected function setUp(): void
    {
        parent::setUp();

        $this->astrid = MasterLocation::create(['area' => 'Kalimantan', 'location' => 'Balikpapan', 'name' => 'OPS ASTRID', 'type' => 'Vessel']);
        $this->bima = MasterLocation::create(['area' => 'Jawa', 'location' => 'Surabaya', 'name' => 'OPS BIMA', 'type' => 'Vessel']);

        foreach ([[$this->astrid, 'Pompa bocor', '2026-03-01'], [$this->bima, 'Genset mati', '2026-05-01'], [$this->astrid, 'Lama', '2024-01-01']] as [$vessel, $title, $date]) {
            DamageReport::create(['master_location_id' => $vessel->id, 'reported_date' => $date, 'title' => $title, 'priority' => 'High', 'status' => 'Open']);
        }
    }

    private function user(string $role, ?MasterLocation $vessel = null): User
    {
        return User::factory()->create(['role' => $role, 'master_location_id' => $vessel?->id]);
    }

    public function test_index_lists_only_reports_the_user_may_open(): void
    {
        $this->actingAs($this->user('Admin Technical'))->get('/admin/laporan')
            ->assertOk()
            ->assertSee('Defect Register')
            ->assertSee('Weekly Action Tracker')
            ->assertDontSee('Crew Register');
    }

    public function test_pdf_respects_vessel_and_year_filters(): void
    {
        $response = $this->actingAs($this->user('Super Admin'))->get('/admin/laporan/export?' . http_build_query([
            'report' => 'damage-reports', 'format' => 'pdf', 'location' => $this->astrid->id, 'year' => 2026,
        ]));

        $response->assertOk()->assertDownload('damage-reports-' . today()->format('Ymd') . '.pdf');
    }

    public function test_rows_are_filtered_and_vessel_admin_is_scoped(): void
    {
        // Baris dicek lewat data view PDF, yang dibangun dari query yang sama dengan Excel.
        $super = $this->user('Super Admin');
        $this->assertSame(['Pompa bocor'], $this->rows($super, ['location' => $this->astrid->id, 'year' => 2026]));
        $this->assertCount(3, $this->rows($super, []));

        // Admin Kapal tetap hanya mendapat kapalnya walau memilih "Semua Kapal".
        $kapal = $this->user('Admin Kapal', $this->astrid);
        $this->assertSame(['Lama', 'Pompa bocor'], $this->rows($kapal, []));
    }

    public function test_excel_download_and_access_rules(): void
    {
        $this->actingAs($this->user('Super Admin'))
            ->get('/admin/laporan/export?report=crews&format=excel')
            ->assertOk()
            ->assertDownload('crews-' . today()->format('Ymd') . '.xlsx');

        $crewing = $this->user('Admin Crewing');
        $this->actingAs($crewing)->get('/admin/laporan/export?report=damage-reports&format=excel')->assertForbidden();
        $this->actingAs($crewing)->get('/admin/laporan/export?report=unknown&format=excel')->assertSessionHasErrors('report');

        $this->actingAs($this->user('Management'))->get('/admin/laporan')->assertForbidden();
    }

    public function test_every_report_exports_with_data(): void
    {
        $vessel = ['master_location_id' => $this->astrid->id];
        $date = '2026-06-01';

        \App\Models\AssuranceProgram::create($vessel + ['assurance_type' => 'Internal Audit', 'planned_date' => $date]);
        \App\Models\SafetyObservation::create($vessel + ['observed_date' => $date, 'category' => 'Near Miss', 'description' => 'Tangga licin', 'status' => 'Open']);
        \App\Models\TrainingActivity::create($vessel + ['activity_type' => 'Emergency Drill', 'topic' => 'Fire drill', 'planned_date' => $date]);
        \App\Models\VesselContract::create($vessel + ['charterer' => 'Pertamina', 'start_date' => $date, 'end_date' => '2027-06-01']);
        \App\Models\VesselCertificate::create($vessel + ['certificate_name' => 'Load Line', 'issued_date' => $date, 'expired_date' => '2027-06-01', 'status' => 'Valid', 'year' => 2026]);
        \App\Models\VesselSurvey::create($vessel + ['survey_type' => 'Docking', 'planned_date' => $date]);
        Crew::create($vessel + ['name' => 'Budi', 'rank' => 'AB', 'sign_on_date' => $date]);
        \App\Models\PurchaseRequest::create($vessel + ['title' => 'Filter oli', 'category' => 'Spare Part', 'request_date' => $date, 'status' => 'Diajukan'])
            ->items()->create(['description' => 'Filter', 'quantity' => 2, 'unit' => 'pcs', 'unit_price' => 50000]);
        \App\Models\ActionItem::create($vessel + ['title' => 'Follow-up', 'department' => 'Technical', 'priority' => 'High', 'target_date' => $date, 'status' => 'Open']);

        $super = $this->user('Super Admin');

        foreach (['assurance-programs', 'safety-observations', 'training-activities', 'vessel-contracts', 'vessel-certificates', 'vessel-surveys', 'damage-reports', 'crews', 'purchase-requests', 'action-items'] as $report) {
            // PDF memakai baris yang sama dan sudah dites di atas; 10 PDF sekaligus terlalu berat memori.
            $this->actingAs($super)->get("/admin/laporan/export?report={$report}&format=excel&year=2026")->assertOk();
        }
    }

    private function rows(User $user, array $query): array
    {
        $rows = null;

        // Tangkap data yang dikirim ke view PDF tanpa mem-parse file PDF.
        view()->composer('admin.reports.pdf', function ($view) use (&$rows) {
            $rows = $view->getData()['rows'];
        });

        $this->actingAs($user)
            ->get('/admin/laporan/export?' . http_build_query($query + ['report' => 'damage-reports', 'format' => 'pdf']))
            ->assertOk();

        return array_column($rows, 2);
    }
}
