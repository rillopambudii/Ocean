<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Crew;
use App\Models\CrewCertificate;
use App\Models\MasterLocation;

class CrewController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('q');

        $locationId = $request->query('location');

        $data = Crew::with(['location', 'certificates'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('rank', 'like', "%{$search}%")
                        ->orWhere('seaman_book_number', 'like', "%{$search}%");
                });
            })
            ->when($locationId, fn ($query) => $query->where('master_location_id', $locationId))
            ->orderBy('name')
            ->get();

        $locations = MasterLocation::orderBy('name')->get();

        // Ringkasan peringatan untuk kru yang sedang tampil.
        $warnings = [

            'contracts' => $data->filter(fn ($crew) => in_array($crew->contractStatus(), ['Expired', 'Segera Habis']))->count(),

            'certificates' => $data->flatMap->certificates
                ->filter(fn ($certificate) => in_array($certificate->status(), ['Expired', 'Segera Habis']))
                ->count(),
        ];

        return view(
            'admin.crews.index',
            compact('data', 'locations', 'search', 'locationId', 'warnings')
        );
    }

    /**
     * Kru yang sedang atau akan naik kapal, per kapal, diurutkan dari sign off terdekat.
     */
    public function rotation()
    {
        $groups = Crew::with('location')
            ->where(fn ($query) => $query->whereNull('sign_off_date')->orWhereDate('sign_off_date', '>=', today()))
            ->orderByRaw('sign_off_date is null')
            ->orderBy('sign_off_date')
            ->get()
            ->groupBy(fn ($crew) => $crew->location?->name ?? 'Belum ditempatkan')
            ->sortKeys();

        return view(
            'admin.crews.rotation',
            compact('groups')
        );
    }

    public function create()
    {
        $crew = null;

        $locations = MasterLocation::orderBy('name')->get();

        return view(
            'admin.crews.form',
            compact('crew', 'locations')
        );
    }

    public function store(Request $request)
    {
        $crew = Crew::create($this->validateCrew($request));

        return redirect("/admin/crews/{$crew->id}")
            ->with('success', 'Data kru berhasil ditambah');
    }

    public function show(Crew $crew)
    {
        $crew->load(['location', 'certificates']);

        return view(
            'admin.crews.show',
            compact('crew')
        );
    }

    public function edit(Crew $crew)
    {
        $locations = MasterLocation::orderBy('name')->get();

        return view(
            'admin.crews.form',
            compact('crew', 'locations')
        );
    }

    public function update(Request $request, Crew $crew)
    {
        $crew->update($this->validateCrew($request));

        return redirect("/admin/crews/{$crew->id}")
            ->with('success', 'Data kru berhasil diperbarui');
    }

    public function destroy(Crew $crew)
    {
        $crew->delete();

        return redirect('/admin/crews')
            ->with('success', 'Data kru berhasil dihapus');
    }

    public function storeCertificate(Request $request, Crew $crew)
    {
        $crew->certificates()->create($request->validate([

            'name' => 'required|string|max:255',

            'certificate_number' => 'nullable|string|max:100',

            'expired_date' => 'nullable|date',
        ]));

        return redirect("/admin/crews/{$crew->id}")
            ->with('success', 'Sertifikat berhasil ditambah');
    }

    public function destroyCertificate(CrewCertificate $certificate)
    {
        $crewId = $certificate->crew_id;

        $certificate->delete();

        return redirect("/admin/crews/{$crewId}")
            ->with('success', 'Sertifikat berhasil dihapus');
    }

    private function validateCrew(Request $request): array
    {
        return $request->validate([

            'name' => 'required|string|max:255',

            'rank' => 'required|string|max:100',

            'master_location_id' => 'nullable|exists:master_locations,id',

            'seaman_book_number' => 'nullable|string|max:100',

            'sign_on_date' => 'nullable|date',

            'sign_off_date' => 'nullable|date|after_or_equal:sign_on_date',

            'remarks' => 'nullable|string',
        ]);
    }
}
