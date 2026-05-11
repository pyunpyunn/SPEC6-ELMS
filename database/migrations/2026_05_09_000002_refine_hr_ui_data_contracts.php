<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'pending_employee_id')) {
                $table->string('pending_employee_id')->nullable()->unique()->after('status');
            }
        });

        DB::statement('ALTER TABLE leave_balances MODIFY allocated_days INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE leave_balances MODIFY used_days INT UNSIGNED NOT NULL DEFAULT 0');
        DB::statement('ALTER TABLE leave_applications MODIFY total_days INT UNSIGNED NOT NULL DEFAULT 1');
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'pending_employee_id')) {
                $table->dropColumn('pending_employee_id');
            }
        });
    }
};
