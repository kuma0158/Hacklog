<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\ProjectFile;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectFileController extends Controller
{
    public function index(Issue $issue): View
    {
        return view('files.index', [
            'issue' => $issue,
            'files' => $issue->files()->latest()->get(),
        ]);
    }

    public function store(Request $request, Issue $issue): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'max:180'],
            'category' => ['nullable', 'max:80'],
            'size' => ['nullable', 'integer', 'min:0'],
            'path' => ['nullable', 'max:255'],
            'description' => ['nullable'],
        ]);
        $data['issue_id'] = $issue->id;
        $data['uploaded_by'] = auth()->id();
        ProjectFile::create($data);

        return back()->with('status', 'ファイル情報を登録しました。');
    }
}
