<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auditorium;
use App\Models\Cinema;
use App\Models\Equipment;
use App\Models\MaintenanceRequest;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaintenanceRequestController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->get('status');
        $requests = MaintenanceRequest::query()
            ->with(['cinema', 'auditorium', 'equipment', 'requester'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('opened_at')
            ->paginate(15)
            ->withQueryString();
        $report = [
            'open' => MaintenanceRequest::where('status', 'OPEN')->count(),
            'in_progress' => MaintenanceRequest::where('status', 'IN_PROGRESS')->count(),
            'closed' => MaintenanceRequest::where('status', 'CLOSED')->count(),
        ];
        return view('admin.maintenance_requests.index', compact('requests', 'status', 'report'));
    }

    public function create(): View
    {
        return view('admin.maintenance_requests.create', $this->formData(new MaintenanceRequest()));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateRequestData($request);
        $maintenanceRequest = MaintenanceRequest::create($data);
        $this->syncEquipmentStatus($maintenanceRequest);

        return redirect()->route('admin.maintenance_requests.show', $maintenanceRequest)->with('success', 'Đã tạo yêu cầu bảo trì.');
    }

    public function show(MaintenanceRequest $maintenanceRequest): View
    {
        $maintenanceRequest->load(['cinema', 'auditorium', 'equipment', 'requester']);
        return view('admin.maintenance_requests.show', compact('maintenanceRequest'));
    }

    public function edit(MaintenanceRequest $maintenanceRequest): View
    {
        return view('admin.maintenance_requests.edit', $this->formData($maintenanceRequest));
    }

    public function update(Request $request, MaintenanceRequest $maintenanceRequest): RedirectResponse
    {
        $data = $this->validateRequestData($request);
        if (($data['status'] ?? null) === 'CLOSED' && empty($data['closed_at'])) {
            $data['closed_at'] = now();
        }
        $maintenanceRequest->update($data);
        $this->syncEquipmentStatus($maintenanceRequest->fresh());

        return redirect()->route('admin.maintenance_requests.show', $maintenanceRequest)->with('success', 'Đã cập nhật yêu cầu bảo trì.');
    }

    public function destroy(MaintenanceRequest $maintenanceRequest): RedirectResponse
    {
        $equipment = $maintenanceRequest->equipment;
        $maintenanceRequest->delete();
        if ($equipment) {
            $this->syncEquipmentStatusForEquipment($equipment);
        }

        return redirect()->route('admin.maintenance_requests.index')->with('success', 'Đã xoá yêu cầu bảo trì.');
    }

    private function formData(MaintenanceRequest $maintenanceRequest): array
    {
        return [
            'maintenanceRequest' => $maintenanceRequest,
            'cinemas' => Cinema::orderBy('name')->get(),
            'auditoriums' => Auditorium::orderBy('name')->get(),
            'equipmentItems' => Equipment::orderBy('name')->get(),
            'staffMembers' => Staff::orderBy('full_name')->get(),
        ];
    }

    private function validateRequestData(Request $request): array
    {
        $data = $request->validate([
            'cinema_id' => ['required', 'integer', 'exists:cinemas,id'],
            'auditorium_id' => ['nullable', 'integer', 'exists:auditoriums,id'],
            'equipment_id' => ['nullable', 'integer', 'exists:equipment,id'],
            'requested_by' => ['nullable', 'integer', 'exists:staff,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', Rule::in(['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'])],
            'status' => ['required', Rule::in(['OPEN', 'IN_PROGRESS', 'CLOSED', 'CANCELLED'])],
            'opened_at' => ['nullable', 'date'],
            'closed_at' => ['nullable', 'date'],
        ]) + [
            'opened_at' => $request->input('opened_at') ?: now(),
        ];

        if (! empty($data['equipment_id'])) {
            $equipment = Equipment::query()->find($data['equipment_id']);
            if ($equipment) {
                if ((int) $equipment->cinema_id !== (int) $data['cinema_id']) {
                    abort(422, 'Thiết bị không thuộc rạp đã chọn.');
                }
                if (! empty($data['auditorium_id']) && $equipment->auditorium_id && (int) $equipment->auditorium_id !== (int) $data['auditorium_id']) {
                    abort(422, 'Thiết bị không thuộc phòng chiếu đã chọn.');
                }
            }
        }

        return $data;
    }

    private function syncEquipmentStatus(?MaintenanceRequest $maintenanceRequest): void
    {
        if (! $maintenanceRequest?->equipment) {
            return;
        }

        $this->syncEquipmentStatusForEquipment($maintenanceRequest->equipment);
    }

    private function syncEquipmentStatusForEquipment(Equipment $equipment): void
    {
        if ($equipment->status === 'RETIRED') {
            return;
        }

        $hasActiveMaintenance = MaintenanceRequest::query()
            ->where('equipment_id', $equipment->id)
            ->whereIn('status', ['OPEN', 'IN_PROGRESS'])
            ->exists();

        if ($hasActiveMaintenance && $equipment->status !== 'BROKEN') {
            $equipment->update(['status' => 'MAINTENANCE']);
            return;
        }

        if (! $hasActiveMaintenance && $equipment->status === 'MAINTENANCE') {
            $equipment->update(['status' => 'ACTIVE']);
        }
    }
}

