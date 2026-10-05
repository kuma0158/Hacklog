{{-- バグフォーム共通パーツ --}}
<div class="field" style="grid-column:1/-1">
    <label>タイトル <span style="color:var(--red)">*</span></label>
    <input name="title" value="{{ old('title', $bug?->title) }}" required>
</div>

<div class="field">
    <label>深刻度 <span style="color:var(--red)">*</span></label>
    <select name="severity">
        @foreach(['critical'=>'🔴 致命的 (critical)','high'=>'🟠 高 (high)','medium'=>'🟡 中 (medium)','low'=>'🟢 低 (low)'] as $k=>$v)
            <option value="{{ $k }}" @selected(old('severity', $bug?->severity ?? 'medium') === $k)>{{ $v }}</option>
        @endforeach
    </select>
</div>

<div class="field">
    <label>優先度 <span style="color:var(--red)">*</span></label>
    <select name="priority">
        @foreach(['critical'=>'最高','high'=>'高','normal'=>'中','low'=>'低'] as $k=>$v)
            <option value="{{ $k }}" @selected(old('priority', $bug?->priority ?? 'normal') === $k)>{{ $v }}</option>
        @endforeach
    </select>
</div>

<div class="field">
    <label>ステータス <span style="color:var(--red)">*</span></label>
    <select name="status">
        @foreach(['open'=>'未対応','investigating'=>'調査中','fixed'=>'修正済','verified'=>'確認済','closed'=>'クローズ'] as $k=>$v)
            <option value="{{ $k }}" @selected(old('status', $bug?->status ?? 'open') === $k)>{{ $v }}</option>
        @endforeach
    </select>
</div>

<div class="field">
    <label>担当者</label>
    <select name="assignee_id">
        <option value="">未設定</option>
        @foreach($users as $user)
            <option value="{{ $user->id }}" @selected(old('assignee_id', $bug?->assignee_id) == $user->id)>{{ $user->name }}</option>
        @endforeach
    </select>
</div>

<div class="field">
    <label>期限</label>
    <input type="date" name="due_date" value="{{ old('due_date', $bug?->due_date?->format('Y-m-d')) }}">
</div>

<div class="field">
    <label>環境情報 <span class="muted" style="font-size:11px">OS・ブラウザ・バージョン等</span></label>
    <input name="environment_info" value="{{ old('environment_info', $bug?->environment_info) }}" placeholder="例: Windows 11 / Chrome 120 / v2.3.1">
</div>

<div class="field" style="grid-column:1/-1">
    <label>詳細・概要</label>
    <textarea name="description" style="min-height:80px">{{ old('description', $bug?->description) }}</textarea>
</div>

<div class="field" style="grid-column:1/-1">
    <label>再現手順</label>
    <textarea name="steps_to_reproduce" style="min-height:100px" placeholder="1. ○○画面を開く&#10;2. △△ボタンをクリック&#10;3. エラーが発生する">{{ old('steps_to_reproduce', $bug?->steps_to_reproduce) }}</textarea>
</div>

<div class="field">
    <label>期待される動作</label>
    <textarea name="expected_behavior" style="min-height:80px">{{ old('expected_behavior', $bug?->expected_behavior) }}</textarea>
</div>

<div class="field">
    <label>実際の動作</label>
    <textarea name="actual_behavior" style="min-height:80px">{{ old('actual_behavior', $bug?->actual_behavior) }}</textarea>
</div>
