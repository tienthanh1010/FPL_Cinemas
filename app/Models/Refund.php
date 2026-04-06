<?php
<<<<<<< HEAD

=======
<<<<<<< HEAD

=======
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    protected $table = 'refunds';
<<<<<<< HEAD
=======
<<<<<<< HEAD
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561

    protected $fillable = [
        'payment_id',
        'amount',
        'status',
        'reason',
        'external_ref',
    ];

    protected $casts = [
        'amount' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }
<<<<<<< HEAD
=======
=======
    protected $fillable = ['payment_id','amount','status','reason','external_ref'];
    public function payment(): BelongsTo { return $this->belongsTo(Payment::class, 'payment_id'); }
>>>>>>> b5618e45f81aeb711d5a8795a20e6bc35d4cabb2
>>>>>>> 64d8c448b79abac0443c5ccf39a8cc0d12ef3561
}
