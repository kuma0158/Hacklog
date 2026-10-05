<?php

namespace App\Http\Controllers;

use App\Models\Issue;
use App\Models\NotificationItem;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('dashboard', [
            'issues' => Issue::active()
                ->with('assignee')
                ->withCount(['workPackages', 'wikiPages', 'files', 'repositories', 'comments'])
                ->withAvg(['workPackages' => fn ($q) => $q->whereNull('parent_id')], 'progress')
                ->latest()
                ->get(),
            'notifications' => NotificationItem::with('issue')->latest()->limit(8)->get(),
        ]);
    }
}
