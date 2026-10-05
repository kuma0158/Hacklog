<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">案件を編集</h1>
    </x-slot>

    <div class="px-4 sm:px-6 lg:px-8 py-6">
        <div class="max-w-5xl mx-auto space-y-5">
            <form method="POST" action="{{ route('projects.update', $project) }}" class="card p-6 space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="field-label">案件名 <span class="text-accent">*</span></label>
                        <input type="text" name="case_name" required value="{{ old('case_name', $project->case_name) }}" class="field" />
                        @error('case_name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="field-label">取引先</label>
                        <input type="text" name="client_name" value="{{ old('client_name', optional($project->client)->name) }}" class="field" />
                    </div>
                    <div>
                        <label class="field-label">ステータス</label>
                        <select name="status" class="field">
                            @foreach ($statuses as $s)
                                <option value="{{ $s }}" @selected(old('status', $project->status) === $s)>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">担当メンバー</label>
                        <input type="text" name="assignee" value="{{ old('assignee', $project->assignee) }}" placeholder="アサインされたメンバー名" class="field" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="field-label">作業内容</label>
                        <textarea name="work_content" rows="4" class="field">{{ old('work_content', $project->work_content) }}</textarea>
                    </div>

                    @php
                        $req = old('required_skills', $project->requiredSkills->pluck('name')->all());
                        $pref = old('preferred_skills', $project->preferredSkills->pluck('name')->all());
                    @endphp

                    <div>
                        <label class="field-label">必須スキル（カンマ区切り）</label>
                        <input type="text" id="required_csv" value="{{ implode(', ', $req) }}"
                               oninput="this.nextElementSibling.querySelectorAll('input').forEach(e => e.remove()); this.value.split(',').map(s=>s.trim()).filter(Boolean).forEach(v=>{ const i=document.createElement('input'); i.type='hidden'; i.name='required_skills[]'; i.value=v; this.nextElementSibling.appendChild(i); })"
                               class="field" />
                        <div>
                            @foreach ($req as $r)<input type="hidden" name="required_skills[]" value="{{ $r }}" />@endforeach
                        </div>
                    </div>
                    <div>
                        <label class="field-label">尚可スキル（カンマ区切り）</label>
                        <input type="text" id="preferred_csv" value="{{ implode(', ', $pref) }}"
                               oninput="this.nextElementSibling.querySelectorAll('input').forEach(e => e.remove()); this.value.split(',').map(s=>s.trim()).filter(Boolean).forEach(v=>{ const i=document.createElement('input'); i.type='hidden'; i.name='preferred_skills[]'; i.value=v; this.nextElementSibling.appendChild(i); })"
                               class="field" />
                        <div>
                            @foreach ($pref as $r)<input type="hidden" name="preferred_skills[]" value="{{ $r }}" />@endforeach
                        </div>
                    </div>

                    <div><label class="field-label">就業場所</label><input type="text" name="location" value="{{ old('location', $project->location) }}" class="field" /></div>
                    <div><label class="field-label">就業期間</label><input type="text" name="period" value="{{ old('period', $project->period) }}" class="field" /></div>
                    <div><label class="field-label">単価</label><input type="text" name="unit_price" value="{{ old('unit_price', $project->unit_price) }}" class="field" /></div>
                    <div><label class="field-label">精算幅</label><input type="text" name="settlement" value="{{ old('settlement', $project->settlement) }}" class="field" /></div>
                    <div><label class="field-label">面談回数</label><input type="number" name="interview_count" min="0" max="9" value="{{ old('interview_count', $project->interview_count) }}" class="field" /></div>
                    <div><label class="field-label">商流制限</label><input type="text" name="flow_limit" value="{{ old('flow_limit', $project->flow_limit) }}" class="field" /></div>
                    <div><label class="field-label">契約形態</label><input type="text" name="contract_type" value="{{ old('contract_type', $project->contract_type) }}" class="field" /></div>
                    <div><label class="field-label">年齢制限</label><input type="text" name="age_limit" value="{{ old('age_limit', $project->age_limit) }}" class="field" /></div>
                    <div><label class="field-label">外国籍可否</label><input type="text" name="foreigner_ok" value="{{ old('foreigner_ok', $project->foreigner_ok) }}" class="field" /></div>
                    <div><label class="field-label">個人事業主可否</label><input type="text" name="freelance_ok" value="{{ old('freelance_ok', $project->freelance_ok) }}" class="field" /></div>

                    <div class="md:col-span-2">
                        <label class="field-label">特記事項</label>
                        <textarea name="memo" rows="3" class="field">{{ old('memo', $project->memo) }}</textarea>
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-4 border-t border-line">
                    <button type="submit" class="btn-primary">更新する</button>
                    <a href="{{ route('projects.show', $project) }}" class="btn-ghost">キャンセル</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
