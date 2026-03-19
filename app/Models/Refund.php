<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    protected $table = 'refunds';
    protected $fillable = ['payment_id','amount','status','reason','external_ref'];
    public function payment(): BelongsTo { return $this->belongsTo(Payment::class, 'payment_id'); }
}
