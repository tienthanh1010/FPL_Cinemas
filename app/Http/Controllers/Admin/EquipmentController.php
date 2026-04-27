<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auditorium;
use App\Models\Cinema;
use App\Models\Equipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EquipmentController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));
        $equipment = Equipment::query()
            ->with(['cinema', 'auditorium'])
            ->withCount('maintenanceRequests')
            ->when($q !== '', fn ($query) => $query->where('name', 'like', "%{$q}%")->orWhere('code', 'like', "%{$q}%")->orWhere('equipment_type', 'like', "%{$q}%"))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();
        $report = [
            'total' => Equipment::count(),
            'active' => Equipment::where('status', 'ACTIVE')->count(),
            'maintenance' => Equipment::where('status', 'MAINTENANCE')->count(),
        ];
        return view('admin.equipment.index', compact('equipment', 'q', 'report'));
    }

    public function create(): View
    {
        return view('admin.equipment.create', $this->formData(new Equipment()));
    }

    public function store(Request $request): RedirectResponse
    {
        $equipment = Equipment::create($this->validateEquipment($request));
        return redirect()->route('admin.equipment.show', $equipment)->with('success', 'Đã thêm thiết bị.');
    }

    public function show(Equipment $equipment): View
    {
        $equipment->load(['cinema', 'auditorium', 'maintenanceRequests.requester']);
        return view('admin.equipment.show', compact('equipment'));
    }

    public function edit(Equipment $equipment): View
    {
        return view('admin.equipment.edit', $this->formData($equipment));
    }

    public function update(Request $request, Equipment $equipment): RedirectResponse
    {
        $equipment->update($this->validateEquipment($request, $equipment));
        return redirect()->route('admin.equipment.show', $equipment)->with('success', 'Đã cập nhật thiết bị.');
    }

    public function destroy(Equipment $equipment): RedirectResponse
    {
        $equipment->delete();
        return redirect()->route('admin.equipment.index')->with('success', 'Đã xoá thiết bị.');
    }

    private function formData(Equipment $equipment): array
    {
        return [
            'equipment' => $equipment,
            'cinemas' => Cinema::orderBy('name')->get(),
            'auditoriums' => Auditorium::orderBy('name')->get(),
        ];
    }

    private function validateEquipment(Request $request, ?Equipment $equipment = null): array
    {
        return $request->validate([
            'cinema_id' => ['required', 'integer', 'exists:cinemas,id'],
            'auditorium_id' => ['nullable', 'integer', 'exists:auditoriums,id'],
            'code' => ['required', 'string', 'max:64', Rule::unique('equipment', 'code')->ignore($equipment?->id)],
            'name' => ['required', 'string', 'max:255'],
            'equipment_type' => ['required', Rule::in(['PROJECTOR', 'AUDIO', 'AIR_CONDITIONER', 'SCREEN', 'LIGHTING', 'OTHER'])],
            'status' => ['required', Rule::in(['ACTIVE', 'MAINTENANCE', 'BROKEN', 'RETIRED'])],
            'installed_at' => ['nullable', 'date'],
        ]);
    }
}
