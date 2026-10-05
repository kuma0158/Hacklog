<?php

use App\Http\Controllers\BugController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeatureRequestController;
use App\Http\Controllers\IssueController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectFileController;
use App\Http\Controllers\RepositoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WikiPageController;
use App\Http\Controllers\WorkPackageController;
use App\Models\Issue;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'auth.session'])->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    // ── プロフィール（Breeze） ──
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ── ユーザー管理（管理者のみ） ──
    Route::middleware('can:manage-users')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });

    // ── 案件 ──
    Route::get('/issues', [IssueController::class, 'index'])->name('issues.index');
    Route::get('/issues/create', [IssueController::class, 'create'])->middleware('can:create,'.Issue::class)->name('issues.create');
    Route::post('/issues', [IssueController::class, 'store'])->middleware('can:create,'.Issue::class)->name('issues.store');
    Route::get('/issues/{issue}', [IssueController::class, 'show'])->name('issues.show');
    Route::put('/issues/{issue}', [IssueController::class, 'update'])->middleware('can:update,issue')->name('issues.update');
    Route::patch('/issues/{issue}/archive', [IssueController::class, 'archive'])->middleware('can:update,issue')->name('issues.archive');
    Route::patch('/issues/{issue}/unarchive', [IssueController::class, 'unarchive'])->middleware('can:update,issue')->name('issues.unarchive');
    Route::post('/issues/{issue}/comments', [IssueController::class, 'comment'])->middleware('can:update,issue')->name('issues.comments.store');
    Route::post('/issues/{issue}/react/{type}', [IssueController::class, 'react'])->middleware('can:update,issue')->whereIn('type', ['watch', 'star'])->name('issues.react');

    // ── バグ管理（案件に紐づく） ──
    Route::get('/issues/{issue}/bugs', [BugController::class, 'index'])->name('bugs.index');
    Route::get('/issues/{issue}/bugs/create', [BugController::class, 'create'])->middleware('can:update,issue')->name('bugs.create');
    Route::post('/issues/{issue}/bugs', [BugController::class, 'store'])->middleware('can:update,issue')->name('bugs.store');
    Route::get('/issues/{issue}/bugs/{bug}', [BugController::class, 'show'])->name('bugs.show');
    Route::get('/issues/{issue}/bugs/{bug}/edit', [BugController::class, 'edit'])->middleware('can:update,issue')->name('bugs.edit');
    Route::put('/issues/{issue}/bugs/{bug}', [BugController::class, 'update'])->middleware('can:update,issue')->name('bugs.update');
    Route::delete('/issues/{issue}/bugs/{bug}', [BugController::class, 'destroy'])->middleware('can:update,issue')->name('bugs.destroy');

    // ── 機能要望（案件に紐づく） ──
    Route::get('/issues/{issue}/features', [FeatureRequestController::class, 'index'])->name('features.index');
    Route::get('/issues/{issue}/features/create', [FeatureRequestController::class, 'create'])->middleware('can:update,issue')->name('features.create');
    Route::post('/issues/{issue}/features', [FeatureRequestController::class, 'store'])->middleware('can:update,issue')->name('features.store');
    Route::get('/issues/{issue}/features/{feature}', [FeatureRequestController::class, 'show'])->name('features.show');
    Route::get('/issues/{issue}/features/{feature}/edit', [FeatureRequestController::class, 'edit'])->middleware('can:update,issue')->name('features.edit');
    Route::put('/issues/{issue}/features/{feature}', [FeatureRequestController::class, 'update'])->middleware('can:update,issue')->name('features.update');
    Route::delete('/issues/{issue}/features/{feature}', [FeatureRequestController::class, 'destroy'])->middleware('can:update,issue')->name('features.destroy');
    Route::post('/issues/{issue}/features/{feature}/vote', [FeatureRequestController::class, 'vote'])->middleware('can:update,issue')->name('features.vote');

    // ── Wiki ──
    Route::get('/issues/{issue}/wiki', [WikiPageController::class, 'index'])->name('wiki.index');
    Route::get('/issues/{issue}/wiki/create', [WikiPageController::class, 'create'])->middleware('can:update,issue')->name('wiki.create');
    Route::post('/issues/{issue}/wiki', [WikiPageController::class, 'store'])->middleware('can:update,issue')->name('wiki.store');
    Route::get('/issues/{issue}/wiki/{wikiPage}', [WikiPageController::class, 'show'])->name('wiki.show');
    Route::get('/issues/{issue}/wiki/{wikiPage}/edit', [WikiPageController::class, 'edit'])->middleware('can:update,issue')->name('wiki.edit');
    Route::get('/issues/{issue}/wiki/{wikiPage}/export', [WikiPageController::class, 'export'])->name('wiki.export');
    Route::put('/issues/{issue}/wiki/{wikiPage}', [WikiPageController::class, 'update'])->middleware('can:update,issue')->name('wiki.update');
    Route::delete('/issues/{issue}/wiki/{wikiPage}', [WikiPageController::class, 'destroy'])->middleware('can:update,issue')->name('wiki.destroy');

    // ── ファイル ──
    Route::get('/issues/{issue}/files', [ProjectFileController::class, 'index'])->name('files.index');
    Route::post('/issues/{issue}/files', [ProjectFileController::class, 'store'])->middleware('can:update,issue')->name('files.store');

    // ── リポジトリ ──
    Route::get('/issues/{issue}/repositories', [RepositoryController::class, 'index'])->name('repositories.index');
    Route::post('/issues/{issue}/repositories', [RepositoryController::class, 'store'])->middleware('can:update,issue')->name('repositories.store');

    // ── WBS ──
    Route::get('/issues/{issue}/wbs', [WorkPackageController::class, 'index'])->name('work-packages.index');
    Route::get('/issues/{issue}/wbs/create', [WorkPackageController::class, 'create'])->middleware('can:update,issue')->name('work-packages.create');
    Route::post('/issues/{issue}/wbs', [WorkPackageController::class, 'store'])->middleware('can:update,issue')->name('work-packages.store');
    Route::get('/issues/{issue}/wbs/gantt', [WorkPackageController::class, 'gantt'])->name('work-packages.gantt');
    Route::get('/issues/{issue}/wbs/kanban', [WorkPackageController::class, 'kanban'])->name('work-packages.kanban');
    Route::get('/issues/{issue}/wbs/{workPackage}/edit', [WorkPackageController::class, 'edit'])->middleware('can:update,issue')->name('work-packages.edit');
    Route::put('/issues/{issue}/wbs/{workPackage}', [WorkPackageController::class, 'update'])->middleware('can:update,issue')->name('work-packages.update');
    Route::patch('/issues/{issue}/wbs/{workPackage}/move', [WorkPackageController::class, 'move'])->middleware('can:update,issue')->name('work-packages.move');
    Route::patch('/issues/{issue}/wbs/{workPackage}/reschedule', [WorkPackageController::class, 'reschedule'])->middleware('can:update,issue')->name('work-packages.reschedule');
    Route::delete('/issues/{issue}/wbs/{workPackage}', [WorkPackageController::class, 'destroy'])->middleware('can:update,issue')->name('work-packages.destroy');
});

require __DIR__.'/auth.php';
