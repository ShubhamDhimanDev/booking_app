<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NavItem extends Model
{
    protected $guarded = [];

    protected $casts = ['opens_new_tab' => 'boolean'];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    public function href(): string
    {
        if ($this->page) {
            return $this->page->url();
        }

        return (string) $this->url;
    }
}
