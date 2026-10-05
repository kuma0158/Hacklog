{{-- 機能要望フォーム共通パーツ --}}
<div class="field" style="grid-column:1/-1">
    <label>タイトル <span style="color:var(--red)">*</span></label>
    <input name="title" value="{{ old('title', $feature?->title) }}" required>
</div>

<div class="field">
    <label>優先度 <span style="color:var(--red)">*</span></label>
    <select name="priority">
        @foreach(['critical'=>'最高','high'=>'高','normal'=>'中','low'=>'低'] as $k=>$v)
            <option value="{{ $k }}" @selected(old('priority', $feature?->priority ?? 'normal') === $k)>{{ $v }}</option>
        @endforeach
    </select>
</div>

<div class="field">
    <label>ステータス <span style="color:var(--red)">*</span></label>
    <select name="status">
        @foreach(['proposed'=>'提案中','reviewing'=>'レビュー中','accepted'=>'承認済','rejected'=>'却下','in_progress'=>'開発中','done'=>'完了'] as $k=>$v)
            <option value="{{ $k }}" @selected(old('status', $feature?->status ?? 'proposed') === $k)>{{ $v }}</option>
        @endforeach
    </select>
</div>

<div class="field">
    <label>担当者</label>
    <select name="assignee_id">
        <option value="">未設定</option>
        @foreach($users as $user)
            <option value="{{ $user->id }}" @selected(old('assignee_id', $feature?->assignee_id) == $user->id)>{{ $user->name }}</option>
        @endforeach
    </select>
</div>

<div class="field">
    <label>期限</label>
    <input type="date" name="due_date" value="{{ old('due_date', $feature?->due_date?->format('Y-m-d')) }}">
</div>

<div class="field" style="grid-column:1/-1">
    <label>詳細・背景</label>
    <textarea name="description" style="min-height:80px">{{ old('description', $feature?->description) }}</textarea>
</div>

<div class="field" style="grid-column:1/-1">
    <label>ユーザーストーリー <span class="muted" style="font-size:11px">（〜として、〜したい、なぜなら〜）</span></label>
    <textarea name="user_story" style="min-height:80px" placeholder="開発者として、バグの再現手順を記録したい。なぜなら、調査コストを減らしたいからだ。">{{ old('user_story', $feature?->user_story) }}</textarea>
</div>
