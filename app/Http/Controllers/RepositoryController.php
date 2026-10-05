<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\Repository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RepositoryController extends Controller
{
    public function index(Issue $issue): View
    {
        return view('repositories.index', [
            'issue' => $issue,
            'repositories' => $issue->repositories()->latest()->get(),
        ]);
    }

    public function store(Request $request, Issue $issue): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'max:120'],
            'type' => ['required', 'in:git,svn'],
            'url' => ['nullable', 'max:255'],
            'description' => ['nullable'],
        ]);
        $data['issue_id'] = $issue->id;
        Repository::create($data);

        return back()->with('status', 'リポジトリを登録しました。');
    }
}
