@extends('layouts.app')
@section('content')
<div class="top">
    <div>
        <div class="h1">@if($page->number){{ $page->number }} @endif{{ $page->title }}</div>
        <div class="sub">{{ $issue->issue_key }} ／ {{ $page->author?->name ?? '-' }} ／ {{ $page->updated_at->format('Y-m-d H:i') }}</div>
    </div>
    <div class="actions">
        <a class="btn light" href="{{ route('wiki.index', $issue) }}">一覧へ戻る</a>
        <a class="btn light" href="{{ route('wiki.export', [$issue, $page]) }}">.mdエクスポート</a>
        @can('update', $issue)
            <a class="btn" href="{{ route('wiki.edit', [$issue, $page]) }}">編集</a>
        @endcan
    </div>
</div>

@cannot('update', $issue)
    <div class="alert" role="status">この案件のWikiは閲覧のみです。エクスポートは引き続き利用できます。</div>
@endcannot

<section class="card">
    <div class="wiki-body">{!! $renderedBody !!}</div>
</section>

@push('scripts')
<script type="module">
    const codeBlocks = document.querySelectorAll('.wiki-body pre > code.language-mermaid');
    if (codeBlocks.length > 0) {
        const { default: mermaid } = await import('https://cdn.jsdelivr.net/npm/mermaid@11/dist/mermaid.esm.min.mjs');
        mermaid.initialize({ startOnLoad: false, securityLevel: 'strict', theme: 'neutral' });
        codeBlocks.forEach((code) => {
            const container = document.createElement('div');
            container.className = 'mermaid-diagram';
            container.textContent = code.textContent;
            code.parentElement.replaceWith(container);
        });
        await mermaid.run({ querySelector: '.wiki-body .mermaid-diagram' });
    }
</script>
<script>
    document.addEventListener('click', (event) => {
        const image = event.target.closest('.wiki-body img');
        if (!image || image.closest('a')) return;

        const overlay = document.createElement('div');
        overlay.className = 'img-lightbox';

        const zoomed = document.createElement('img');
        zoomed.src = image.src;
        zoomed.alt = image.alt;
        overlay.appendChild(zoomed);

        const close = () => {
            overlay.remove();
            document.removeEventListener('keydown', onKeydown);
        };
        const onKeydown = (e) => {
            if (e.key === 'Escape') close();
        };

        overlay.addEventListener('click', close);
        document.addEventListener('keydown', onKeydown);
        document.body.appendChild(overlay);
    });
</script>
@endpush
@endsection
