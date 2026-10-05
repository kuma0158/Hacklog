<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationItem extends Model
{
    use HasFactory;

    protected $fillable = ['issue_id', 'title', 'body', 'kind', 'read_at'];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }
}
