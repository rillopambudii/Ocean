<?php

namespace App\Http\Controllers;

use App\Models\ActionItem;
use App\Models\AssuranceProgram;
use App\Models\AuditFinding;
use App\Models\Budget;
use App\Models\Crew;
use App\Models\CrewCertificate;
use App\Models\DamageReport;
use App\Models\PurchaseRequest;
use App\Models\SafetyObservation;
use App\Models\TrainingActivity;
use App\Models\Vendor;
use App\Models\VesselCertificate;
use App\Models\VesselContract;
use App\Models\VesselSurvey;
use Illuminate\Support\Facades\Gate;

/**
 * Dashboard ringkas per modul: kartu KPI + satu daftar yang perlu perhatian.
 * Semua angka dihitung dari data modul yang sudah ada.
 */
class ModuleDashboardController extends Controller
{
    public const GATES = [

        'qhse' => 'qhse-access',

        'operation' => 'operation-access',

        'technical' => 'super-admin',

        'crewing' => 'super-admin',

        'purchasing' => 'super-admin',
    ];

    public function show(string $module)
    {
        Gate::authorize(self::GATES[$module]);

        return view('admin.module-dashboard', $this->{$module}());
    }

    private function qhse(): array
    {
        $openFindings = AuditFinding::where('status', 'Open');

        $schedule = AssuranceProgram::with('location')
            ->whereNull('actual_date')
            ->whereDate('planned_date', '<=', today()->addDays(Crew::WARNING_DAYS))
            ->orderBy('planned_date')
            ->get();

        return [
            'title' => 'QHSE Dashboard',
            'cards' => [
                ['Open Findings', (clone $openFindings)->count(), 'warning', 'fas fa-search', '/admin/audit-findings'],
                ['Overdue Findings', $openFindings->whereDate('target_date', '<', today())->count(), 'danger', 'fas fa-exclamation-circle', '/admin/audit-findings'],
                ['Assurance Overdue', $schedule->filter(fn ($item) => $item->status() === 'Overdue')->count(), 'danger', 'fas fa-clipboard-list', '/admin/assurance-programs'],
                ['Open Observations', SafetyObservation::where('status', 'Open')->count(), 'info', 'fas fa-eye', '/admin/safety-observations'],
                ['Training & Drill ' . today()->year, TrainingActivity::whereYear('actual_date', today()->year)->count(), 'success', 'fas fa-chalkboard-teacher', '/admin/training-activities'],
            ],
            'list' => [
                'title' => 'Assurance Program jatuh tempo ' . Crew::WARNING_DAYS . ' hari',
                'url' => '/admin/assurance-programs',
                'headers' => ['Kapal', 'Program', 'Rencana', 'Status'],
                'rows' => $schedule->map(fn ($item) => [
                    $item->location?->name,
                    $item->assurance_type,
                    $item->planned_date->format('d-m-Y'),
                    [$item->status(), AssuranceProgram::STATUS_BADGES[$item->status()]],
                ]),
            ],
        ];
    }

    private function operation(): array
    {
        $contracts = VesselContract::with('location')->orderBy('end_date')->get();

        $dueContracts = $contracts->filter(fn ($contract) => in_array($contract->status(), ['Due ≤30 days', 'Due 31–90 days']));

        $certificates = VesselCertificate::query();

        return [
            'title' => 'Operation Dashboard',
            'cards' => [
                ['Kontrak Aktif', $contracts->filter->isCurrent()->count(), 'success', 'fas fa-file-contract', '/admin/vessel-contracts'],
                ['Kontrak Habis ≤ 90 Hari', $dueContracts->count(), 'warning', 'fas fa-hourglass-half', '/admin/vessel-contracts'],
                ['Sertifikat Expired', (clone $certificates)->whereDate('expired_date', '<', today())->count(), 'danger', 'fas fa-certificate', '/admin/renewal-queue'],
                ['Sertifikat Habis ≤ ' . Crew::WARNING_DAYS . ' Hari', $certificates->whereBetween('expired_date', [today(), today()->addDays(Crew::WARNING_DAYS)])->count(), 'warning', 'fas fa-certificate', '/admin/renewal-queue'],
                ['Follow-up Terbuka', ActionItem::involving('Operation')->where('status', '!=', 'Closed')->count(), 'info', 'fas fa-clipboard-check', '/admin/follow-up/operation'],
            ],
            'list' => [
                'title' => 'Kontrak yang habis dalam 90 hari',
                'url' => '/admin/vessel-contracts',
                'headers' => ['Kapal', 'Charterer', 'Berakhir', 'Status'],
                'rows' => $dueContracts->map(fn ($contract) => [
                    $contract->location?->name,
                    $contract->charterer,
                    $contract->end_date->format('d-m-Y'),
                    [$contract->status(), VesselContract::STATUS_BADGES[$contract->status()]],
                ]),
            ],
        ];
    }

