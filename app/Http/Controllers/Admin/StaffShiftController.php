<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\Staff;
use App\Models\StaffShift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffShiftController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->get('date');
        $shifts = StaffShift::query()
            ->with(['cinema', 'staff.roles'])
            ->when($date, fn ($q) => $q->whereDate('shift_date', $date))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
        $report = [
            'total' => StaffShift::count(),
            'today' => StaffShift::whereDate('shift_date', today())->count(),
            'assigned' => StaffShift::has('staff')->count(),
        ];
        return view('admin.staff_shifts.index', compact('shifts', 'date', 'report'));
    }

    public function create(): View
    {
        return view('admin.staff_shifts.create', $this->formData(new StaffShift()));
    }

    public function store(Request $request): RedirectResponse
    {
        $shift = StaffShift::create($this->validateShift($request));
        $shift->staff()->sync($request->input('staff_ids', []));
        return redirect()->route('admin.staff_shifts.show', $shift)->with('success', 'Đã tạo ca làm.');
    }

    public function show(StaffShift $staffShift): View
    {
        $staffShift->load(['cinema', 'staff.roles']);
        return view('admin.staff_shifts.show', ['shift' => $staffShift]);
    }

    public function edit(StaffShift $staffShift): View
    {
        $staffShift->load('staff');
        return view('admin.staff_shifts.edit', $this->formData($staffShift));
    }

    public function update(Request $request, StaffShift $staffShift): RedirectResponse
    {
        $staffShift->update($this->validateShift($request));
        $staffShift->staff()->sync($request->input('staff_ids', []));
        return redirect()->route('admin.staff_shifts.show', $staffShift)->with('success', 'Đã cập nhật ca làm.');
    }

    public function destroy(StaffShift $staffShift): RedirectResponse
    {
        $staffShift->staff()->detach();
        $staffShift->delete();
        return redirect()->route('admin.staff_shifts.index')->with('success', 'Đã xoá ca làm.');
    }

    private function formData(StaffShift $shift): array
    {
        return [
            'shift' => $shift,
            'cinemas' => Cinema::orderBy('name')->get(),
            'staffMembers' => Staff::with('roles')->where('status', 'ACTIVE')->orderBy('full_name')->get(),
        ];
    }

    private function validateShift(Request $request): array
    {
        return $request->validate([
            'cinema_id' => ['required', 'integer', 'exists:cinemas,id'],
            'shift_date' => ['required', 'date'],
            'start_time' => ['required'],
            'end_time' => ['required', 'after:start_time'],
            'note' => ['nullable', 'string', 'max:255'],
            'staff_ids' => ['nullable', 'array'],
            'staff_ids.*' => ['integer', 'exists:staff,id'],
        ]);
    }
}
