<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeatureRequest extends Model
{
    use HasFactory;

    public const PRIORITIES = ['critical', 'high', 'normal', 'low'];
    public const STATUSES   = ['proposed', 'reviewing', 'accepted', 'rejected', 'in_progress', 'done'];

    protected $fillable = [
        'issue_id', 'requester_id', 'assignee_id',
        'title', 'description', 'user_story',
        'priority', 'status', 'vote_count', 'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(FeatureRequestVote::class);
    }

    public function hasVotedBy(int $userId): bool
    {
        if ($this->relationLoaded('votes')) {
            return $this->votes->contains('user_id', $userId);
        }
        return $this->votes()->where('user_id', $userId)->exists();
    }
}