    private function technical(): array
    {
        $openDefects = DamageReport::with('location')
            ->where('status', 'Open')
            ->orderByRaw("case priority when 'High' then 1 when 'Medium' then 2 else 3 end")
            ->orderBy('reported_date')
            ->get();

        $surveys = VesselSurvey::whereNull('actual_date')->get();

        $year = today()->year;

        $actual = PurchaseRequest::with('items')
            ->where('status', 'Diterima')
            ->whereYear('received_date', $year)
            ->get()
            ->sum->total();

        return [
            'title' => 'Technical Dashboard',
            'cards' => [
                ['Defect Terbuka', $openDefects->count(), 'warning', 'fas fa-exclamation-triangle', '/admin/damage-reports'],
                ['Defect High', $openDefects->where('priority', 'High')->count(), 'danger', 'fas fa-fire', '/admin/damage-reports'],
                ['Survey Terlambat', $surveys->filter(fn ($survey) => $survey->status() === 'Terlambat')->count(), 'danger', 'fas fa-anchor', '/admin/vessel-surveys'],
                ['Survey ≤ ' . Crew::WARNING_DAYS . ' Hari', $surveys->filter(fn ($survey) => $survey->status() === 'Segera')->count(), 'warning', 'fas fa-anchor', '/admin/vessel-surveys'],
                ['Anggaran ' . $year, 'Rp ' . number_format(Budget::where('year', $year)->sum('amount'), 0, ',', '.'), 'primary', 'fas fa-wallet', '/admin/budgets'],
                ['Realisasi ' . $year, 'Rp ' . number_format($actual, 0, ',', '.'), 'success', 'fas fa-receipt', '/admin/budgets'],
            ],
            'list' => [
                'title' => 'Defect terbuka',
                'url' => '/admin/damage-reports',
                'headers' => ['Kapal', 'Defect', 'Dilaporkan', 'Prioritas'],
                'rows' => $openDefects->map(fn ($defect) => [
                    $defect->location?->name,
                    $defect->title,
                    $defect->reported_date->format('d-m-Y'),
                    [$defect->priority, DamageReport::PRIORITIES[$defect->priority] ?? 'secondary'],
                ]),
            ],
        ];
    }

    private function crewing(): array
    {
        $warningDate = today()->addDays(Crew::WARNING_DAYS);

        $onboard = Crew::with('location')
            ->where(fn ($query) => $query->whereNull('sign_on_date')->orWhereDate('sign_on_date', '<=', today()))
            ->where(fn ($query) => $query->whereNull('sign_off_date')->orWhereDate('sign_off_date', '>=', today()))
            ->orderBy('sign_off_date')
            ->get();

        $relief = $onboard->filter(fn ($crew) => $crew->sign_off_date?->lte($warningDate));

        $certificates = CrewCertificate::query();

        return [
            'title' => 'Crewing Dashboard',
            'cards' => [
                ['Kru Onboard', $onboard->count(), 'success', 'fas fa-user-friends', '/admin/crew-rotation'],
                ['Perlu Relief', $relief->count(), 'warning', 'fas fa-sync-alt', '/admin/crew-rotation'],
                ['Sertifikat Kru Expired', (clone $certificates)->whereDate('expired_date', '<', today())->count(), 'danger', 'fas fa-id-card', '/admin/crews'],
                ['Sertifikat Kru ≤ ' . Crew::WARNING_DAYS . ' Hari', $certificates->whereBetween('expired_date', [today(), $warningDate])->count(), 'warning', 'fas fa-id-card', '/admin/crews'],
            ],
            'list' => [
                'title' => 'Kru yang perlu relief',
                'url' => '/admin/crew-rotation',
                'headers' => ['Kapal', 'Nama', 'Rank', 'Sign Off'],
                'rows' => $relief->map(fn ($crew) => [
                    $crew->location?->name,
                    $crew->name,
                    $crew->rank,
                    $crew->sign_off_date->format('d-m-Y'),
                ]),
            ],
        ];
    }

    private function purchasing(): array
    {
        $outstanding = PurchaseRequest::with('location')
            ->whereIn('status', ['Diajukan', 'Disetujui', 'Dipesan'])
            ->orderBy('request_date')
            ->get();

        $vendors = Vendor::with('latestEvaluation')->get();

        return [
            'title' => 'Purchasing Dashboard',
            'cards' => [
                ['PR Diajukan', $outstanding->where('status', 'Diajukan')->count(), 'secondary', 'fas fa-file-invoice', '/admin/purchase-requests'],
                ['PR Disetujui', $outstanding->where('status', 'Disetujui')->count(), 'info', 'fas fa-check', '/admin/purchase-requests'],
                ['PR Dipesan', $outstanding->where('status', 'Dipesan')->count(), 'primary', 'fas fa-truck', '/admin/purchase-requests'],
                ['Vendor Belum Dievaluasi', $vendors->where('approval_status', 'To Evaluate')->count(), 'warning', 'fas fa-star-half-alt', '/admin/vendors'],
                ['Vendor Hasil Kurang', $vendors->filter(fn ($vendor) => $vendor->latestEvaluation?->result() === 'Kurang')->count(), 'danger', 'fas fa-thumbs-down', '/admin/vendors'],
            ],
            'list' => [
                'title' => 'Purchase request yang belum diterima',
                'url' => '/admin/purchase-requests',
                'headers' => ['No. PR', 'Kapal', 'Judul', 'Tanggal', 'Status'],
                'rows' => $outstanding->map(fn ($request) => [
                    $request->request_number,
                    $request->location?->name,
                    $request->title,
                    $request->request_date->format('d-m-Y'),
                    [$request->status, PurchaseRequest::STATUSES[$request->status]],
                ]),
            ],
        ];
    }
}
