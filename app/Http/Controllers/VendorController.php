<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Concerns\HandlesEvidence;
use App\Models\PurchaseRequest;
use App\Models\Vendor;
use App\Models\VendorEvaluation;

class VendorController extends Controller
{
    use HandlesEvidence;

    public function index(Request $request)
    {
        $search = $request->query('q');

        $category = $request->query('category');

        $status = $request->query('status');

        $data = Vendor::with('latestEvaluation')
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when($category, fn ($query) => $query->where('category', $category))
            ->when($status, fn ($query) => $query->where('approval_status', $status))
            ->orderBy('name')
            ->get();

        return view(
            'admin.vendors.index',
            compact('data', 'search', 'category', 'status')
        );
    }

    public function create()
    {
        $vendor = null;

        return view('admin.vendors.form', compact('vendor'));
    }

    public function store(Request $request)
    {
        $vendor = Vendor::create($this->validateVendor($request));

        return redirect("/admin/vendors/{$vendor->id}")
            ->with('success', 'Vendor berhasil ditambah');
    }

    public function show(Vendor $vendor)
    {
        $vendor->load('evaluations');

        return view('admin.vendors.show', compact('vendor'));
    }

    public function edit(Vendor $vendor)
    {
        return view('admin.vendors.form', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $vendor->update($this->validateVendor($request));

        return redirect("/admin/vendors/{$vendor->id}")
            ->with('success', 'Vendor berhasil diperbarui');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->evaluations->each(fn ($evaluation) => $this->deleteEvidence($evaluation));

        $vendor->delete();

        return redirect('/admin/vendors')
            ->with('success', 'Vendor berhasil dihapus');
    }

    public function storeEvaluation(Request $request, Vendor $vendor)
    {
        $scores = array_fill_keys(array_keys(VendorEvaluation::CRITERIA), 'required|integer|between:1,5');

        $validated = $request->validate($scores + [

            'review_date' => 'required|date',

            'reviewer' => 'nullable|string|max:255',

            'remarks' => 'nullable|string',

            'evidence' => $this->evidenceRule(),
        ]);

        unset($validated['evidence']);

        $vendor->evaluations()->create($validated + $this->storeEvidence($request, null, 'vendor-evaluations'));

        return redirect("/admin/vendors/{$vendor->id}")
            ->with('success', 'Evaluasi berhasil ditambah');
    }

    public function destroyEvaluation(VendorEvaluation $evaluation)
    {
        $this->deleteEvidence($evaluation);

        $evaluation->delete();

        return redirect("/admin/vendors/{$evaluation->vendor_id}")
            ->with('success', 'Evaluasi berhasil dihapus');
    }

    public function evaluationEvidence(VendorEvaluation $evaluation)
    {
        return $this->evidenceResponse($evaluation);
    }

    private function validateVendor(Request $request): array
    {
        return $request->validate([

            'name' => 'required|string|max:255',

            'category' => ['required', Rule::in(PurchaseRequest::CATEGORIES)],

            'contact' => 'nullable|string|max:255',

            'approval_status' => ['required', Rule::in(array_keys(Vendor::APPROVAL_STATUSES))],

            'remarks' => 'nullable|string',
        ]);
    }
}
