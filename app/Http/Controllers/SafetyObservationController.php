<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Concerns\HandlesEvidence;
use App\Models\MasterLocation;
use App\Models\SafetyObservation;

class SafetyObservationController extends Controller
{
    use HandlesEvidence;

    public function index(Request $request)
    {
        $locationId = $request->query('location');

        $category = $request->query('category');

        $status = $request->query('status');

        $data = SafetyObservation::with('location')
            ->when($locationId, fn ($query) => $query->where('master_location_id', $locationId))
            ->when($category, fn ($query) => $query->where('category', $category))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByRaw("status = 'Closed'")
            ->latest('observed_date')
            ->get();

        $locations = MasterLocation::orderBy('name')->get();

        $overdue = $data->filter->isOverdue()->count();

        return view(
            'admin.safety-observations.index',
            compact('data', 'locations', 'locationId', 'category', 'status', 'overdue')
        );
    }

    public function create()
    {
        $observation = null;

        $locations = MasterLocation::orderBy('name')->get();

        return view(
            'admin.safety-observations.form',
            compact('observation', 'locations')
        );
    }

    public function store(Request $request)
    {
        SafetyObservation::create($this->validateObservation($request));

        return redirect('/admin/safety-observations')
            ->with('success', 'Observasi berhasil ditambah');
    }

    public function edit(SafetyObservation $safetyObservation)
    {
        $observation = $safetyObservation;

        $locations = MasterLocation::orderBy('name')->get();

        return view(
            'admin.safety-observations.form',
            compact('observation', 'locations')
        );
    }

    public function update(Request $request, SafetyObservation $safetyObservation)
    {
        $safetyObservation->update($this->validateObservation($request, $safetyObservation));

        return redirect('/admin/safety-observations')
            ->with('success', 'Observasi berhasil diperbarui');
    }

    public function destroy(SafetyObservation $safetyObservation)
    {
        $this->deleteEvidence($safetyObservation);

        $safetyObservation->delete();

        return redirect('/admin/safety-observations')
            ->with('success', 'Observasi berhasil dihapus');
    }

    public function evidence(SafetyObservation $safetyObservation)
    {
        return $this->evidenceResponse($safetyObservation);
    }

    private function validateObservation(Request $request, ?SafetyObservation $observation = null): array
    {
        $validated = $request->validate([

            'master_location_id' => 'required|exists:master_locations,id',

            'observed_date' => 'required|date',

            'category' => ['required', Rule::in(array_keys(SafetyObservation::CATEGORIES))],

            'description' => 'required|string',

            'immediate_action' => 'nullable|string',

            'pic' => 'nullable|string|max:255',

            'due_date' => 'nullable|date',

            'status' => ['required', Rule::in(SafetyObservation::STATUSES)],

            'closed_date' => 'nullable|date|after_or_equal:observed_date',

            'evidence' => $this->evidenceRule(),
        ]);

        // Tanggal close hanya untuk status Closed; default hari ini.
        $validated['closed_date'] = $validated['status'] === 'Closed'
            ? ($validated['closed_date'] ?? today()->toDateString())
            : null;

        unset($validated['evidence']);

        return $validated + $this->storeEvidence($request, $observation, 'observations');
    }
}
