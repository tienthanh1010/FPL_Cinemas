<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Relations\BelongsTo;
=======
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    protected $table = 'customers';

    protected $fillable = [
<<<<<<< HEAD
        'public_id', 'user_id', 'full_name', 'phone', 'email', 'dob', 'gender', 'city', 'account_status',
=======
        'public_id', 'full_name', 'phone', 'email', 'dob', 'gender', 'city', 'account_status',
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
    ];

    protected $casts = [
        'dob' => 'date',
    ];

<<<<<<< HEAD


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

=======
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'customer_id');
    }

    public function loyaltyAccount(): HasOne
    {
        return $this->hasOne(LoyaltyAccount::class, 'customer_id');
    }
}
