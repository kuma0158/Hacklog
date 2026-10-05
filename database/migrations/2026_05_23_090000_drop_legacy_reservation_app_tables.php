<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('service_days');
        Schema::dropIfExists('weekday_templates');
        Schema::dropIfExists('slots');
        Schema::dropIfExists('children');
        Schema::dropIfExists('facilities');

        DB::table('migrations')->whereIn('migration', [
            '2025_08_13_070008_create_children_table',
            '2025_08_13_070015_create_slots_table',
            '2025_08_13_070021_create_reservations_table',
            '2025_08_14_050510_create_facilities_table',
            '2025_08_14_050518_create_service_days_table',
            '2025_08_14_050525_create_weekday_templates_table',
        ])->delete();
    }

    public function down(): void
    {
        // Rollback intentionally does not recreate these tables — they originated from a
        // different application that previously shared this database.
    }
};
