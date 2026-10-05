<div class="field"><label>WBSコード</label><input name="wbs_code" value="{{ old('wbs_code', $package?->wbs_code) }}" placeholder="1.1"></div>
<div class="field"><label>親ワークパッケージ</label><select name="parent_id"><option value="">なし</option>@foreach($workPackages as $parent)<option value="{{ $parent->id }}" @selected(old('parent_id', $package?->parent_id) == $parent->id)>{{ str_repeat('— ', $parent->hierarchyDepth) }}{{ $parent->wbs_code }} {{ $parent->name }}</option>@endforeach</select></div>
<div class="field"><label>名称</label><input name="name" value="{{ old('name', $package?->name) }}"></div>
<div class="field"><label>担当者</label><select name="assignee_id"><option value="">未設定</option>@foreach($users as $user)<option value="{{ $user->id }}" @selected(old('assignee_id', $package?->assignee_id) == $user->id)>{{ $user->name }}</option>@endforeach</select></div>
@php
    $childCount = $package ? $package->children()->count() : 0;
    $isParentWp = $childCount > 0;
    $hasStartedChild = $isParentWp && $package->children()->whereIn('status', ['in_progress', 'done'])->exists();
@endphp
<div class="field">
    <label>ステータス@if($hasStartedChild) — 着手済みの子WBSがあるため「未着手」には戻せません@endif</label>
    <select name="status">@foreach(['not_started'=>'未着手','in_progress'=>'着手','done'=>'終了','blocked'=>'サスペンド'] as $key => $label)<option value="{{ $key }}" @selected(old('status', $package?->status ?? 'not_started') === $key)>{{ $label }}</option>@endforeach</select>
</div>
<div class="field">
    <label>進捗 (%) —
        @if($isParentWp)
            子WBS {{ $childCount }} 件の平均から自動算出 (編集不可)
        @else
            入力した値が親WBS・案件全体の集計に反映されます
        @endif
    </label>
    <div style="display:flex;gap:6px;align-items:center">
        <input id="progress-input" name="progress" type="number" min="0" max="100" step="5"
            value="{{ old('progress', $package?->progress ?? 0) }}"
            @if($isParentWp) readonly style="flex:1;background:#f1f5f9;cursor:not-allowed;color:#475569" @else style="flex:1" @endif>
        <span style="font-weight:700;color:#475569">%</span>
    </div>
    @unless($isParentWp)
        <div class="actions" style="margin-top:6px;gap:4px">
            @foreach([0, 25, 50, 75, 100] as $pct)
                <button type="button" class="btn light" style="padding:4px 10px;font-size:12px" onclick="document.getElementById('progress-input').value={{ $pct }}">{{ $pct }}%</button>
            @endforeach
        </div>
    @endunless
</div>
<div class="field"><label>開始日</label><input name="start_date" type="date" value="{{ old('start_date', $package?->start_date?->format('Y-m-d')) }}"></div>
<div class="field"><label>終了日</label><input name="due_date" type="date" value="{{ old('due_date', $package?->due_date?->format('Y-m-d')) }}"></div>
<div class="field"><label>成果物</label><input name="deliverable" value="{{ old('deliverable', $package?->deliverable) }}"></div>
<div class="field" style="grid-column:1/-1"><label>説明</label><textarea name="description">{{ old('description', $package?->description) }}</textarea></div>
