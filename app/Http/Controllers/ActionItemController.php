<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Concerns\HandlesEvidence;
use App\Models\ActionItem;
use App\Models\MasterLocation;

class ActionItemController extends Controller
{
    use HandlesEvidence;

    /**
     * Weekly Action Tracker (semua departemen) atau tampilan follow-up satu departemen.
     */
    public function index(Request $request, ?string $slug = null)
    {
        $department = $slug ? (ActionItem::departmentBySlug($slug) ?? abort(404)) : null;

        $title = $department ? ActionItem::DEPARTMENTS[$department]['view'] : 'Weekly Action Tracker';

        $locationId = $request->query('location');

        $status = $request->query('status');

        $data = ActionItem::with('location')
            ->when($department, fn ($query) => $query->involving($department))
            ->when($locationId, fn ($query) => $query->where('master_location_id', $locationId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByRaw("status = 'Closed'")
            ->orderByRaw('target_date is null')
            ->orderBy('target_date')
            ->get();

        $locations = MasterLocation::orderBy('name')->get();

        $overdue = $data->filter->isOverdue()->count();

        $back = '/admin/follow-up' . ($slug ? "/{$slug}" : '');

        return view(
            'admin.action-items.index',
            compact('data', 'locations', 'locationId', 'status', 'department', 'title', 'overdue', 'back')
        );
    }

    public function create(Request $request)
    {
        $action = null;

        $locations = MasterLocation::orderBy('name')->get();

        $back = $this->back($request);

        // Dibuka dari tampilan departemen → departemen terisi otomatis.
        $defaultDepartment = ActionItem::departmentBySlug(basename($back));

        return view(
            'admin.action-items.form',
            compact('action', 'locations', 'back', 'defaultDepartment')
        );
    }

    public function store(Request $request)
    {
        ActionItem::create($this->validateAction($request));

        return redirect($this->back($request))
            ->with('success', 'Action berhasil ditambah');
    }

    public function edit(Request $request, ActionItem $actionItem)
    {
        $action = $actionItem;

        $locations = MasterLocation::orderBy('name')->get();

        $back = $this->back($request);

        $defaultDepartment = null;

        return view(
            'admin.action-items.form',
            compact('action', 'locations', 'back', 'defaultDepartment')
        );
    }

    public function update(Request $request, ActionItem $actionItem)
    {
        $actionItem->update($this->validateAction($request, $actionItem));

        return redirect($this->back($request))
            ->with('success', 'Action berhasil diperbarui');
    }

    public function destroy(Request $request, ActionItem $actionItem)
    {
        $this->deleteEvidence($actionItem);

        $actionItem->delete();

        return redirect($this->back($request))
            ->with('success', 'Action berhasil dihapus');
    }

    public function evidence(ActionItem $actionItem)
    {
        return $this->evidenceResponse($actionItem);
    }

    /**
     * Halaman asal untuk kembali setelah simpan; hanya halaman follow-up yang diizinkan.
     */
    private function back(Request $request): string
    {
        $back = (string) $request->input('back');

        return preg_match('#^/admin/follow-up(/[a-z]+)?$#', $back) ? $back : '/admin/follow-up';
    }

    private function validateAction(Request $request, ?ActionItem $action = null): array
    {
        $departments = array_keys(ActionItem::DEPARTMENTS);

        $validated = $request->validate([

            'master_location_id' => 'required|exists:master_locations,id',

            'title' => 'required|string|max:255',

            'department' => ['required', Rule::in($departments)],

            'lead_pic' => 'nullable|string|max:255',

            'supporting_department' => ['nullable', Rule::in($departments), 'different:department'],

            'priority' => ['required', Rule::in(array_keys(ActionItem::PRIORITIES))],

            'target_date' => 'nullable|date',

            'actual_date' => 'nullable|date',

            'status' => ['required', Rule::in(array_keys(ActionItem::STATUSES))],

            'next_action' => 'nullable|string',

            'evidence' => $this->evidenceRule(),
        ]);

        // Tanggal aktual = tanggal selesai; hanya untuk Closed, default hari ini.
        $validated['actual_date'] = $validated['status'] === 'Closed'
            ? ($validated['actual_date'] ?? today()->toDateString())
            : null;

        unset($validated['evidence']);

        return $validated + $this->storeEvidence($request, $action, 'actions');
    }
}
