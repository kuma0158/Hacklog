<x-app-layout>
    <x-slot name="header">
        <h1 class="page-title">リード追加 / 解析</h1>
    </x-slot>

    <div class="px-4 sm:px-6 lg:px-8 py-6">
        <div class="max-w-5xl mx-auto space-y-5"
             x-data="leadCreate(@js($statuses), @js($prefill))">

            <div class="card p-6 space-y-4">
                <div class="flex items-center gap-2">
                    <span class="grid place-items-center w-6 h-6 rounded-full bg-accent text-white text-xs font-semibold">1</span>
                    <h3 class="text-sm font-semibold text-ink">リード本文を貼り付けて解析</h3>
                </div>
                <textarea x-model="rawText" rows="10"
                          class="field font-mono text-sm leading-relaxed"
                          placeholder="【案件名】：...&#10;【必須スキル】：...&#10;..."></textarea>
                <div class="flex flex-wrap items-center gap-3">
                    <button type="button" @click="parse()" :disabled="parsing" class="btn-primary">
                        <svg x-show="!parsing" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3l14 9-14 9V3z"/></svg>
                        <svg x-show="parsing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/></svg>
                        <span x-text="parsing ? '解析中...' : '解析する'"></span>
                    </button>
                    <button type="button" @click="manual()" class="btn-secondary">手動入力で登録</button>
                    <p class="text-sm text-rose-600" x-text="error"></p>
                </div>
            </div>

            <form method="POST" action="{{ route('projects.store') }}"
                  x-show="showForm" x-cloak
                  class="card p-6 space-y-5">
                @csrf

                <div class="flex items-center gap-2">
                    <span class="grid place-items-center w-6 h-6 rounded-full bg-accent text-white text-xs font-semibold">2</span>
                    <h3 class="text-sm font-semibold text-ink">内容を確認・修正して登録</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="field-label">案件名 <span class="text-accent">*</span></label>
                        <input type="text" name="case_name" required x-model="form.case_name" class="field" />
                        @error('case_name') <p class="text-xs text-rose-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="field-label">取引先</label>
                        <input type="text" name="client_name" x-model="form.client_name" class="field" />
                    </div>
                    <div>
                        <label class="field-label">ステータス</label>
                        <select name="status" x-model="form.status" class="field">
                            <template x-for="s in statuses" :key="s">
                                <option :value="s" x-text="s"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">担当メンバー</label>
                        <input type="text" name="assignee" x-model="form.assignee" placeholder="アサインされたメンバー名" class="field" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="field-label">作業内容</label>
                        <textarea name="work_content" rows="4" x-model="form.work_content" class="field"></textarea>
                    </div>
                    <div>
                        <label class="field-label">必須スキル（カンマ区切り）</label>
                        <input type="text" :value="form.required_skills.join(', ')"
                               @input="form.required_skills = splitCsv($event.target.value)" class="field" />
                        <template x-for="s in form.required_skills" :key="s">
                            <input type="hidden" name="required_skills[]" :value="s" />
                        </template>
                    </div>
                    <div>
                        <label class="field-label">尚可スキル（カンマ区切り）</label>
                        <input type="text" :value="form.preferred_skills.join(', ')"
                               @input="form.preferred_skills = splitCsv($event.target.value)" class="field" />
                        <template x-for="s in form.preferred_skills" :key="s">
                            <input type="hidden" name="preferred_skills[]" :value="s" />
                        </template>
                    </div>
                    <div>
                        <label class="field-label">就業場所</label>
                        <input type="text" name="location" x-model="form.location" class="field" />
                    </div>
                    <div>
                        <label class="field-label">就業期間</label>
                        <input type="text" name="period" x-model="form.period" class="field" />
                    </div>
                    <div>
                        <label class="field-label">単価</label>
                        <input type="text" name="unit_price" x-model="form.unit_price" class="field" />
                    </div>
                    <div>
                        <label class="field-label">精算幅</label>
                        <input type="text" name="settlement" x-model="form.settlement" class="field" />
                    </div>
                    <div>
                        <label class="field-label">面談回数</label>
                        <input type="number" name="interview_count" min="0" max="9" x-model.number="form.interview_count" class="field" />
                    </div>
                    <div>
                        <label class="field-label">商流制限</label>
                        <input type="text" name="flow_limit" x-model="form.flow_limit" class="field" />
                    </div>
                    <div>
                        <label class="field-label">契約形態</label>
                        <input type="text" name="contract_type" x-model="form.contract_type" class="field" />
                    </div>
                    <div>
                        <label class="field-label">年齢制限</label>
                        <input type="text" name="age_limit" x-model="form.age_limit" class="field" />
                    </div>
                    <div>
                        <label class="field-label">外国籍可否</label>
                        <input type="text" name="foreigner_ok" x-model="form.foreigner_ok" class="field" />
                    </div>
                    <div>
                        <label class="field-label">個人事業主可否</label>
                        <input type="text" name="freelance_ok" x-model="form.freelance_ok" class="field" />
                    </div>
                    <div class="md:col-span-2">
                        <label class="field-label">特記事項</label>
                        <textarea name="memo" rows="3" x-model="form.memo" class="field"></textarea>
                    </div>
                </div>

                <input type="hidden" name="raw_text" :value="rawText" />

                <div class="flex items-center gap-3 pt-4 border-t border-line">
                    <button type="submit" class="btn-primary">登録する</button>
                    <button type="button" @click="reset()" class="btn-ghost">破棄</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function leadCreate(statuses, prefill) {
        const empty = {
            case_name: '', client_name: '', work_content: '',
            required_skills: [], preferred_skills: [],
            location: '', period: '', unit_price: '', settlement: '',
            interview_count: null, flow_limit: '', contract_type: '',
            age_limit: '', foreigner_ok: '', freelance_ok: '', memo: '',
            assignee: '',
            status: statuses[0] || '未対応',
        };
        return {
            statuses,
            rawText: '',
            parsing: false,
            error: '',
            showForm: Object.keys(prefill || {}).length > 0,
            form: Object.assign({}, empty, prefill || {}),
            async parse() {
                this.error = '';
                if (!this.rawText || this.rawText.length < 10) {
                    this.error = '本文を貼り付けてください';
                    return;
                }
                this.parsing = true;
                try {
                    const res = await fetch('{{ route('leads.parse') }}', {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ raw_text: this.rawText }),
                    });
                    const data = await res.json();
                    if (!res.ok) {
                        this.error = data.message || '解析に失敗しました';
                        if (data.fallback) { this.showForm = true; }
                        return;
                    }
                    this.form = Object.assign({}, empty, data.data || {});
                    this.showForm = true;
                } catch (e) {
                    this.error = '通信エラー: ' + e.message;
                    this.showForm = true;
                } finally {
                    this.parsing = false;
                }
            },
            manual() {
                this.form = Object.assign({}, empty);
                this.showForm = true;
            },
            reset() {
                this.form = Object.assign({}, empty);
                this.showForm = false;
            },
            splitCsv(v) {
                return (v || '').split(',').map(s => s.trim()).filter(Boolean);
            },
        };
    }
    </script>
</x-app-layout>
