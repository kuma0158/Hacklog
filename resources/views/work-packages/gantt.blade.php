@extends('layouts.app')
@section('content')
@php
    $statusLabels = ['not_started'=>'未着手','in_progress'=>'着手','done'=>'終了','blocked'=>'サスペンド'];
    $months = $days->groupBy(fn ($day) => $day->format('Y/m'));
@endphp
<div class="top">
    <div>
        <div class="h1">ガントチャート</div>
        <div class="sub">{{ $issue->issue_key }} {{ $issue->summary }} ／ {{ $chartStart->format('Y/m/d') }} - {{ $chartEnd->format('Y/m/d') }}</div>
    </div>
    <div class="actions">
        <a class="btn light" href="{{ route('work-packages.index', $issue) }}">WBS一覧</a>
        <a class="btn light" href="{{ route('work-packages.kanban', $issue) }}">カンバン</a>
        @can('update', $issue)
        <a class="btn" href="{{ route('work-packages.create', $issue) }}">WBS追加</a>
        @endcan
    </div>
</div>

@cannot('update', $issue)
    <div class="alert" role="status">この案件のガントチャートは閲覧のみです。日程のドラッグ変更は行えません。</div>
@endcannot

<section class="card gantt-page">
    <form method="get" action="{{ route('work-packages.gantt', $issue) }}" class="gantt-filters">
        <div class="field"><label>種別</label><select><option>すべて</option></select></div>
        <div class="field"><label>ステータス</label><select name="status"><option value="">すべて</option>@foreach($statusLabels as $key => $label)<option value="{{ $key }}" @selected(($filters['status'] ?? '') === $key)>{{ $label }}</option>@endforeach</select></div>
        <div class="field"><label>担当者</label><select name="assignee_id"><option value="">すべて</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(($filters['assignee_id'] ?? '') == $user->id)>{{ $user->name }}</option>@endforeach</select></div>
        <input type="hidden" name="from" value="{{ $filters['from'] ?? $chartStart->format('Y-m-d') }}">
        <input type="hidden" name="to" value="{{ $filters['to'] ?? $chartEnd->format('Y-m-d') }}">
        <div class="field"><label>&nbsp;</label><button class="btn light">表示更新</button></div>
    </form>
    <div class="gantt-tools">
        <div class="actions">
            <span class="muted">スケール</span>
            <span class="badge">日</span>
            <span class="badge">システム開発WBS</span>
            <button type="button" class="btn light small" id="gantt-toggle-all" hidden>すべて折りたたむ</button>
        </div>
        <div class="muted">
            @can('update', $issue)
                バー中央で移動、左右端で期間を変更できます。親タスクは中央から子タスクごと移動します。
            @else
                縦スクロールで案件行、横スクロールでカレンダーが追従します。
            @endcan
        </div>
    </div>

    <div class="gantt-scroll" style="--days:{{ $days->count() }}" data-chart-start="{{ $chartStart->format('Y-m-d') }}" data-chart-end="{{ $chartEnd->format('Y-m-d') }}">
        <div class="gantt-row gantt-row-head">
            <div class="gantt-cell-left gantt-corner">
                <span>件名</span><span>担当者</span><span>ステータス</span>
            </div>
            <div class="gantt-cell-right">
                <div class="month-row" style="grid-template-columns:@foreach($months as $monthDays) {{ $monthDays->count() * 28 }}px @endforeach">
                    @foreach($months as $month => $monthDays)
                        <div class="month-head">{{ $month }}</div>
                    @endforeach
                </div>
                <div class="day-row">
                    @foreach($days as $day)
                        <div class="day-cell {{ $day->isWeekend() ? 'weekend' : '' }} {{ $day->isToday() ? 'today' : '' }}">{{ $day->format('j') }}</div>
                    @endforeach
                </div>
            </div>
        </div>

        @forelse($workPackages as $package)
            @php
                $depth = $package->hierarchyDepth;
                $rowStart = $package->start_date ?? $chartStart;
                $rowEnd = $package->due_date ?? $rowStart;
                if ($rowEnd->lt($rowStart)) { $rowEnd = $rowStart; }
                $offset = max(0, $chartStart->diffInDays($rowStart, false));
                $span = max(1, $rowStart->diffInDays($rowEnd) + 1);
                $color = ['not_started'=>'#c0c0c0','in_progress'=>'#40a090','done'=>'#209070','blocked'=>'#d09020'][$package->status] ?? '#40a090';
                $hasSchedule = $package->start_date && $package->due_date;
                $isSummary = (int) ($package->children_count ?? 0) > 0;
                $progress = max(0, min(100, (int) $package->progress));
                $barHint = match (true) {
                    ! $hasSchedule => '開始日と終了日の両方を設定するとドラッグで変更できます',
                    $isSummary => 'バー中央をドラッグすると子タスクごと移動します',
                    default => 'バー中央で移動、左右端で期間変更',
                };
            @endphp
            <div class="gantt-row" data-depth="{{ $depth }}" data-wp-id="{{ $package->id }}">
                <div class="gantt-cell-left gantt-row-left">
                    <div class="gantt-name">
                        @if($depth === 0 && $isSummary)
                            <button type="button" class="gantt-toggle" aria-expanded="true" aria-label="子タスクの表示切替" title="子タスクの表示を切り替えます"></button>
                        @else
                            <span class="gantt-toggle-spacer"></span>
                        @endif
                        @can('update', $issue)
                        <a class="gantt-title" href="{{ route('work-packages.edit', [$issue, $package]) }}"><span class="wbs-indent" style="--depth:{{ $depth }}"></span>{{ $package->name }}</a>
                        @else
                        <span class="gantt-title"><span class="wbs-indent" style="--depth:{{ $depth }}"></span>{{ $package->name }}</span>
                        @endcan
                    </div>
                    <span><span class="avatar">{{ mb_substr($package->assignee?->name ?? '未', 0, 1) }}</span>{{ $package->assignee?->name ?? '未設定' }}</span>
                    <span class="status-pill status-{{ $package->status }}">{{ $statusLabels[$package->status] ?? $package->status }}</span>
                </div>
                <div class="gantt-cell-right gantt-row-days">
                    @foreach($days as $day)
                        <div class="line-day {{ $day->isWeekend() ? 'weekend' : '' }} {{ $day->isToday() ? 'today' : '' }}"></div>
                    @endforeach
                    @can('update', $issue)
                    <a class="gantt-bar @if($hasSchedule) is-schedulable @endif"
                       href="{{ route('work-packages.edit', [$issue, $package]) }}"
                       style="left:{{ $offset * 28 }}px;width:{{ $span * 28 }}px;background:{{ $color }}"
                       draggable="false"
                       title="{{ $barHint }}"
                       @if($hasSchedule)
                           data-wp-id="{{ $package->id }}"
                           data-start="{{ $package->start_date->format('Y-m-d') }}"
                           data-due="{{ $package->due_date->format('Y-m-d') }}"
                           data-reschedule-url="{{ route('work-packages.reschedule', [$issue, $package]) }}"
                           data-move-only="{{ $isSummary ? 'true' : 'false' }}"
                       @endif>
                        @if($hasSchedule && ! $isSummary)<span class="gantt-bar-handle start" data-handle="start"></span>@endif
                        <span class="gantt-bar-progress" style="width:{{ $progress }}%" title="進捗 {{ $progress }}%" aria-hidden="true"></span>
                        <span class="gantt-bar-label">{{ $issue->issue_key }} {{ $package->wbs_code }} {{ $package->name }}</span>
                        @if($hasSchedule && ! $isSummary)<span class="gantt-bar-handle end" data-handle="end"></span>@endif
                    </a>
                    @else
                    <div class="gantt-bar"
                         style="left:{{ $offset * 28 }}px;width:{{ $span * 28 }}px;background:{{ $color }}"
                         draggable="false">
                        <span class="gantt-bar-progress" style="width:{{ $progress }}%" title="進捗 {{ $progress }}%" aria-hidden="true"></span>
                        <span class="gantt-bar-label">{{ $issue->issue_key }} {{ $package->wbs_code }} {{ $package->name }}</span>
                    </div>
                    @endcan
                </div>
            </div>
        @empty
            <div class="muted" style="padding:18px">WBSはまだ登録されていません。</div>
        @endforelse
    </div>
