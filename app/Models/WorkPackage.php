<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkPackage extends Model
{
    use HasFactory;

    /** Display-only depth assigned by inHierarchyOrder(). */
    public int $hierarchyDepth = 0;

    /** Display-only sequential position assigned by inHierarchyOrder(). */
    public int $hierarchyPosition = 0;

    protected $fillable = [
        'issue_id',
        'parent_id',
        'assignee_id',
        'wbs_code',
        'name',
        'description',
        'deliverable',
        'status',
        'progress',
        'start_date',
        'due_date',
        'sort_order',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('wbs_code');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /** Return every descendant id without trusting the WBS code or tree depth. */
    public function descendantIds(): array
    {
        $visited = [$this->getKey() => true];
        $descendantIds = [];
        $parentIds = [$this->getKey()];

        while ($parentIds !== []) {
            $childIds = self::query()
                ->where('issue_id', $this->issue_id)
                ->whereIn('parent_id', $parentIds)
                ->pluck($this->getKeyName());
            $parentIds = [];

            foreach ($childIds as $childId) {
                $childId = (int) $childId;
                if (isset($visited[$childId])) {
                    continue;
                }

                $visited[$childId] = true;
                $descendantIds[] = $childId;
                $parentIds[] = $childId;
            }
        }

        return $descendantIds;
    }

    /**
     * Arrange a flat collection as a depth-first hierarchy: parent, children,
     * grandchildren, then the next sibling/root. Siblings use their automatic
     * sort order, natural WBS code order, and finally their primary key.
     */
    public static function inHierarchyOrder(EloquentCollection $workPackages): EloquentCollection
    {
        $sorted = $workPackages->sort(function (self $left, self $right): int {
            $sortComparison = (int) $left->sort_order <=> (int) $right->sort_order;
            if ($sortComparison !== 0) {
                return $sortComparison;
            }

            $codeComparison = strnatcasecmp((string) $left->wbs_code, (string) $right->wbs_code);

            return $codeComparison !== 0
                ? $codeComparison
                : $left->getKey() <=> $right->getKey();
        })->values();

        $packagesById = $sorted->keyBy(fn (self $package) => $package->getKey());
        $childrenByParent = $sorted->groupBy(
            fn (self $package) => $package->parent_id === null
                ? 'root'
                : 'parent:'.$package->parent_id,
        );

        $ordered = new EloquentCollection;
        $visited = [];

        $roots = $sorted->filter(
            fn (self $package) => $package->parent_id === null
                || ! $packagesById->has($package->parent_id),
        );

        $starts = $roots->concat($sorted)->values();

        foreach ($starts as $start) {
            if (isset($visited[$start->getKey()])) {
                continue;
            }

            $stack = [[$start, 0]];

            while ($stack !== []) {
                [$package, $depth] = array_pop($stack);
                $id = $package->getKey();
                if (isset($visited[$id])) {
                    continue;
                }

                $visited[$id] = true;
                $package->hierarchyDepth = $depth;
                $package->hierarchyPosition = $ordered->count() + 1;
                $ordered->push($package);

                $children = $childrenByParent->get('parent:'.$id, collect());
                foreach ($children->reverse() as $child) {
                    $stack[] = [$child, $depth + 1];
                }
            }
        }

        return $ordered;
    }

    /**
     * Recompute this WP's progress as the average of its children's progress.
     * Returns true if the value changed (or had to be written), false if leaf or unchanged.
     */
    public function recomputeFromChildren(): bool
    {
        $values = $this->children()->pluck('progress');
        if ($values->isEmpty()) {
            return false;
        }
        $new = (int) round((float) $values->avg());
        if ((int) $this->progress === $new) {
            return false;
        }
        $this->progress = $new;
        $this->save();

        return true;
    }

    /**
     * Walk up the parent chain and recompute each ancestor's progress.
     * Always reaches the root so a touched branch also repairs stale ancestors.
     */
    public function cascadeProgressUp(): void
    {
        $visited = [$this->getKey() => true];
        $node = $this->parent;

        while ($node && ! isset($visited[$node->getKey()])) {
            $visited[$node->getKey()] = true;
            $node->recomputeFromChildren();
            $node = $node->parent;
        }
    }

    /**
     * 子タスクが1つでも動き出したら、親タスクも「着手」に引き上げる。
     * 引き上げるのは「未着手」の親だけで、手動で「終了」「サスペンド」にした親は
     * 上書きしないし、着手済みの親を未着手へ戻すこともしない（昇格のみの片方向）。
     * Returns true if the value changed, false if leaf or unchanged.
     */
    public function recomputeStatusFromChildren(): bool
    {
        if ($this->status !== 'not_started') {
            return false;
        }

        // 「終了」の子は着手を経ずに直接終了にされた場合も含めて着手済みとみなす。
        $startedChildren = $this->children()
            ->whereIn('status', ['in_progress', 'done'])
            ->exists();

        if (! $startedChildren) {
            return false;
        }

        $this->status = 'in_progress';
        $this->save();

        return true;
    }

    /**
     * Walk up the parent chain and promote every ancestor that is still not started.
     * A single started leaf therefore marks its whole branch as in progress.
     */
    public function cascadeStatusUp(): void
    {
        $visited = [$this->getKey() => true];
        $node = $this->parent;

        while ($node && ! isset($visited[$node->getKey()])) {
            $visited[$node->getKey()] = true;
            $node->recomputeStatusFromChildren();
            $node = $node->parent;
        }
    }

    /**
     * Recompute this WP's dates to exactly cover its children's schedules.
     * The parent starts with the earliest child and ends with the latest child,
     * so it also shrinks when children move inward or their schedules shorten.
     * A side no child has a date for is left on the parent's own value.
     * Returns true if the value changed, false if leaf or unchanged.
     */
    public function recomputeDatesFromChildren(): bool
    {
        $children = $this->children()->get(['start_date', 'due_date']);
        if ($children->isEmpty()) {
            return false;
        }

        $childrenStart = $children->pluck('start_date')->filter()->min();
        $childrenDue = $children->pluck('due_date')->filter()->max();

        // 子が誰もその側の日付を持っていなければ集計しようがないので、自身の値をそのまま残す。
        // ここで null を書き込むと、日付未入力の子を1件追加しただけで親に入力済みの日程が消え、
        // ガントチャートでも「開始日・終了日が揃ったバー」だけがドラッグ対象のため動かせなくなる。
        $newStart = $childrenStart ?? $this->start_date;
        $newDue = $childrenDue ?? $this->due_date;

        // 片側だけを子から引き継ぐと開始日 > 終了日になりうるので、子から求めた側を優先して寄せる。
        if ($newStart && $newDue && $newStart->greaterThan($newDue)) {
            if ($childrenStart) {
                $newDue = $newStart->copy();
            } else {
                $newStart = $newDue->copy();
            }
        }

        $startChanged = $this->start_date?->toDateString() !== $newStart?->toDateString();
        $dueChanged = $this->due_date?->toDateString() !== $newDue?->toDateString();
        if (! $startChanged && ! $dueChanged) {
            return false;
        }

        $this->start_date = $newStart;
        $this->due_date = $newDue;
        $this->save();

        return true;
    }

    /**
     * Walk up the parent chain and recompute each ancestor's dates.
     * Always reaches the root so schedules left stale by older aggregation rules
     * are repaired the next time any descendant is changed.
     */
    public function cascadeDatesUp(): void
    {
        $visited = [$this->getKey() => true];
        $node = $this->parent;

        while ($node && ! isset($visited[$node->getKey()])) {
            $visited[$node->getKey()] = true;
            $node->recomputeDatesFromChildren();
            $node = $node->parent;
        }
    }

    /**
     * Shift this WP's entire subtree by the same number of days, keeping every
     * descendant's schedule relative to this WP unchanged (used when the whole
     * bar is dragged/moved, as opposed to stretched).
     */
    public function shiftDescendantDates(int $days): void
    {
        if ($days === 0) {
            return;
        }

        foreach ($this->children()->get() as $child) {
            if ($child->start_date) {
                $child->start_date = $child->start_date->copy()->addDays($days);
            }
            if ($child->due_date) {
                $child->due_date = $child->due_date->copy()->addDays($days);
            }
            $child->save();
            $child->shiftDescendantDates($days);
        }
    }
}
