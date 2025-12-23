<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventExclusion extends Model
{
    use HasFactory;

    protected $table = 'event_exclusions';

    protected $fillable = [
        'event_id',
        'date',
        'exclude_all',
        'times',
    ];

    protected $casts = [
        'times' => 'array',
        'exclude_all' => 'boolean',
        'date' => 'date:Y-m-d',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
