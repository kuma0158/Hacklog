<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Milestone extends Model
{
    use HasFactory;

    protected $fillable = ['issue_id', 'name', 'kind', 'start_date', 'release_date', 'description'];

    protected $casts = [
        'start_date' => 'date',
        'release_date' => 'date',
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }
}
