<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyAccount extends Model
{
    protected $table = 'loyalty_accounts';
    protected $fillable = ['customer_id','tier_id','points_balance','lifetime_points'];
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class, 'customer_id'); }
}
