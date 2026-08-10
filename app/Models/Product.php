<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'stock',
        'image_path',
    ];

    // Store price as integer cents
    public function getPriceFormattedAttribute()
    {
        return number_format($this->price / 100, 2);
    }
}
