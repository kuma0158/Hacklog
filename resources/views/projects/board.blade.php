<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h1 class="page-title">ボード</h1>
            <a href="{{ route('projects.create') }}" class="btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                リード追加
            </a>
        </div>
    </x-slot>

    <div class="px-4 sm:px-6 lg:px-8 py-6">
        @if (session('status'))
            <div class="alert-success mb-5">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
            @php
                $dot = [
                    '未対応' => 'bg-slate-400', '検討中' => 'bg-indigo-500', '提案済' => 'bg-amber-500',
                    '成約' => 'bg-emerald-500', '見送り' => 'bg-rose-500',
                ];
            @endphp
            @foreach ($statuses as $s)
                <div class="flex flex-col rounded-xl bg-app-2/50 border border-line">
                    <div class="flex items-center justify-between px-3 py-2.5 border-b border-line">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full {{ $dot[$s] ?? 'bg-slate-400' }}"></span>
                            <h3 class="text-sm font-semibold text-ink">{{ $s }}</h3>
                        </div>
                        <span class="text-xs font-medium text-muted bg-card border border-line rounded-full px-2 py-0.5">{{ count($grouped[$s]) }}</span>
                    </div>
                    <div class="p-2.5 space-y-2.5 min-h-[60px]">
                        @foreach ($grouped[$s] as $p)
                            <div class="card p-3 hover:shadow-pop hover:-translate-y-px transition-all">
                                <a class="text-sm font-medium text-ink hover:text-accent transition-colors block mb-1.5" href="{{ route('projects.show', $p) }}">{{ $p->case_name }}</a>
                                @if ($p->location)<div class="text-xs text-muted">{{ $p->location }}</div>@endif
                                @if ($p->unit_price)<div class="text-xs text-ink-soft font-medium tabular-nums">{{ $p->unit_price }}</div>@endif
                                <form method="POST" action="{{ route('projects.status', $p) }}" class="mt-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" class="field text-xs py-1">
                                        @foreach ($statuses as $opt)
                                            <option value="{{ $opt }}" @selected($p->status === $opt)>{{ $opt }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </div>
                        @endforeach
                        @if (count($grouped[$s]) === 0)
                            <div class="text-xs text-muted-2 text-center py-6">なし</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
