<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h1 class="page-title truncate">{{ $project->case_name }}</h1>
            <div class="flex gap-2 shrink-0">
                <a href="{{ route('projects.edit', $project) }}" class="btn-primary btn-sm">編集</a>
                <a href="{{ route('projects.index') }}" class="btn-secondary btn-sm">一覧へ</a>
            </div>
        </div>
    </x-slot>

    <div class="px-4 sm:px-6 lg:px-8 py-6">
        <div class="max-w-5xl mx-auto space-y-5">
            @if (session('status'))
                <div class="alert-success">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                    {{ session('status') }}
                </div>
            @endif

            <div class="card p-5 flex flex-wrap items-center gap-x-6 gap-y-3">
                <form method="POST" action="{{ route('projects.status', $project) }}" class="flex items-center gap-2">
                    @csrf
                    @method('PATCH')
                    <span class="field-label !mb-0">ステータス</span>
                    <select name="status" onchange="this.form.submit()" class="field text-sm w-auto py-1.5">
                        @foreach ($statuses as $s)
                            <option value="{{ $s }}" @selected($project->status === $s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </form>
                <div class="flex flex-wrap items-center gap-x-5 gap-y-1 text-sm text-muted">
                    <span>担当 <span class="text-ink-soft font-medium">{{ $project->assignee ?: '—' }}</span></span>
                    <span>取引先 <span class="text-ink-soft font-medium">{{ optional($project->client)->name ?: '—' }}</span></span>
                    <span>登録者 <span class="text-ink-soft font-medium">{{ $project->user->name }}</span></span>
                    <span>登録日 <span class="text-ink-soft font-medium font-mono text-xs">{{ $project->created_at->format('Y/m/d H:i') }}</span></span>
                </div>
            </div>

            <div class="card p-6 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5 text-sm">
                @php $rows = [
                    '作業内容' => $project->work_content,
                    '就業場所' => $project->location,
                    '就業期間' => $project->period,
                    '単価' => $project->unit_price,
                    '精算幅' => $project->settlement,
                    '面談回数' => $project->interview_count,
                    '商流制限' => $project->flow_limit,
                    '契約形態' => $project->contract_type,
                    '年齢制限' => $project->age_limit,
                    '外国籍可否' => $project->foreigner_ok,
                    '個人事業主可否' => $project->freelance_ok,
                ]; @endphp
                @foreach ($rows as $label => $value)
                    <div>
                        <div class="field-label">{{ $label }}</div>
                        <div class="text-ink-soft whitespace-pre-wrap">{{ $value !== null && $value !== '' ? $value : '—' }}</div>
                    </div>
                @endforeach
                <div class="md:col-span-2">
                    <div class="field-label">必須スキル</div>
                    <div class="flex flex-wrap">
                        @forelse ($project->requiredSkills as $s)
                            <span class="chip">{{ $s->name }}</span>
                        @empty <span class="text-muted-2">—</span>
                        @endforelse
                    </div>
                </div>
                <div class="md:col-span-2">
                    <div class="field-label">尚可スキル</div>
                    <div class="flex flex-wrap">
                        @forelse ($project->preferredSkills as $s)
                            <span class="chip-soft">{{ $s->name }}</span>
                        @empty <span class="text-muted-2">—</span>
                        @endforelse
                    </div>
                </div>
                <div class="md:col-span-2">
                    <div class="field-label">特記事項</div>
                    <div class="text-ink-soft whitespace-pre-wrap">{{ $project->memo ?: '—' }}</div>
                </div>
            </div>

            @if ($project->raw_text)
                <details class="card p-5 group">
                    <summary class="flex items-center gap-2 cursor-pointer text-sm font-medium text-muted hover:text-ink transition-colors list-none">
                        <svg class="w-4 h-4 transition group-open:rotate-90" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m9 5 7 7-7 7"/></svg>
                        元のリード本文を表示
                    </summary>
                    <pre class="mt-3 text-xs text-ink-soft whitespace-pre-wrap font-mono leading-relaxed bg-app-2/60 rounded-lg p-4 border border-line">{{ $project->raw_text }}</pre>
                </details>
            @endif
        </div>
    </div>
</x-app-layout>
