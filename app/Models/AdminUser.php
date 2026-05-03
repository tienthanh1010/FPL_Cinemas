<?php

namespace App\Models;

use App\Support\AdminPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AdminUser extends Model
{
    use HasFactory;

    protected $table = 'admin_users';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'admin_user_roles', 'admin_user_id', 'role_id')
            ->withTimestamps()
            ->orderBy('name');
    }

    /**
     * @return array<int, string>
     */
    public function roleCodes(): array
    {
        $roles = $this->relationLoaded('roles') ? $this->roles : $this->roles()->get();

        return $roles->pluck('code')->filter()->values()->all();
    }

    public function hasRole(string $roleCode): bool
    {
        return in_array($roleCode, $this->roleCodes(), true);
    }

    public function hasPermission(string $permission): bool
    {
        return AdminPermissions::hasPermission($this->roleCodes(), $permission);
    }
}
