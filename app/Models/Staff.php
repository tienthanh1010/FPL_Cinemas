<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    protected $table = 'staff';
    protected $fillable = ['public_id','cinema_id','staff_code','full_name','phone','email','status','hired_at'];
    protected $casts = ['hired_at' => 'date'];
    public function cinema(): BelongsTo { return $this->belongsTo(Cinema::class, 'cinema_id'); }
    public function roles(): BelongsToMany { return $this->belongsToMany(Role::class, 'staff_roles', 'staff_id', 'role_id'); }
    public function shifts(): BelongsToMany { return $this->belongsToMany(StaffShift::class, 'shift_assignments', 'staff_id', 'shift_id'); }
    public function maintenanceRequests(): HasMany { return $this->hasMany(MaintenanceRequest::class, 'requested_by'); }
}
