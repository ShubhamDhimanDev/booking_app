<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $guarded = [];

    protected $casts = ['is_home' => 'boolean'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function sections()
    {
        return $this->hasMany(PageSection::class)->orderBy('sort_order')->orderBy('id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function url(): string
    {
        return $this->is_home ? $this->country->url() : $this->country->url($this->slug);
    }

    /**
     * Legacy behaviour: a page that is just one full HTML document
     * (doctype / html tag) is sent as-is, without the site layout.
     */
    public function fullDocumentHtml(): ?string
    {
        $visible = $this->sections->where('is_visible', true)->values();
        if ($visible->count() !== 1 || $visible[0]->type !== 'html') {
            return null;
        }
        $html = trim((string) ($visible[0]->content['html'] ?? ''));

        return preg_match('/^<(!doctype|html)\b/i', $html) ? $html : null;
    }
}
