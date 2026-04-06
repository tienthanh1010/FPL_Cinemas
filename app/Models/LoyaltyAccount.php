<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Relations\HasMany;
=======
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561

class LoyaltyAccount extends Model
{
    protected $table = 'loyalty_accounts';
<<<<<<< HEAD

    protected $fillable = ['customer_id', 'tier_id', 'points_balance', 'lifetime_points'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(LoyaltyTier::class, 'tier_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(LoyaltyTransaction::class, 'loyalty_account_id');
    }
=======
    protected $fillable = ['customer_id','tier_id','points_balance','lifetime_points'];
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class, 'customer_id'); }
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
}
