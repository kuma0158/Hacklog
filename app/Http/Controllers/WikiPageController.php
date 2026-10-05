<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\User;
use App\Models\WikiPage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\HtmlString;
use Illuminate\View\View;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WikiPageController extends Controller
{
    public function index(Issue $issue): View
    {
        return view('wiki.index', [
            'issue' => $issue,
            'pages' => $issue->wikiPages()->with('author')->get()
                ->sortBy(fn (WikiPage $page) => [$page->numberSortKey(), $page->title])
                ->values(),
        ]);
    }

    public function show(Issue $issue, WikiPage $wikiPage): View
    {
        abort_unless($wikiPage->issue_id === $issue->id, 404);

        return view('wiki.show', [
            'issue' => $issue,
            'page' => $wikiPage->load('author'),
            'renderedBody' => $this->renderMarkdown($wikiPage->body),
        ]);
    }

    public function edit(Issue $issue, WikiPage $wikiPage): View
    {
        abort_unless($wikiPage->issue_id === $issue->id, 404);

        return view('wiki.edit', [
            'issue' => $issue,
            'page' => $wikiPage,
        ]);
    }

    public function create(Issue $issue): View
    {
        return view('wiki.create', [
            'issue' => $issue,
        ]);
    }

    public function store(Request $request, Issue $issue): RedirectResponse
    {
        $data = $request->validate([
            'number' => ['nullable', 'string', 'max:50', 'regex:/^\d+(\.\d+)*$/'],
            'title' => ['required', 'max:160'],
            'body' => ['required'],
        ], [
            'number.regex' => '番号は 1、1.1、1.2.1 のような形式で入力してください。',
        ]);

        $data['issue_id'] = $issue->id;
        $data['author_id'] = auth()->id();

        WikiPage::create($data);

        return redirect()->route('wiki.index', $issue)->with('status', 'Wikiページを作成しました。');
    }

    public function update(Request $request, Issue $issue, WikiPage $wikiPage): RedirectResponse
    {
        abort_unless($wikiPage->issue_id === $issue->id, 404);

        $data = $request->validate([
            'number' => ['nullable', 'string', 'max:50', 'regex:/^\d+(\.\d+)*$/'],
            'title' => ['required', 'max:160'],
            'body' => ['required'],
        ], [
            'number.regex' => '番号は 1、1.1、1.2.1 のような形式で入力してください。',
        ]);

        $wikiPage->update($data);

        return redirect()->route('wiki.show', [$issue, $wikiPage])->with('status', 'Wikiページを更新しました。');
    }

    public function export(Issue $issue, WikiPage $wikiPage): StreamedResponse
    {
        abort_unless($wikiPage->issue_id === $issue->id, 404);

        $filename = trim(($wikiPage->number ? $wikiPage->number.'_' : '').$wikiPage->title);
        $filename = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $filename);

        return response()->streamDownload(
            fn () => print($wikiPage->body),
            $filename.'.md',
            ['Content-Type' => 'text/markdown; charset=UTF-8']
        );
    }

    public function destroy(Issue $issue, WikiPage $wikiPage): RedirectResponse
    {
        abort_unless($wikiPage->issue_id === $issue->id, 404);

        $wikiPage->delete();

        return redirect()->route('wiki.index', $issue)->with('status', 'Wikiページを削除しました。');
    }

    private function renderMarkdown(string $body): HtmlString
    {
        $converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return new HtmlString((string) $converter->convert($body));
    }
}
