<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bug extends Model
{
    use HasFactory;

    public const SEVERITIES = ['critical', 'high', 'medium', 'low'];
    public const PRIORITIES = ['critical', 'high', 'normal', 'low'];
    public const STATUSES   = ['open', 'investigating', 'fixed', 'verified', 'closed'];

    protected $fillable = [
        'issue_id', 'reporter_id', 'assignee_id',
        'title', 'description', 'severity', 'priority', 'status',
        'steps_to_reproduce', 'expected_behavior', 'actual_behavior',
        'environment_info', 'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return in_array($this->status, ['verified', 'closed']);
    }
}
