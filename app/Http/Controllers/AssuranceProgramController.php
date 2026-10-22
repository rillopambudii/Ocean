<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Concerns\HandlesEvidence;
use App\Models\AssuranceProgram;
use App\Models\MasterLocation;

class AssuranceProgramController extends Controller
{
    use HandlesEvidence;

    public function index(Request $request)
    {
        $locationId = $request->query('location');

        $type = $request->query('type');

        $year = $request->query('year');

        $status = $request->query('status');

        $data = AssuranceProgram::with('location')
            ->when($locationId, fn ($query) => $query->where('master_location_id', $locationId))
            ->when($type, fn ($query) => $query->where('assurance_type', $type))
            ->when($year, fn ($query) => $query->whereYear('planned_date', $year))
            ->orderByRaw('actual_date is not null')
            ->orderBy('planned_date')
            ->get()
            // Status dihitung dari tanggal, jadi difilter setelah query.
            ->when($status, fn ($rows) => $rows->filter(fn ($row) => $row->status() === $status)->values());

        $locations = MasterLocation::orderBy('name')->get();

        $summary = [

            'overdue' => $data->filter(fn ($row) => $row->status() === 'Overdue')->count(),

            'dueSoon' => $data->filter(fn ($row) => $row->status() === 'Due Soon')->count(),

            'ncOpen' => $data->sum('nc_open'),
        ];

        return view(
            'admin.assurance-programs.index',
            compact('data', 'locations', 'locationId', 'type', 'year', 'status', 'summary')
        );
    }

    public function create()
    {
        $program = null;

        $locations = MasterLocation::orderBy('name')->get();

        return view(
            'admin.assurance-programs.form',
            compact('program', 'locations')
        );
    }

    public function store(Request $request)
    {
        AssuranceProgram::create($this->validateProgram($request));

        return redirect('/admin/assurance-programs')
            ->with('success', 'Assurance berhasil ditambah');
    }

    public function edit(AssuranceProgram $assuranceProgram)
    {
        $program = $assuranceProgram;

        $locations = MasterLocation::orderBy('name')->get();

        return view(
            'admin.assurance-programs.form',
            compact('program', 'locations')
        );
    }

    public function update(Request $request, AssuranceProgram $assuranceProgram)
    {
        $assuranceProgram->update($this->validateProgram($request, $assuranceProgram));

        return redirect('/admin/assurance-programs')
            ->with('success', 'Assurance berhasil diperbarui');
    }

    public function destroy(AssuranceProgram $assuranceProgram)
    {
        $this->deleteEvidence($assuranceProgram);

        $assuranceProgram->delete();

        return redirect('/admin/assurance-programs')
            ->with('success', 'Assurance berhasil dihapus');
    }

    public function evidence(AssuranceProgram $assuranceProgram)
    {
        return $this->evidenceResponse($assuranceProgram);
    }

    private function validateProgram(Request $request, ?AssuranceProgram $program = null): array
    {
        $validated = $request->validate([

            'master_location_id' => 'required|exists:master_locations,id',

            'assurance_type' => ['required', Rule::in(AssuranceProgram::TYPES)],

            'planned_date' => 'required|date',

            'actual_date' => 'nullable|date',

            'remarks' => 'nullable|string',

            'nc_open' => 'nullable|integer|min:0',

            'nc_closed' => 'nullable|integer|min:0',

            'evidence' => $this->evidenceRule(),
        ]);

        $validated['nc_open'] ??= 0;

        $validated['nc_closed'] ??= 0;

        unset($validated['evidence']);

        return $validated + $this->storeEvidence($request, $program, 'assurance');
    }
}
