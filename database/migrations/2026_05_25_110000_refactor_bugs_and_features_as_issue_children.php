<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 前回追加した issues のバグ/機能要望カラムを削除 ──
        Schema::dropIfExists('issue_votes');

        Schema::table('issues', function (Blueprint $table) {
            $table->dropColumn([
                'severity', 'steps_to_reproduce', 'expected_behavior',
                'actual_behavior', 'environment_info', 'user_story', 'vote_count',
            ]);
        });

        // ── バグ管理テーブル ──
        Schema::create('bugs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reporter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('severity')->default('medium');   // critical / high / medium / low
            $table->string('priority')->default('normal');   // critical / high / normal / low
            $table->string('status')->default('open');       // open / investigating / fixed / verified / closed
            $table->text('steps_to_reproduce')->nullable();
            $table->text('expected_behavior')->nullable();
            $table->text('actual_behavior')->nullable();
            $table->string('environment_info')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamps();
        });

        // ── 機能要望テーブル ──
        Schema::create('feature_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requester_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('user_story')->nullable();
            $table->string('priority')->default('normal');   // critical / high / normal / low
            $table->string('status')->default('proposed');   // proposed / reviewing / accepted / rejected / in_progress / done
            $table->unsignedInteger('vote_count')->default(0);
            $table->date('due_date')->nullable();
            $table->timestamps();
        });

        // ── 機能要望への投票（1人1票） ──
        Schema::create('feature_request_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('feature_request_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'feature_request_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_request_votes');
        Schema::dropIfExists('feature_requests');
        Schema::dropIfExists('bugs');

        Schema::table('issues', function (Blueprint $table) {
            $table->string('severity')->nullable()->after('priority');
            $table->text('steps_to_reproduce')->nullable();
            $table->text('expected_behavior')->nullable();
            $table->text('actual_behavior')->nullable();
            $table->text('environment_info')->nullable();
            $table->text('user_story')->nullable();
            $table->unsignedInteger('vote_count')->default(0)->after('star_count');
        });

        Schema::create('issue_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('issue_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'issue_id']);
        });
    }
};
