<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\DamageReport;
use App\Models\MasterLocation;

class DamageReportController extends Controller
{
    public function index(Request $request)
    {
        $locationId = $request->query('location');

        $status = $request->query('status');

        $data = DamageReport::with('location')
            ->when($locationId, fn ($query) => $query->where('master_location_id', $locationId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByRaw("status = 'Closed'")
            ->latest('reported_date')
            ->get();

        $locations = MasterLocation::orderBy('name')->get();

        return view(
            'admin.damage-reports.index',
            compact('data', 'locations', 'locationId', 'status')
        );
    }

    public function create()
    {
        $report = null;

        $locations = MasterLocation::orderBy('name')->get();

        return view(
            'admin.damage-reports.form',
            compact('report', 'locations')
        );
    }

    public function store(Request $request)
    {
        DamageReport::create($this->validateReport($request));

        return redirect('/admin/damage-reports')
            ->with('success', 'Laporan berhasil ditambah');
    }

    public function edit(DamageReport $damageReport)
    {
        $report = $damageReport;

        $locations = MasterLocation::orderBy('name')->get();

        return view(
            'admin.damage-reports.form',
            compact('report', 'locations')
        );
    }

    public function update(Request $request, DamageReport $damageReport)
    {
        $damageReport->update($this->validateReport($request));

        return redirect('/admin/damage-reports')
            ->with('success', 'Laporan berhasil diperbarui');
    }

    public function destroy(DamageReport $damageReport)
    {
        $damageReport->delete();

        return redirect('/admin/damage-reports')
            ->with('success', 'Laporan berhasil dihapus');
    }

    private function validateReport(Request $request): array
    {
        $validated = $request->validate([

            'master_location_id' => 'required|exists:master_locations,id',

            'reported_date' => 'required|date',

            'title' => 'required|string|max:255',

            'description' => 'nullable|string',

            'priority' => ['required', Rule::in(array_keys(DamageReport::PRIORITIES))],

            'status' => ['required', Rule::in(DamageReport::STATUSES)],

            'closed_date' => 'nullable|date|after_or_equal:reported_date',

            'action_taken' => 'nullable|string',
        ]);

        // Tanggal selesai hanya bermakna untuk laporan Closed; default hari ini.
        $validated['closed_date'] = $validated['status'] === 'Closed'
            ? ($validated['closed_date'] ?? today()->toDateString())
            : null;

        return $validated;
    }
}
