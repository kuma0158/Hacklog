<?php

namespace Database\Seeders;

use App\Models\Issue;
use App\Models\IssueComment;
use App\Models\Milestone;
use App\Models\NotificationItem;
use App\Models\ProjectFile;
use App\Models\Repository;
use App\Models\User;
use App\Models\WorkPackage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => '管理者',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
            ]
        );

        if (! $admin->isAdmin()) {
            $admin->forceFill(['role' => User::ROLE_ADMIN])->save();
        }

        $users = collect([
            ['name' => '佐藤 開発', 'email' => 'sato@example.com'],
            ['name' => '田中 PM', 'email' => 'tanaka@example.com'],
            ['name' => '鈴木 QA', 'email' => 'suzuki@example.com'],
        ])->map(fn ($user) => User::firstOrCreate(
            ['email' => $user['email']],
            [
                'name' => $user['name'],
                'password' => Hash::make('password'),
                'role' => User::ROLE_GENERAL,
            ]
        ));

        $issue = Issue::firstOrCreate(
            ['issue_key' => 'ISSUE-1'],
            [
                'assignee_id' => $users[0]->id,
                'type' => 'task',
                'summary' => 'Backlog Clone デモ案件',
                'description' => '課題、Wiki、ガント、ファイル、リポジトリをまとめて管理するデモ案件です。',
                'status' => 'in_progress',
                'priority' => 'high',
                'start_date' => now()->startOfMonth(),
                'due_date' => now()->addMonths(2),
                'progress' => 45,
                'watch_count' => 2,
                'star_count' => 3,
            ]
        );

        $milestone = Milestone::firstOrCreate(
            ['issue_id' => $issue->id, 'name' => 'v1.0 MVP'],
            ['kind' => 'version', 'start_date' => now(), 'release_date' => now()->addMonth()]
        );

        if ($issue->milestone_id === null) {
            $issue->forceFill(['milestone_id' => $milestone->id])->save();
        }

        IssueComment::firstOrCreate(
            ['issue_id' => $issue->id, 'body' => '詳細画面では履歴とコメントを同じ流れで確認できるようにします。'],
            ['user_id' => $users[1]->id]
        );

        $issue->wikiPages()->firstOrCreate(
            ['title' => '要件定義メモ'],
            ['author_id' => $users[1]->id, 'body' => "## スコープ\n課題管理、Wiki、ガントチャート、ファイル、リポジトリをMVPに含めます。", 'star_count' => 1]
        );

        ProjectFile::firstOrCreate(
            ['issue_id' => $issue->id, 'name' => '画面設計.pdf'],
            ['category' => '設計', 'size' => 2480000, 'uploaded_by' => $users[1]->id, 'description' => '主要画面のワイヤーフレーム']
        );

        Repository::firstOrCreate(
            ['issue_id' => $issue->id, 'name' => 'backlog-clone'],
            ['type' => 'git', 'url' => 'git@example.com:dev/backlog-clone.git', 'description' => 'Laravelアプリケーション本体']
        );

        NotificationItem::firstOrCreate(
            ['issue_id' => $issue->id, 'title' => $issue->issue_key . ' が更新されました'],
            ['kind' => 'issue', 'body' => '進捗が45%になりました。']
        );

        $requirements = WorkPackage::firstOrCreate(
            ['issue_id' => $issue->id, 'wbs_code' => '1'],
            [
                'assignee_id' => $users[1]->id,
                'name' => '要件定義',
                'description' => 'プロジェクト全体のスコープと受入条件を確定します。',
                'deliverable' => '要件定義書',
                'status' => 'done',
                'progress' => 100,
                'start_date' => now()->subDays(10),
                'due_date' => now()->subDays(2),
                'sort_order' => 10,
            ]
        );

        WorkPackage::firstOrCreate(
            ['issue_id' => $issue->id, 'wbs_code' => '1.1'],
            [
                'parent_id' => $requirements->id,
                'assignee_id' => $users[1]->id,
                'name' => '課題管理要件',
                'description' => '状態、優先度、担当者、コメントの管理要件を整理します。',
                'deliverable' => '課題管理仕様',
                'status' => 'done',
                'progress' => 100,
                'start_date' => now()->subDays(10),
                'due_date' => now()->subDays(6),
                'sort_order' => 11,
            ]
        );

        WorkPackage::firstOrCreate(
            ['issue_id' => $issue->id, 'wbs_code' => '2'],
            [
                'assignee_id' => $users[0]->id,
                'name' => '実装',
                'description' => 'Laravelで主要機能を実装します。',
                'deliverable' => 'アプリケーションコード',
                'status' => 'in_progress',
                'progress' => 55,
                'start_date' => now()->subDays(1),
                'due_date' => now()->addDays(18),
                'sort_order' => 20,
            ]
        );
    }
}
