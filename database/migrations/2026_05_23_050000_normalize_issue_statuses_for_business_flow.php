<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('issues')->where('status', 'open')->update(['status' => 'not_started']);
        DB::table('issues')->where('status', 'resolved')->update(['status' => 'done']);
        DB::table('issues')->where('status', 'closed')->update(['status' => 'done']);
    }

    public function down(): void
    {
        DB::table('issues')->where('status', 'not_started')->update(['status' => 'open']);
        DB::table('issues')->where('status', 'done')->update(['status' => 'closed']);
    }
};
