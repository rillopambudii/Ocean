<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\MasterLocation;
use App\Models\PurchaseRequest;
use App\Models\PurchaseRequestItem;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('q');

        $locationId = $request->query('location');

        $status = $request->query('status');

        $data = PurchaseRequest::with(['location', 'items'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('request_number', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('po_number', 'like', "%{$search}%")
                        ->orWhere('vendor', 'like', "%{$search}%");
                });
            })
            ->when($locationId, fn ($query) => $query->where('master_location_id', $locationId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest('request_date')
            ->latest('id')
            ->get();

        $locations = MasterLocation::orderBy('name')->get();

        return view(
            'admin.purchase-requests.index',
            compact('data', 'locations', 'search', 'locationId', 'status')
        );
    }

    public function create()
    {
        $purchase = null;

        $locations = MasterLocation::orderBy('name')->get();

        return view(
            'admin.purchase-requests.form',
            compact('purchase', 'locations')
        );
    }

    public function store(Request $request)
    {
        $purchase = PurchaseRequest::create(
            $this->validatePurchase($request) + ['requested_by' => auth()->id()]
        );

        return redirect("/admin/purchase-requests/{$purchase->id}")
            ->with('success', 'Permintaan dibuat, silakan tambahkan item');
    }

    public function show(PurchaseRequest $purchaseRequest)
    {
        $purchase = $purchaseRequest->load(['location', 'items', 'requester']);

        return view(
            'admin.purchase-requests.show',
            compact('purchase')
        );
    }

    public function edit(PurchaseRequest $purchaseRequest)
    {
        $purchase = $purchaseRequest;

        $locations = MasterLocation::orderBy('name')->get();

        return view(
            'admin.purchase-requests.form',
            compact('purchase', 'locations')
        );
    }

    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        $purchaseRequest->update($this->validatePurchase($request, $purchaseRequest));

        return redirect("/admin/purchase-requests/{$purchaseRequest->id}")
            ->with('success', 'Permintaan berhasil diperbarui');
    }

    public function destroy(PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->attachment_path) {
            Storage::delete($purchaseRequest->attachment_path);
        }

        $purchaseRequest->delete();

        return redirect('/admin/purchase-requests')
            ->with('success', 'Permintaan berhasil dihapus');
    }

    public function attachment(PurchaseRequest $purchaseRequest)
    {
        abort_unless(
            $purchaseRequest->attachment_path && Storage::exists($purchaseRequest->attachment_path),
            404
        );

        return Storage::response(
            $purchaseRequest->attachment_path,
            $purchaseRequest->attachment_name
        );
    }

    public function storeItem(Request $request, PurchaseRequest $purchaseRequest)
    {
        $validated = $request->validate([

            'description' => 'required|string|max:255',

            'quantity' => 'required|numeric|gt:0',

            'unit' => 'required|string|max:30',

            'unit_price' => 'nullable|numeric|min:0',
        ]);

        // Harga boleh dikosongkan saat pengajuan, diisi setelah ada penawaran vendor.
        $validated['unit_price'] ??= 0;

        $purchaseRequest->items()->create($validated);

        return redirect("/admin/purchase-requests/{$purchaseRequest->id}")
            ->with('success', 'Item berhasil ditambah');
    }

    public function destroyItem(PurchaseRequestItem $item)
    {
        $purchaseId = $item->purchase_request_id;

        $item->delete();

        return redirect("/admin/purchase-requests/{$purchaseId}")
            ->with('success', 'Item berhasil dihapus');
    }

    private function validatePurchase(Request $request, ?PurchaseRequest $purchase = null): array
    {
        $validated = $request->validate([

            'master_location_id' => 'required|exists:master_locations,id',

            'title' => 'required|string|max:255',

            'category' => ['required', Rule::in(PurchaseRequest::CATEGORIES)],

            'request_date' => 'required|date',

            'status' => ['required', Rule::in(array_keys(PurchaseRequest::STATUSES))],

            'vendor' => 'nullable|string|max:255',

            'po_number' => 'nullable|string|max:100',

            'received_date' => 'nullable|date|after_or_equal:request_date',

            'remarks' => 'nullable|string',

            'attachment' => 'nullable|file|' . DocumentController::FILE_RULES,
        ]);

        // Tanggal terima = bulan realisasi anggaran; hanya untuk status Diterima, default hari ini.
        $validated['received_date'] = $validated['status'] === 'Diterima'
            ? ($validated['received_date'] ?? today()->toDateString())
            : null;

        unset($validated['attachment']);

        if ($request->hasFile('attachment')) {

            if ($purchase?->attachment_path) {
                Storage::delete($purchase->attachment_path);
            }

            $file = $request->file('attachment');

            $validated['attachment_path'] = $file->store('purchasing');

            $validated['attachment_name'] = $file->getClientOriginalName();
        }

        return $validated;
    }
}
