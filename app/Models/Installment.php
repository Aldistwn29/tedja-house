<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Installment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'mortgage_request_id',
        'no_of_payment',
        'total_taxt_amount',
        'grand_total_amount',
        'sub_total_amount',
        'insurance_amount',
        'proof',
        'is_paid',
        'payment_type',
        'remaining_loan_amount',
    ];
}
