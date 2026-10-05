<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h1 class="page-title">案件一覧</h1>
            <a href="{{ route('projects.create') }}" class="btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                リード追加
            </a>
        </div>
    </x-slot>

    <div class="px-4 sm:px-6 lg:px-8 py-6 space-y-5">
        @if (session('status'))
            <div class="alert-success">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                {{ session('status') }}
            </div>
        @endif

        <form method="GET" action="{{ route('projects.index') }}"
              class="card p-4 grid grid-cols-1 md:grid-cols-12 gap-3">
            <div class="md:col-span-6">
                <label class="field-label">キーワード（案件名・作業内容・場所・スキル・担当）</label>
                <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="検索…" class="field" />
            </div>
            <div class="md:col-span-3">
                <label class="field-label">ステータス</label>
                <select name="status" class="field">
                    <option value="">全て</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s }}" @selected($filters['status'] === $s)>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="field-label">ソート</label>
                <select name="sort" class="field">
                    <option value="created_at" @selected($filters['sort'] === 'created_at')>登録日</option>
                    <option value="unit_price" @selected($filters['sort'] === 'unit_price')>単価</option>
                    <option value="case_name" @selected($filters['sort'] === 'case_name')>案件名</option>
                </select>
            </div>
            <div class="md:col-span-1 flex items-end">
                <button class="btn-primary w-full">検索</button>
            </div>
        </form>

        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-line">
                    <thead>
                        <tr class="table-head">
                            <th class="px-5 py-3 font-semibold">ステータス</th>
                            <th class="px-5 py-3 font-semibold">案件名</th>
                            <th class="px-5 py-3 font-semibold">場所</th>
                            <th class="px-5 py-3 font-semibold">単価</th>
                            <th class="px-5 py-3 font-semibold">必須スキル</th>
                            <th class="px-5 py-3 font-semibold">担当</th>
                            <th class="px-5 py-3 font-semibold">取引先</th>
                            <th class="px-5 py-3 font-semibold">登録日</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-sm">
                        @forelse ($projects as $p)
                            <tr class="hover:bg-app-2/50 transition-colors">
                                <td class="px-5 py-3">
                                    <span @class([
                                        'badge',
                                        'bg-slate-100 text-slate-600' => $p->status === '未対応',
                                        'bg-indigo-50 text-indigo-700' => $p->status === '検討中',
                                        'bg-amber-50 text-amber-700' => $p->status === '提案済',
                                        'bg-emerald-50 text-emerald-700' => $p->status === '成約',
                                        'bg-rose-50 text-rose-700' => $p->status === '見送り',
                                    ])>{{ $p->status }}</span>
                                </td>
                                <td class="px-5 py-3">
                                    <a class="font-medium text-ink hover:text-accent transition-colors" href="{{ route('projects.show', $p) }}">{{ $p->case_name }}</a>
                                </td>
                                <td class="px-5 py-3 text-muted">{{ $p->location }}</td>
                                <td class="px-5 py-3 text-ink-soft font-medium tabular-nums">{{ $p->unit_price }}</td>
                                <td class="px-5 py-3">
                                    @foreach ($p->requiredSkills as $s)
                                        <span class="chip">{{ $s->name }}</span>
                                    @endforeach
                                </td>
                                <td class="px-5 py-3 text-ink-soft">{{ $p->assignee ?: '—' }}</td>
                                <td class="px-5 py-3 text-muted">{{ optional($p->client)->name }}</td>
                                <td class="px-5 py-3 text-muted-2 whitespace-nowrap font-mono text-xs">{{ $p->created_at->format('Y/m/d') }}</td>
                                <td class="px-5 py-3 text-right">
                                    <a class="btn-secondary btn-sm" href="{{ route('projects.edit', $p) }}">編集</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-14 text-center">
                                    <div class="text-muted">案件はまだ登録されていません。</div>
                                    <a href="{{ route('projects.create') }}" class="btn-primary btn-sm mt-3">＋ リードを追加</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>{{ $projects->links() }}</div>
    </div>
</x-app-layout>
