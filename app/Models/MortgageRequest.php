<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MortgageRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'house_id',
        'duration',
        'interest_id',
        'house_price',
        'bank_name',
        'interest',
        'dp_total_amount',
        'dp_percentage',
        'loan_total_amount',
        'loan_interest_total_amount',
        'monthly_amount',
        'status',
        'document',
    ];
    

    public function customer()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function interests()
    {
        return $this->belongsTo(Interest::class, 'interest_id');
    }


    public function house()
    {
        return $this->belongsTo(House::class, 'house_id');
    }

    public function installments()
    {
        return $this->hasMany(Installment::class);
    }

    public function getRemainingLoanAmountAttribute()
    {
        // check if there are any installments
        if ($this->installments()->count() === 0){
            // default to total loan interest amount if no installment exist
            return $this->loan_interest_total_amount;
        }

        // calculated the total paid amount from installments
        $totalPaid = $this->installments()
            ->where('is_paid', true)
            ->sum('sub_total_amount');

            return max($this->loan_interest_total_amount - $totalPaid, 0);
    }
}
