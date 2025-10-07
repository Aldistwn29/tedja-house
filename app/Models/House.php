<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class House extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'thumbnail',
        'certificate',
        'about',
        'price',
        'bedroom',
        'bathroom',
        'electric',
        'land_area',
        'building_area',
        'category_id',
        'city_id',
    ];

    // fungsi untuk slug
    public function setNameAttribute($value){
        $this->attributes['name'] = $value;
        $this->attributes['slug'] = Str::slug($value);
    }

    // relasi ke categori
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // relasi ke city
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    // ralasi ke house poto
    public function photos()
    {
        return $this->hasMany(HousePhoto::class);
    }

    // relasi ke interest
    public function interest()
    {
        return $this->hasMany(Interest::class);
    }

    // ralasi ke house fasiliti
    public function facilities()
    {
        return $this->hasMany(HouseFacility::class);
    }

    // relasi ke tabel morgagerequest
    public function morgageRequest()
    {
        return $this->hasMany(MortgageRequest::class);
    }
}
