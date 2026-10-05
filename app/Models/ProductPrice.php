<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    protected $guarded = [];

    protected $casts = [
        'mrp' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function getCurrencySymbolAttribute(): string
    {
        return config('cms.currencies.' . $this->currency, $this->currency . ' ');
    }

    public function getDiscountPercentAttribute(): int
    {
        return $this->mrp > 0 && $this->sale_price < $this->mrp
            ? (int) round((1 - $this->sale_price / $this->mrp) * 100)
            : 0;
    }
}