</section>

@push('scripts')
<script>
// 第1レベル（トップレベル）のWPで、配下の子タスク行をまとめて折りたたむ。
// 行はサーバー側で深さ優先順に並んでいるので、あるトップレベル行の子孫は
// 「次に depth=0 の行が現れるまでの連続した行」で表せる。
(() => {
    const container = document.querySelector('.gantt-scroll');
    if (!container) return;

    const rows = Array.from(container.querySelectorAll('.gantt-row[data-wp-id]'));
    const toggleAllButton = document.getElementById('gantt-toggle-all');
    const storageKey = 'gantt.collapsed.{{ $issue->id }}';

    let collapsed = new Set();
    try {
        const saved = JSON.parse(window.localStorage.getItem(storageKey) || '[]');
        if (Array.isArray(saved)) collapsed = new Set(saved.map(String));
    } catch (e) {
        // localStorage が使えない環境では折りたたみ状態を保存しないだけで動作は継続する
    }

    // 各トップレベル行と、その配下の子孫行の対応表
    const groups = rows
        .map((row, index) => {
            if (!row.querySelector('.gantt-toggle')) return null;
            const children = [];
            for (let i = index + 1; i < rows.length; i++) {
                if (Number(rows[i].dataset.depth) <= 0) break;
                children.push(rows[i]);
            }
            return { row, children, toggle: row.querySelector('.gantt-toggle') };
        })
        .filter(Boolean);

    if (groups.length === 0) return;

    function render() {
        groups.forEach(({ row, children, toggle }) => {
            const isCollapsed = collapsed.has(row.dataset.wpId);
            toggle.setAttribute('aria-expanded', isCollapsed ? 'false' : 'true');
            children.forEach((child) => child.classList.toggle('is-collapsed-child', isCollapsed));
        });

        if (toggleAllButton) {
            const allCollapsed = groups.every(({ row }) => collapsed.has(row.dataset.wpId));
            toggleAllButton.hidden = false;
            toggleAllButton.textContent = allCollapsed ? 'すべて展開' : 'すべて折りたたむ';
        }
    }

    function persist() {
        try {
            window.localStorage.setItem(storageKey, JSON.stringify([...collapsed]));
        } catch (e) {
            // 保存できなくても表示自体は切り替わっているので無視する
        }
    }

    groups.forEach(({ row, toggle }) => {
        toggle.addEventListener('click', () => {
            const id = row.dataset.wpId;
            if (collapsed.has(id)) {
                collapsed.delete(id);
            } else {
                collapsed.add(id);
            }
            render();
            persist();
        });
    });

    if (toggleAllButton) {
        toggleAllButton.addEventListener('click', () => {
            const allCollapsed = groups.every(({ row }) => collapsed.has(row.dataset.wpId));
            collapsed = allCollapsed
                ? new Set()
                : new Set(groups.map(({ row }) => row.dataset.wpId));
            render();
            persist();
        });
    }

    render();
})();
</script>
@endpush

