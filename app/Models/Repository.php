<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Repository extends Model
{
    use HasFactory;

    protected $fillable = ['issue_id', 'name', 'type', 'url', 'description'];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }
}
