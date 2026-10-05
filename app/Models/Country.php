<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'ecommerce_enabled' => 'boolean',
    ];

    public function pages()
    {
        return $this->hasMany(Page::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function productPrices()
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function freeSessionEvent()
    {
        return $this->belongsTo(Event::class, 'free_session_event_id');
    }

    public function navItems()
    {
        return $this->hasMany(NavItem::class)->orderBy('sort_order');
    }

    public function headerItems()
    {
        return $this->navItems()->where('location', 'header');
    }

    public function footerItems()
    {
        return $this->navItems()->where('location', 'footer');
    }

    public function getCurrencySymbolAttribute(): string
    {
        return config('cms.currencies.' . $this->currency, $this->currency . ' ');
    }

    public function url(string $path = ''): string
    {
        return url('/' . $this->slug . ($path !== '' ? '/' . ltrim($path, '/') : ''));
    }

    public static function defaultCountry(): ?self
    {
        return static::where('is_active', true)->orderByDesc('is_default')->orderBy('sort_order')->first();
    }

    /** True when this country has its own header (custom HTML or menu items). */
    public function hasCustomHeader(): bool
    {
        return trim((string) $this->header_html) !== '' || $this->headerItems()->exists();
    }

    public function hasCustomFooter(): bool
    {
        return trim((string) $this->footer_html) !== '' || $this->footerItems()->exists();
    }
}
