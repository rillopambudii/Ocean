<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MasterLocation;
use App\Models\VesselContract;
use App\Models\VesselSurvey;

class VesselContractController extends Controller
{
    public function index()
    {
        $contracts = VesselContract::with('location')
            ->orderByDesc('start_date')
            ->get();

        $nextDockings = VesselSurvey::where('survey_type', 'Docking')
            ->whereNull('actual_date')
            ->orderBy('planned_date')
            ->get()
            ->unique('master_location_id')
            ->keyBy('master_location_id');

        // Ringkasan per kapal: kontrak berjalan, kontrak berikutnya, docking berikutnya.
        $fleet = MasterLocation::where('type', '!=', 'Shore')
            ->orWhereIn('id', $contracts->pluck('master_location_id'))
            ->orderBy('name')
            ->get()
            ->map(function ($vessel) use ($contracts, $nextDockings) {
                $own = $contracts->where('master_location_id', $vessel->id);

                return [
                    'vessel' => $vessel,
                    'current' => $own->first->isCurrent(),
                    'next' => $own->filter(fn ($contract) => $contract->start_date->isAfter(today()))
                        ->sortBy('start_date')
                        ->first(),
                    'docking' => $nextDockings->get($vessel->id),
                ];
            });

        return view(
            'admin.vessel-contracts.index',
            compact('fleet', 'contracts')
        );
    }

    public function create()
    {
        $contract = null;

        $locations = MasterLocation::orderBy('name')->get();

        return view(
            'admin.vessel-contracts.form',
            compact('contract', 'locations')
        );
    }

    public function store(Request $request)
    {
        VesselContract::create($this->validateContract($request));

        return redirect('/admin/vessel-contracts')
            ->with('success', 'Kontrak berhasil ditambah');
    }

    public function edit(VesselContract $vesselContract)
    {
        $contract = $vesselContract;

        $locations = MasterLocation::orderBy('name')->get();

        return view(
            'admin.vessel-contracts.form',
            compact('contract', 'locations')
        );
    }

    public function update(Request $request, VesselContract $vesselContract)
    {
        $vesselContract->update($this->validateContract($request));

        return redirect('/admin/vessel-contracts')
            ->with('success', 'Kontrak berhasil diperbarui');
    }

    public function destroy(VesselContract $vesselContract)
    {
        $vesselContract->delete();

        return redirect('/admin/vessel-contracts')
            ->with('success', 'Kontrak berhasil dihapus');
    }

    private function validateContract(Request $request): array
    {
        return $request->validate([

            'master_location_id' => 'required|exists:master_locations,id',

            'charterer' => 'required|string|max:255',

            'start_date' => 'required|date',

            'end_date' => 'required|date|after_or_equal:start_date',

            'remarks' => 'nullable|string',
        ]);
    }
}
