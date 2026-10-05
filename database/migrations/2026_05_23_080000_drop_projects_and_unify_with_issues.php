<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_packages', function (Blueprint $table) {
            $table->dropForeign(['project_id']);
            $table->dropUnique(['project_id', 'wbs_code']);
            $table->dropColumn('project_id');
            $table->unique(['issue_id', 'wbs_code']);
        });

        foreach (['issues', 'milestones', 'wiki_pages', 'project_files', 'repositories', 'notification_items'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropConstrainedForeignId('project_id');
            });
        }

        Schema::dropIfExists('projects');
    }

    public function down(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color', 20)->default('#1f8f5f');
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        foreach (['issues', 'milestones', 'wiki_pages', 'project_files', 'repositories', 'notification_items'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            });
        }

        Schema::table('work_packages', function (Blueprint $table) {
            $table->dropUnique(['issue_id', 'wbs_code']);
            $table->foreignId('project_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });
    }
};
