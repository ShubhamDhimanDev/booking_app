<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    protected $guarded = [];

    protected $casts = [
        'images' => 'array',
        'content' => 'array',
        'track_stock' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'grants_free_session' => 'boolean',
    ];

    /** Single-value text fields of the landing-page content. */
    public const CONTENT_TEXT = [
        'subtitle', 'purpose', 'consult_url', 'benefits_intro', 'about_heading', 'about',
        'steps_heading', 'energy_quote', 'note_quote', 'not_intro',
    ];

    /** Lists of plain strings. */
    public const CONTENT_LISTS = [
        'benefits', 'highlights', 'who_yes', 'who_no', 'signs', 'energy_checks', 'expect_yes', 'expect_no',
    ];

    /** Repeating rows: key => row fields. */
    public const CONTENT_ROWS = [
        'benefit_cards' => ['title', 'body'],
        'steps' => ['title', 'body'],
        'usage' => ['title', 'body'],
        'not_cards' => ['title', 'body'],
        'specs' => ['key', 'value'],
        'faq' => ['q', 'a'],
        'reviews' => ['name', 'location', 'text', 'tag', 'rating'],
    ];

    /** Trims the submitted landing-page content and drops empty items. Returns null when nothing is left. */
    public static function sanitizeContent(?array $raw): ?array
    {
        $raw = (array) $raw;
        $clean = [];

        foreach (self::CONTENT_TEXT as $key) {
            $value = trim((string) ($raw[$key] ?? ''));
            if ($value !== '') {
                $clean[$key] = $value;
            }
        }

        foreach (self::CONTENT_LISTS as $key) {
            $items = array_values(array_filter(array_map(fn ($v) => trim((string) $v), (array) ($raw[$key] ?? []))));
            if ($items) {
                $clean[$key] = $items;
            }
        }

        foreach (self::CONTENT_ROWS as $key => $fields) {
            $rows = [];
            foreach ((array) ($raw[$key] ?? []) as $row) {
                $row = (array) $row;
                $item = [];
                foreach ($fields as $field) {
                    $item[$field] = trim((string) ($row[$field] ?? ''));
                }
                if ($key === 'reviews') {
                    $item['rating'] = max(1, min(5, (int) ($item['rating'] ?: 5)));
                    $filled = $item['text'] !== '';
                } else {
                    $filled = $item[$fields[0]] !== '' || $item[$fields[1]] !== '';
                }
                if ($filled) {
                    $rows[] = $item;
                }
            }
            if ($rows) {
                $clean[$key] = $rows;
            }
        }

        return $clean ?: null;
    }

    /** One value of the landing-page content, or $default when it is not set. */
    public function content(string $key, $default = null)
    {
        $value = $this->content[$key] ?? null;

        return $value === null || $value === [] || $value === '' ? $default : $value;
    }

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
