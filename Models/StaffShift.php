<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class StaffShift extends Model
{
    protected $table = 'staff_shifts';
    protected $fillable = ['cinema_id','shift_date','start_time','end_time','note'];
    protected $casts = ['shift_date' => 'date'];
    public function cinema(): BelongsTo { return $this->belongsTo(Cinema::class, 'cinema_id'); }
    public function staff(): BelongsToMany { return $this->belongsToMany(Staff::class, 'shift_assignments', 'shift_id', 'staff_id'); }
}
