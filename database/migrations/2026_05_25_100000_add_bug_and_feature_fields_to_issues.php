<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            // バグ固有フィールド
            $table->string('severity')->nullable()->after('priority');   // critical / high / medium / low
            $table->text('steps_to_reproduce')->nullable()->after('description');
            $table->text('expected_behavior')->nullable()->after('steps_to_reproduce');
            $table->text('actual_behavior')->nullable()->after('expected_behavior');
            $table->text('environment_info')->nullable()->after('actual_behavior');
            // 機能要望固有フィールド
            $table->text('user_story')->nullable()->after('environment_info');
            $table->unsignedInteger('vote_count')->default(0)->after('star_count');
        });

        // 機能要望への投票テーブル（1人1票保証）
        Schema::create('issue_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('issue_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'issue_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_votes');

        Schema::table('issues', function (Blueprint $table) {
            $table->dropColumn([
                'severity',
                'steps_to_reproduce',
                'expected_behavior',
                'actual_behavior',
                'environment_info',
                'user_story',
                'vote_count',
            ]);
        });
    }
};
