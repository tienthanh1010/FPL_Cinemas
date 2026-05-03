<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $table = 'roles';
    protected $fillable = ['code','name'];
    public function staff(): BelongsToMany { return $this->belongsToMany(Staff::class, 'staff_roles', 'role_id', 'staff_id'); }
}
