<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VesselCertificate;
use App\Models\MasterLocation;
use App\Models\VesselSurvey;
use Carbon\Carbon;
use App\Exports\VesselCertificatesExport;
use Maatwebsite\Excel\Facades\Excel;

class VesselCertificateController extends Controller
{
    public function index()
    {
        $data = VesselCertificate::with('location')->get();

        return view(
            'admin.vessel-certificates.index',
            compact('data')
        );
    }

    public function create()
    {
        $locations = MasterLocation::all();

        return view(
            'admin.vessel-certificates.create',
            compact('locations')
        );
    }

    public function store(Request $request)
    {
        $expiredDate = Carbon::parse(
            $request->expired_date
        );

        $daysValid = now()->diffInDays(
            $expiredDate,
            false
        );

        if ($daysValid < 0) {

            $status = 'Expired';

        } elseif ($daysValid <= 30) {

            $status = 'Expiring Soon';

        } else {

            $status = 'Valid';
        }

        VesselCertificate::create([

            'master_location_id' =>
                $request->master_location_id,

            'certificate_name' =>
                $request->certificate_name,

            'issue_place' =>
                $request->issue_place,

            'issued_date' =>
                $request->issued_date,

            'expired_date' =>
                $request->expired_date,

            'days_valid' =>
                $daysValid,

            'status' =>
                $status,

            'remarks' =>
                $request->remarks,

            'year' =>
                $request->year,
        ]);

        return redirect('/admin/vessel-certificates')
            ->with('success', 'Certificate berhasil ditambah');
    }
    /**
     * Antrian sertifikat yang harus diperpanjang dan survey yang belum dilaksanakan,
     * diurutkan dari tanggal paling mendesak.
     */
    public function renewalQueue()
    {
        $horizon = today()->addDays(90);

        // ponytail: sertifikat lama yang sudah diganti record baru tetap muncul sampai dihapus.
        $certificates = VesselCertificate::with('location')
            ->whereDate('expired_date', '<=', $horizon)
            ->get()
            ->map(fn ($certificate) => [
                'type' => 'Sertifikat',
                'vessel' => $certificate->location?->name,
                'item' => $certificate->certificate_name,
                'due' => Carbon::parse($certificate->expired_date),
            ]);

        $surveys = VesselSurvey::with('location')
            ->whereNull('actual_date')
            ->whereDate('planned_date', '<=', $horizon)
            ->get()
            ->map(fn ($survey) => [
                'type' => 'Survey',
                'vessel' => $survey->location?->name,
                'item' => $survey->survey_type,
                'due' => $survey->planned_date,
            ]);

        $queue = $certificates->concat($surveys)
            ->map(function ($row) {
                $row['days'] = (int) today()->diffInDays($row['due'], false);
                $row['priority'] = match (true) {
                    $row['days'] < 0 => 'Overdue',
                    $row['days'] <= 30 => 'Urgent',
                    default => 'Plan',
                };

                return $row;
            })
            ->sortBy('due')
            ->values();

        return view(
            'admin.vessel-certificates.queue',
            compact('queue')
        );
    }

    public function exportExcel()
    {
        return Excel::download(
            new VesselCertificatesExport,
            'certificate-monitoring.xlsx'
        );
    }
}