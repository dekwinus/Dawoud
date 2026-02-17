<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompetitorProduct extends Model
{
    protected $table = 'competitor_products';

    protected $fillable = [
        'competitor_name', 'product_name', 'code', 'price',
        'description', 'url', 'image', 'category_id',
        'brand_id', 'notes', 'is_active',
    ];

    protected $casts = [
        'price' => 'double',
        'category_id' => 'integer',
        'brand_id' => 'integer',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
}
