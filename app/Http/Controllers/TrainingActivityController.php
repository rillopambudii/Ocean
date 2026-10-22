<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Concerns\HandlesEvidence;
use App\Models\MasterLocation;
use App\Models\TrainingActivity;

class TrainingActivityController extends Controller
{
    use HandlesEvidence;

    public function index(Request $request)
    {
        $locationId = $request->query('location');

        $type = $request->query('type');

        $year = $request->query('year');

        $data = TrainingActivity::with('location')
            ->when($locationId, fn ($query) => $query->where('master_location_id', $locationId))
            ->when($type, fn ($query) => $query->where('activity_type', $type))
            ->when($year, fn ($query) => $query->whereYear('planned_date', $year))
            ->orderByRaw('actual_date is not null')
            ->orderBy('planned_date')
            ->get();

        $locations = MasterLocation::orderBy('name')->get();

        $overdue = $data->filter(fn ($row) => $row->status() === 'Overdue')->count();

        return view(
            'admin.training-activities.index',
            compact('data', 'locations', 'locationId', 'type', 'year', 'overdue')
        );
    }

    public function create()
    {
        $activity = null;

        $locations = MasterLocation::orderBy('name')->get();

        return view(
            'admin.training-activities.form',
            compact('activity', 'locations')
        );
    }

    public function store(Request $request)
    {
        TrainingActivity::create($this->validateActivity($request));

        return redirect('/admin/training-activities')
            ->with('success', 'Kegiatan berhasil ditambah');
    }

    public function edit(TrainingActivity $trainingActivity)
    {
        $activity = $trainingActivity;

        $locations = MasterLocation::orderBy('name')->get();

        return view(
            'admin.training-activities.form',
            compact('activity', 'locations')
        );
    }

    public function update(Request $request, TrainingActivity $trainingActivity)
    {
        $trainingActivity->update($this->validateActivity($request, $trainingActivity));

        return redirect('/admin/training-activities')
            ->with('success', 'Kegiatan berhasil diperbarui');
    }

    public function destroy(TrainingActivity $trainingActivity)
    {
        $this->deleteEvidence($trainingActivity);

        $trainingActivity->delete();

        return redirect('/admin/training-activities')
            ->with('success', 'Kegiatan berhasil dihapus');
    }

    public function evidence(TrainingActivity $trainingActivity)
    {
        return $this->evidenceResponse($trainingActivity);
    }

    private function validateActivity(Request $request, ?TrainingActivity $activity = null): array
    {
        $validated = $request->validate([

            'master_location_id' => 'required|exists:master_locations,id',

            'activity_type' => ['required', Rule::in(array_keys(TrainingActivity::TYPES))],

            'topic' => 'required|string|max:255',

            'planned_date' => 'required|date',

            'actual_date' => 'nullable|date',

            'participants' => 'nullable|integer|min:0',

            'instructor' => 'nullable|string|max:255',

            'evidence' => $this->evidenceRule(),
        ]);

        unset($validated['evidence']);

        return $validated + $this->storeEvidence($request, $activity, 'training');
    }
}
