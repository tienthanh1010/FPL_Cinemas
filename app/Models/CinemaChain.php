<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CinemaChain extends Model
{
    use HasFactory;

    protected $table = 'cinema_chains';

    protected $fillable = [
        'public_id',
        'chain_code',
        'name',
        'legal_name',
        'tax_code',
        'hotline',
        'email',
        'website',
        'status',
    ];

    public function cinemas(): HasMany
    {
        return $this->hasMany(Cinema::class, 'chain_id');
    }
}
