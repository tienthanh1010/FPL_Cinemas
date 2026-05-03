<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceRequest extends Model
{
    protected $table = 'maintenance_requests';
    protected $fillable = ['cinema_id','auditorium_id','equipment_id','requested_by','title','description','priority','status','opened_at','closed_at'];
    protected $casts = ['opened_at' => 'datetime', 'closed_at' => 'datetime'];
    public function cinema(): BelongsTo { return $this->belongsTo(Cinema::class, 'cinema_id'); }
    public function auditorium(): BelongsTo { return $this->belongsTo(Auditorium::class, 'auditorium_id'); }
    public function equipment(): BelongsTo { return $this->belongsTo(Equipment::class, 'equipment_id'); }
    public function requester(): BelongsTo { return $this->belongsTo(Staff::class, 'requested_by'); }
}
