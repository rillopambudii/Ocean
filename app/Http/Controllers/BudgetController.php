<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Budget;
use App\Models\MasterLocation;
use App\Models\PurchaseRequest;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $year = (int) $request->query('year', now()->year);

        $locationId = $request->query('location');

        $budgets = Budget::where('year', $year)
            ->when($locationId, fn ($query) => $query->where('master_location_id', $locationId))
            ->get();

        // Realisasi = pembelian berstatus Diterima, dibukukan di bulan tanggal terima.
        $purchases = PurchaseRequest::with('items')
            ->where('status', 'Diterima')
            ->whereYear('received_date', $year)
            ->when($locationId, fn ($query) => $query->where('master_location_id', $locationId))
            ->get();

        $blank = ['budget' => 0, 'actual' => 0];

        $monthly = array_fill_keys(array_keys(Budget::MONTHS), $blank);

        $byCategory = array_fill_keys(PurchaseRequest::CATEGORIES, $blank);

        foreach ($budgets as $budget) {
            $monthly[$budget->month]['budget'] += $budget->amount;
            $byCategory[$budget->category]['budget'] += $budget->amount;
        }

        foreach ($purchases as $purchase) {
            $monthly[$purchase->received_date->month]['actual'] += $purchase->total();
            $byCategory[$purchase->category]['actual'] += $purchase->total();
        }

        $locations = MasterLocation::orderBy('name')->get();

        return view(
            'admin.budgets.index',
            compact('year', 'locationId', 'locations', 'monthly', 'byCategory')
        );
    }

    public function plan(Request $request)
    {
        $year = (int) $request->query('year', now()->year);

        $locations = MasterLocation::orderBy('name')->get();

        $location = $locations->firstWhere('id', $request->query('location')) ?? $locations->first();

        abort_unless($location, 404, 'Tambahkan kapal di Master Lokasi terlebih dahulu.');

        // [kategori][bulan] => nominal
        $amounts = Budget::where('year', $year)
            ->where('master_location_id', $location->id)
            ->get()
            ->groupBy('category')
            ->map(fn ($rows) => $rows->pluck('amount', 'month'));

        return view(
            'admin.budgets.plan',
            compact('year', 'location', 'locations', 'amounts')
        );
    }

    public function savePlan(Request $request)
    {
        $validated = $request->validate([

            'master_location_id' => 'required|exists:master_locations,id',

            'year' => 'required|integer|min:2000|max:2100',

            'amounts' => 'array',

            'amounts.*' => 'array',

            'amounts.*.*' => 'nullable|numeric|min:0',
        ]);

        $rows = [];

        foreach ($validated['amounts'] ?? [] as $category => $months) {

            abort_unless(in_array($category, PurchaseRequest::CATEGORIES, true), 422);

            foreach ($months as $month => $amount) {

                if ((float) $amount > 0 && isset(Budget::MONTHS[$month])) {
                    $rows[] = [
                        'master_location_id' => $validated['master_location_id'],
                        'category' => $category,
                        'year' => $validated['year'],
                        'month' => $month,
                        'amount' => $amount,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // Grid menggantikan seluruh anggaran kapal tsb di tahun tsb.
        DB::transaction(function () use ($validated, $rows) {
            Budget::where('master_location_id', $validated['master_location_id'])
                ->where('year', $validated['year'])
                ->delete();

            Budget::insert($rows);
        });

        return redirect("/admin/budgets?year={$validated['year']}&location={$validated['master_location_id']}")
            ->with('success', 'Anggaran berhasil disimpan');
    }
}