@can('update', $issue)
@push('scripts')
<script>
(() => {
    const DAY_WIDTH = 28;
    const container = document.querySelector('.gantt-scroll');
    if (!container) return;

    const chartStart = parseISODate(container.dataset.chartStart);
    const chartEnd = parseISODate(container.dataset.chartEnd);
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    function parseISODate(str) {
        const [y, m, d] = str.split('-').map(Number);
        return new Date(y, m - 1, d);
    }

    function formatISODate(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function formatMD(date) {
        return `${date.getMonth() + 1}/${date.getDate()}`;
    }

    function addDays(date, days) {
        const d = new Date(date);
        d.setDate(d.getDate() + days);
        return d;
    }

    function diffInDays(from, to) {
        return Math.round((to.getTime() - from.getTime()) / 86400000);
    }

    function showToast(message, isError) {
        const el = document.createElement('div');
        el.className = 'alert';
        el.style.position = 'fixed';
        el.style.top = '15px';
        el.style.right = '15px';
        el.style.zIndex = 300;
        el.style.boxShadow = 'var(--shadow-mid)';
        if (isError) {
            el.style.background = '#f0d0c0';
            el.style.color = '#a05040';
            el.style.border = '1px solid #e08070';
        }
        el.textContent = message;
        document.body.appendChild(el);
        setTimeout(() => el.remove(), 2500);
    }

    let tip = null;
    function showTip(x, y, text) {
        if (!tip) {
            tip = document.createElement('div');
            tip.className = 'gantt-drag-tip';
            document.body.appendChild(tip);
        }
        tip.textContent = text;
        tip.style.left = `${x}px`;
        tip.style.top = `${y}px`;
    }
    function hideTip() {
        if (tip) {
            tip.remove();
            tip = null;
        }
    }

    let state = null;
    let suppressClickOn = null;

    container.addEventListener('pointerdown', (event) => {
        const bar = event.target.closest('.gantt-bar.is-schedulable');
        if (!bar) return;

        const handle = event.target.closest('.gantt-bar-handle');
        const mode = handle ? handle.dataset.handle : 'move';

        event.preventDefault();
        bar.setPointerCapture(event.pointerId);

        state = {
            bar,
            mode,
            pointerId: event.pointerId,
            startX: event.clientX,
            trueStart: parseISODate(bar.dataset.start),
            trueDue: parseISODate(bar.dataset.due),
            origLeft: bar.offsetLeft,
            origWidth: bar.offsetWidth,
            newStart: null,
            newDue: null,
            dragged: false,
        };
        bar.classList.add('is-dragging');
    });

    container.addEventListener('pointermove', (event) => {
        if (!state || event.pointerId !== state.pointerId) return;

        event.preventDefault();

        const deltaPx = event.clientX - state.startX;
        const deltaDays = Math.round(deltaPx / DAY_WIDTH);
        if (deltaDays !== 0) state.dragged = true;

        // 実際に保存する日付は必ず「元の日付 + 移動日数」から計算する(画面外に一部だけ
        // 表示されているバーでも、隠れている側の日付を書き換えてしまわないようにするため)。
        let newStart = state.trueStart;
        let newDue = state.trueDue;

        if (state.mode === 'move') {
            newStart = addDays(state.trueStart, deltaDays);
            newDue = addDays(state.trueDue, deltaDays);
        } else if (state.mode === 'start') {
            newStart = addDays(state.trueStart, deltaDays);
            if (newStart > state.trueDue) newStart = state.trueDue;
        } else if (state.mode === 'end') {
            newDue = addDays(state.trueDue, deltaDays);
            if (newDue < state.trueStart) newDue = state.trueStart;
        }

        state.newStart = newStart;
        state.newDue = newDue;

        // 表示位置はチャート表示範囲より前に出ないようクランプする(そうしないと、表示範囲外の
        // 開始日を持つバーをリサイズした際に left が大きな負の値になり画面外に消えてしまう)。
        // 幅は表示範囲に関係なく実際の期間(newStart~newDue)で計算する(サーバー側の初期描画と
        // 同じ考え方。ここをチャート範囲でクランプすると、両端が表示範囲外のバーが1日分に
        // 潰れてしまう)。
        const trueSpanDays = Math.max(1, diffInDays(newStart, newDue) + 1);
        const visualStartDays = Math.max(0, diffInDays(chartStart, newStart));

        state.bar.style.left = `${visualStartDays * DAY_WIDTH}px`;
        state.bar.style.width = `${trueSpanDays * DAY_WIDTH}px`;

        showTip(event.clientX, event.clientY, `${formatMD(newStart)} - ${formatMD(newDue)}`);
    });

    container.addEventListener('pointerup', (event) => {
        if (!state || event.pointerId !== state.pointerId) return;

        const { bar, dragged, origLeft, origWidth, newStart, newDue } = state;
        bar.classList.remove('is-dragging');
        hideTip();
        state = null;

        if (!dragged) return;

        suppressClickOn = bar;

        fetch(bar.dataset.rescheduleUrl, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ start_date: formatISODate(newStart), due_date: formatISODate(newDue) }),
        })
            .then(async (res) => {
                if (!res.ok) throw await res.json().catch(() => ({}));
                return res.json();
            })
            .then((data) => {
                bar.dataset.start = data.start_date;
                bar.dataset.due = data.due_date;

                // サーバー側では親WPの期間も連動して変わりうる（子の期間を包含するよう
                // 上書き／祖先へ伝播）ため、ドラッグしたバー自身を書き換えるだけでは
                // 画面全体を最新状態に保てない。「表示更新」を押さなくても済むよう、
                // 常にページを再読み込みして最新のデータで描画し直す。表示範囲(上部の
                // 開始日/終了日)を超えていた場合はその範囲も広げてから再読み込みする。
                const updatedStart = parseISODate(data.start_date);
                const updatedDue = parseISODate(data.due_date);
                const url = new URL(window.location.href);
                const newFrom = updatedStart < chartStart ? updatedStart : chartStart;
                const newTo = updatedDue > chartEnd ? updatedDue : chartEnd;
                url.searchParams.set('from', formatISODate(newFrom));
                url.searchParams.set('to', formatISODate(newTo));

                if (url.href !== window.location.href) {
                    window.location.href = url.href;
                } else {
                    window.location.reload();
                }
            })
            .catch((err) => {
                bar.style.left = `${origLeft}px`;
                bar.style.width = `${origWidth}px`;
                showToast(err?.message ?? 'スケジュールの更新に失敗しました。', true);
            });
    });

    container.addEventListener('pointercancel', (event) => {
        if (!state || event.pointerId !== state.pointerId) return;

        state.bar.style.left = `${state.origLeft}px`;
        state.bar.style.width = `${state.origWidth}px`;
        state.bar.classList.remove('is-dragging');
        hideTip();
        state = null;
    });

    document.addEventListener('click', (event) => {
        if (suppressClickOn && event.target.closest('.gantt-bar') === suppressClickOn) {
            event.preventDefault();
            suppressClickOn = null;
        }
    }, true);
})();
</script>
@endpush
@endcan
@endsection
