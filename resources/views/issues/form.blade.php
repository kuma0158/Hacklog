{{-- 案件フォーム共通パーツ（create / show 両用）--}}
<div class="field" style="grid-column:1/-1"><label>案件名</label><input name="summary" value="{{ old('summary', $issue?->summary) }}" required></div>

<div class="field">
    <label>ステータス</label>
    <select name="status">
        @foreach(['not_started' => '未着手', 'in_progress' => '着手', 'done' => '終了', 'blocked' => 'サスペンド'] as $k => $v)
            <option value="{{ $k }}" @selected(old('status', $issue?->status ?? 'not_started') === $k)>{{ $v }}</option>
        @endforeach
    </select>
</div>

<div class="field">
    <label>優先度</label>
    <select name="priority">
        @foreach(['critical' => '最高', 'high' => '高', 'normal' => '中', 'low' => '低'] as $k => $v)
            <option value="{{ $k }}" @selected(old('priority', $issue?->priority ?? 'normal') === $k)>{{ $v }}</option>
        @endforeach
    </select>
</div>

<div class="field">
    <label>担当者</label>
    <select name="assignee_id">
        <option value="">未設定</option>
        @foreach($users as $user)
            <option value="{{ $user->id }}" @selected(old('assignee_id', $issue?->assignee_id) == $user->id)>{{ $user->name }}</option>
        @endforeach
    </select>
</div>

<div class="field">
    <label>マイルストーン / バージョン</label>
    <select name="milestone_id">
        <option value="">未設定</option>
        @foreach($milestones as $milestone)
            <option value="{{ $milestone->id }}" @selected(old('milestone_id', $issue?->milestone_id) == $milestone->id)>{{ $milestone->name }}</option>
        @endforeach
    </select>
</div>

<div class="field"><label>開始日</label><input type="date" name="start_date" value="{{ old('start_date', $issue?->start_date?->format('Y-m-d')) }}"></div>
<div class="field"><label>期限</label><input type="date" name="due_date" value="{{ old('due_date', $issue?->due_date?->format('Y-m-d')) }}"></div>

<div class="field">
    <label>進捗 (フォールバック値) <span class="muted" style="font-size:11px">WBS登録時は自動集計値を表示</span></label>
    <input type="number" min="0" max="100" name="progress" value="{{ old('progress', $issue?->progress ?? 0) }}">
</div>

<div class="field" style="grid-column:1/-1"><label>詳細</label><textarea name="description">{{ old('description', $issue?->description) }}</textarea></div>
