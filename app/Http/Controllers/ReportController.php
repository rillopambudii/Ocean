<?php

namespace App\Http\Controllers;

use App\Models\ActionItem;
use App\Models\AssuranceProgram;
use App\Models\Crew;
use App\Models\DamageReport;
use App\Models\MasterLocation;
use App\Models\PurchaseRequest;
use App\Models\SafetyObservation;
use App\Models\TrainingActivity;
use App\Models\VesselCertificate;
use App\Models\VesselContract;
use App\Models\VesselSurvey;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Laporan Excel / PDF per register, difilter kapal dan tahun.
 * Akses mengikuti menu modul; Admin Kapal otomatis hanya mendapat data kapalnya.
 */
class ReportController extends Controller
{
    public function index()
    {
        $reports = collect($this->reports())->filter(fn ($report) => Gate::any($report['gates']));

        $locations = MasterLocation::orderBy('name')->get();

        return view('admin.reports.index', compact('reports', 'locations'));
    }

    public function export(Request $request)
    {
        $reports = $this->reports();

        $validated = $request->validate([
            'report' => ['required', Rule::in(array_keys($reports))],
            'format' => ['required', Rule::in(['excel', 'pdf'])],
            'location' => ['nullable', 'integer'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
        ]);

        $report = $reports[$validated['report']];

        abort_unless(Gate::any($report['gates']), 403);

        $location = filled($validated['location'] ?? null)
            ? MasterLocation::findOrFail($validated['location'])
            : null;

        $year = $validated['year'] ?? null;

        $rows = $report['query']()
            ->when($location, fn ($query) => $query->where('master_location_id', $location->id))
            ->when($year, fn ($query) => $query->whereYear($report['date'], $year))
            ->orderBy($report['date'])
            ->get()
            ->map($report['row'])
            ->map(fn ($row) => array_map(fn ($cell) => $cell instanceof Carbon ? $cell->format('d-m-Y') : $cell, $row))
            ->all();

        $title = $report['title'];
        $headers = $report['headers'];
        $filters = ['Kapal: ' . ($location->name ?? 'Semua'), 'Tahun: ' . ($year ?? 'Semua')];
        $filename = $validated['report'] . '-' . today()->format('Ymd');

        if ($validated['format'] === 'pdf') {
            return Pdf::loadView('admin.reports.pdf', compact('title', 'headers', 'rows', 'filters'))
                ->setPaper('a4', 'landscape')
                ->download("{$filename}.pdf");
        }

        $sheet = new class($headers, $rows) implements FromArray, WithHeadings
        {
            public function __construct(private array $headers, private array $rows) {}

            public function array(): array
            {
                return $this->rows;
            }

            public function headings(): array
            {
                return $this->headers;
            }
        };

        return Excel::download($sheet, "{$filename}.xlsx");
    }

    /**
     * Definisi laporan: judul, modul, gate, kolom tanggal untuk filter tahun, query, dan baris.
     */
    private function reports(): array
    {
        $vessel = fn ($model) => $model->location?->name;

        return [
            'assurance-programs' => [
                'title' => 'Assurance Program',
                'module' => 'QHSE',
                'gates' => ['qhse-access', 'vessel-admin'],
                'date' => 'planned_date',
                'query' => fn () => AssuranceProgram::with('location'),
                'headers' => ['Kapal', 'Program', 'Rencana', 'Aktual', 'NC Open', 'NC Closed', 'Status', 'Catatan'],
                'row' => fn ($item) => [$vessel($item), $item->assurance_type, $item->planned_date, $item->actual_date, $item->nc_open, $item->nc_closed, $item->status(), $item->remarks],
            ],
            'safety-observations' => [
                'title' => 'OSCARD & Observations',
                'module' => 'QHSE',
                'gates' => ['qhse-access', 'vessel-admin'],
                'date' => 'observed_date',
                'query' => fn () => SafetyObservation::with('location'),
                'headers' => ['ID', 'Kapal', 'Tanggal', 'Kategori', 'Deskripsi', 'PIC', 'Due Date', 'Status'],
                'row' => fn ($item) => [$item->code(), $vessel($item), $item->observed_date, $item->category, $item->description, $item->pic, $item->due_date, $item->status],
            ],
            'training-activities' => [
                'title' => 'Training & Drills',
                'module' => 'QHSE',
                'gates' => ['qhse-access', 'vessel-admin'],
                'date' => 'planned_date',
                'query' => fn () => TrainingActivity::with('location'),
                'headers' => ['ID', 'Kapal', 'Jenis', 'Topik', 'Rencana', 'Aktual', 'Peserta', 'Status'],
                'row' => fn ($item) => [$item->code(), $vessel($item), $item->activity_type, $item->topic, $item->planned_date, $item->actual_date, $item->participants, $item->status()],
            ],
            'vessel-contracts' => [
                'title' => 'Hire & Contract Status',
                'module' => 'Operation',
                'gates' => ['operation-access', 'vessel-admin'],
                'date' => 'start_date',
                'query' => fn () => VesselContract::with('location'),
                'headers' => ['Kapal', 'Charterer', 'Mulai', 'Berakhir', 'Status', 'Catatan'],
                'row' => fn ($item) => [$vessel($item), $item->charterer, $item->start_date, $item->end_date, $item->status(), $item->remarks],
            ],
            'vessel-certificates' => [
                'title' => 'Certificate Register',
                'module' => 'Operation',
                'gates' => ['operation-access', 'vessel-admin'],
                'date' => 'expired_date',
                'query' => fn () => VesselCertificate::with('location'),
                'headers' => ['Kapal', 'Sertifikat', 'Tempat Terbit', 'Terbit', 'Expired', 'Sisa Hari', 'Catatan'],
                'row' => fn ($item) => [$vessel($item), $item->certificate_name, $item->issue_place, Carbon::parse($item->issued_date), Carbon::parse($item->expired_date), (int) today()->diffInDays(Carbon::parse($item->expired_date), false), $item->remarks],
            ],
            'vessel-surveys' => [
                'title' => 'Docking Plan',
                'module' => 'Technical',
                'gates' => ['technical-access', 'vessel-admin'],
                'date' => 'planned_date',
                'query' => fn () => VesselSurvey::with('location'),
                'headers' => ['Kapal', 'Jenis', 'Rencana', 'Aktual', 'Status', 'Catatan'],
                'row' => fn ($item) => [$vessel($item), $item->survey_type, $item->planned_date, $item->actual_date, $item->status(), $item->remarks],
            ],
            'damage-reports' => [
                'title' => 'Defect Register',
                'module' => 'Technical',
                'gates' => ['technical-access', 'vessel-admin'],
                'date' => 'reported_date',
                'query' => fn () => DamageReport::with('location'),
                'headers' => ['Kapal', 'Dilaporkan', 'Defect', 'Prioritas', 'Status', 'Ditutup', 'Tindakan'],
                'row' => fn ($item) => [$vessel($item), $item->reported_date, $item->title, $item->priority, $item->status, $item->closed_date, $item->action_taken],
            ],
            'crews' => [
                'title' => 'Crew Register',
                'module' => 'Crewing',
                'gates' => ['crewing-access', 'vessel-admin'],
                'date' => 'sign_on_date',
                'query' => fn () => Crew::with('location'),
                'headers' => ['Nama', 'Rank', 'Kapal', 'Seaman Book', 'Sign On', 'Sign Off', 'Status Kontrak'],
                'row' => fn ($item) => [$item->name, $item->rank, $vessel($item), $item->seaman_book_number, $item->sign_on_date, $item->sign_off_date, $item->contractStatus()],
            ],
            'purchase-requests' => [
                'title' => 'Vessel Requisitions',
                'module' => 'Purchasing',
                'gates' => ['purchasing-access', 'vessel-admin'],
                'date' => 'request_date',
                'query' => fn () => PurchaseRequest::with(['location', 'items']),
                'headers' => ['No. PR', 'Kapal', 'Tanggal', 'Judul', 'Kategori', 'Vendor', 'No. PO', 'Status', 'Total (Rp)'],
                'row' => fn ($item) => [$item->request_number, $vessel($item), $item->request_date, $item->title, $item->category, $item->vendor, $item->po_number, $item->status, $item->total()],
            ],
            'action-items' => [
                'title' => 'Weekly Action Tracker',
                'module' => 'Fleet Overview',
                'gates' => ['workspace'],
                'date' => 'target_date',
                'query' => fn () => ActionItem::with('location'),
                'headers' => ['ID', 'Kapal', 'Action', 'Departemen', 'PIC', 'Prioritas', 'Target', 'Status', 'Next Action'],
                'row' => fn ($item) => [$item->code(), $vessel($item), $item->title, $item->department, $item->lead_pic, $item->priority, $item->target_date, $item->status, $item->next_action],
            ],
        ];
    }
}
