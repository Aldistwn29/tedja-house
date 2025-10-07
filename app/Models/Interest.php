<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Interest extends Model
{
    use SoftDeletes;

    protected $fillable = ['house_id', 'bank_id', 'interest', 'duration'];

    public function house()
    {
        return $this->belongsTo(House::class, 'house_id');
    }

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }
}
