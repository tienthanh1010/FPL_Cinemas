<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    protected $table = 'equipment';
    protected $fillable = ['cinema_id','auditorium_id','code','name','equipment_type','status','installed_at'];
    protected $casts = ['installed_at' => 'date'];
    public function cinema(): BelongsTo { return $this->belongsTo(Cinema::class, 'cinema_id'); }
    public function auditorium(): BelongsTo { return $this->belongsTo(Auditorium::class, 'auditorium_id'); }
    public function maintenanceRequests(): HasMany { return $this->hasMany(MaintenanceRequest::class, 'equipment_id'); }
}
