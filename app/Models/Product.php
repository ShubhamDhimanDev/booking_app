<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $guarded = [];

    protected $casts = [
        'images' => 'array',
        'track_stock' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'grants_free_session' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function prices()
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function freeSessionEvent()
    {
        return $this->belongsTo(Event::class, 'free_session_event_id');
    }

    public function priceFor(Country $country): ?ProductPrice
    {
        return $this->prices->firstWhere('country_id', $country->id);
    }

    public function scopeSoldIn($query, Country $country)
    {
        return $query->where('is_active', true)
            ->whereHas('prices', fn ($q) => $q->where('country_id', $country->id));
    }

    public function inStock(int $qty = 1): bool
    {
        return ! $this->track_stock || $this->stock_qty >= $qty;
    }

    public function getFirstImageAttribute(): ?string
    {
        return $this->images[0] ?? null;
    }

    /** Deletes uploaded files that are no longer referenced. */
    public static function deleteImageFiles(array $urls): void
    {
        $prefix = Storage::disk('public')->url('');
        foreach ($urls as $url) {
            if (str_starts_with($url, $prefix)) {
                Storage::disk('public')->delete(substr($url, strlen($prefix)));
            }
        }
    }
}
