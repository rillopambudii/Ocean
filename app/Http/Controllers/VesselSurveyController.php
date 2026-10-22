<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\MasterLocation;
use App\Models\VesselSurvey;

class VesselSurveyController extends Controller
{
    public function index(Request $request)
    {
        $locationId = $request->query('location');

        $data = VesselSurvey::with('location')
            ->when($locationId, fn ($query) => $query->where('master_location_id', $locationId))
            ->orderByRaw('actual_date is not null')
            ->orderBy('planned_date')
            ->get();

        $locations = MasterLocation::orderBy('name')->get();

        $alerts = $data->filter(fn ($survey) => in_array($survey->status(), ['Terlambat', 'Segera']))->count();

        return view(
            'admin.vessel-surveys.index',
            compact('data', 'locations', 'locationId', 'alerts')
        );
    }

    public function create()
    {
        $survey = null;

        $locations = MasterLocation::orderBy('name')->get();

        return view(
            'admin.vessel-surveys.form',
            compact('survey', 'locations')
        );
    }

    public function store(Request $request)
    {
        VesselSurvey::create($this->validateSurvey($request));

        return redirect('/admin/vessel-surveys')
            ->with('success', 'Jadwal berhasil ditambah');
    }

    public function edit(VesselSurvey $vesselSurvey)
    {
        $survey = $vesselSurvey;

        $locations = MasterLocation::orderBy('name')->get();

        return view(
            'admin.vessel-surveys.form',
            compact('survey', 'locations')
        );
    }

    public function update(Request $request, VesselSurvey $vesselSurvey)
    {
        $vesselSurvey->update($this->validateSurvey($request));

        return redirect('/admin/vessel-surveys')
            ->with('success', 'Jadwal berhasil diperbarui');
    }

    public function destroy(VesselSurvey $vesselSurvey)
    {
        $vesselSurvey->delete();

        return redirect('/admin/vessel-surveys')
            ->with('success', 'Jadwal berhasil dihapus');
    }

    private function validateSurvey(Request $request): array
    {
        return $request->validate([

            'master_location_id' => 'required|exists:master_locations,id',

            'survey_type' => ['required', Rule::in(VesselSurvey::TYPES)],

            'planned_date' => 'required|date',

            'actual_date' => 'nullable|date',

            'remarks' => 'nullable|string',
        ]);
    }
}
