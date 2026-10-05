<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_packages', function (Blueprint $table) {
            $table->foreignId('issue_id')->nullable()->after('project_id')->constrained('issues')->nullOnDelete();
        });

        Schema::table('wiki_pages', function (Blueprint $table) {
            $table->foreignId('issue_id')->nullable()->after('project_id')->constrained('issues')->nullOnDelete();
        });

        Schema::table('project_files', function (Blueprint $table) {
            $table->foreignId('issue_id')->nullable()->after('project_id')->constrained('issues')->nullOnDelete();
        });

        Schema::table('repositories', function (Blueprint $table) {
            $table->foreignId('issue_id')->nullable()->after('project_id')->constrained('issues')->nullOnDelete();
        });

        $projectIds = DB::table('projects')->pluck('id');
        foreach ($projectIds as $projectId) {
            $issueId = DB::table('issues')->where('project_id', $projectId)->orderBy('id')->value('id');

            if (! $issueId) {
                continue;
            }

            DB::table('work_packages')->where('project_id', $projectId)->update(['issue_id' => $issueId]);
            DB::table('wiki_pages')->where('project_id', $projectId)->update(['issue_id' => $issueId]);
            DB::table('project_files')->where('project_id', $projectId)->update(['issue_id' => $issueId]);
            DB::table('repositories')->where('project_id', $projectId)->update(['issue_id' => $issueId]);
        }
    }

    public function down(): void
    {
        Schema::table('work_packages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('issue_id');
        });

        Schema::table('wiki_pages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('issue_id');
        });

        Schema::table('project_files', function (Blueprint $table) {
            $table->dropConstrainedForeignId('issue_id');
        });

        Schema::table('repositories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('issue_id');
        });
    }
};
