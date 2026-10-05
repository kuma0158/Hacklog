<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('milestones', function (Blueprint $table) {
            $table->foreignId('issue_id')->nullable()->after('project_id')->constrained('issues')->nullOnDelete();
        });

        $projectIds = DB::table('projects')->pluck('id');
        foreach ($projectIds as $projectId) {
            $issueId = DB::table('issues')->where('project_id', $projectId)->orderBy('id')->value('id');

            if (! $issueId) {
                continue;
            }

            DB::table('milestones')->where('project_id', $projectId)->update(['issue_id' => $issueId]);
            DB::table('notification_items')
                ->where('project_id', $projectId)
                ->whereNull('issue_id')
                ->update(['issue_id' => $issueId]);
        }
    }

    public function down(): void
    {
        Schema::table('milestones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('issue_id');
        });
    }
};
