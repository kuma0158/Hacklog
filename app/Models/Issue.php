<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Issue extends Model
{
    use HasFactory;

    protected $fillable = [
        'milestone_id', 'assignee_id', 'issue_key', 'type', 'summary',
        'description', 'status', 'priority', 'start_date', 'due_date', 'progress',
        'watch_count', 'star_count',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'due_date'    => 'date',
        'archived_at' => 'datetime',
    ];

    /** アーカイブされていない案件のみ */
    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    /** アーカイブ済みの案件のみ */
    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(IssueComment::class);
    }

    public function workPackages(): HasMany
    {
        return $this->hasMany(WorkPackage::class);
    }

    public function wikiPages(): HasMany
    {
        return $this->hasMany(WikiPage::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProjectFile::class);
    }

    public function repositories(): HasMany
    {
        return $this->hasMany(Repository::class);
    }

    public function subMilestones(): HasMany
    {
        return $this->hasMany(Milestone::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(NotificationItem::class);
    }

    /** バグ一覧（この案件に紐づく） */
    public function bugs(): HasMany
    {
        return $this->hasMany(Bug::class);
    }

    /** 機能要望一覧（この案件に紐づく） */
    public function featureRequests(): HasMany
    {
        return $this->hasMany(FeatureRequest::class);
    }

    /**
     * 案件全体の進捗 = top-level WP (parent_id IS NULL) の進捗平均。
     */
    public function overallProgress(): int
    {
        if (array_key_exists('work_packages_avg_progress', $this->attributes)) {
            $avg = $this->attributes['work_packages_avg_progress'];
            if ($avg !== null) {
                return (int) round((float) $avg);
            }
        }

        if ($this->relationLoaded('workPackages')) {
            $rootWps = $this->workPackages->whereNull('parent_id');
            if ($rootWps->isNotEmpty()) {
                return (int) round($rootWps->avg('progress'));
            }
        }

        $rootWps = $this->workPackages()->whereNull('parent_id')->pluck('progress');
        if ($rootWps->isNotEmpty()) {
            return (int) round((float) $rootWps->avg());
        }

        return (int) $this->progress;
    }

    public function hasWorkPackages(): bool
    {
        if (array_key_exists('work_packages_count', $this->attributes)) {
            return (int) $this->attributes['work_packages_count'] > 0;
        }
        if ($this->relationLoaded('workPackages')) {
            return $this->workPackages->isNotEmpty();
        }
        return $this->workPackages()->exists();
    }
}
