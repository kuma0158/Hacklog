@php
    $nav = [
        ['route' => 'projects.index',  'pattern' => 'projects.index',  'label' => '案件一覧',  'icon' => 'list'],
        ['route' => 'projects.board',  'pattern' => 'projects.board',  'label' => 'ボード',    'icon' => 'board'],
        ['route' => 'projects.create', 'pattern' => 'projects.create', 'label' => 'リード追加', 'icon' => 'plus'],
    ];
@endphp

<aside
    class="fixed inset-y-0 left-0 z-40 w-64 flex flex-col bg-card border-r border-line
           transition-transform duration-200 lg:translate-x-0"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

    <!-- Brand -->
    <div class="flex items-center justify-between h-16 px-5 border-b border-line">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5">
            <span class="grid place-items-center w-8 h-8 rounded-lg bg-accent text-white font-bold text-sm shadow-sm">S</span>
            <span class="font-semibold tracking-tight text-ink">{{ config('app.name', 'SESmatter') }}</span>
        </a>
        <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-md text-muted hover:bg-app-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Nav -->
    <nav class="flex-1 overflow-y-auto px-3 py-4">
        <p class="px-3 mb-2 eyebrow">Menu</p>
        <div class="space-y-1">
            @foreach ($nav as $item)
                <a href="{{ route($item['route']) }}"
                   @class(['nav-item', 'nav-item-active' => request()->routeIs($item['pattern'])])>
                    @switch($item['icon'])
                        @case('list')
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/></svg>
                            @break
                        @case('board')
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5h4v14H4zM10 5h4v9h-4zM16 5h4v6h-4z"/></svg>
                            @break
                        @case('plus')
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/></svg>
                            @break
                    @endswitch
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>
    </nav>

    <!-- Account -->
    <div class="border-t border-line p-3"
         x-data="{ open: false }" @click.outside="open = false">
        <button @click="open = !open"
                class="w-full flex items-center gap-3 rounded-lg px-2.5 py-2 hover:bg-app-2 transition">
            <span class="grid place-items-center w-8 h-8 rounded-full bg-app-2 text-muted text-sm font-semibold uppercase">
                {{ mb_substr(Auth::user()->name, 0, 1) }}
            </span>
            <span class="flex-1 min-w-0 text-left">
                <span class="block text-sm font-medium text-ink truncate">{{ Auth::user()->name }}</span>
                <span class="block text-xs text-muted truncate">{{ Auth::user()->email }}</span>
            </span>
            <svg class="w-4 h-4 text-muted-2 transition" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/></svg>
        </button>

        <div x-show="open" x-cloak x-transition
             class="mt-2 rounded-lg border border-line bg-card shadow-pop p-1">
            <a href="{{ route('profile.edit') }}" class="block rounded-md px-3 py-2 text-sm text-ink-soft hover:bg-app-2 transition">{{ __('Profile') }}</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-left rounded-md px-3 py-2 text-sm text-rose-600 hover:bg-rose-50 transition">{{ __('Log Out') }}</button>
            </form>
        </div>
    </div>
</aside>
