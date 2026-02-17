<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentStatusLog extends Model
{
    protected $fillable = [
        'payment_sale_id', 'previous_status', 'new_status', 'changed_by', 'changed_at'
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function payment()
    {
        return $this->belongsTo(PaymentSale::class, 'payment_sale_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
