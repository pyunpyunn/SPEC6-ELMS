<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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

        if (Schema::getConnection()->getDriverName() !== 'sqlite') {
            Schema::table('leave_balances', function (Blueprint $table) {
                $table->unsignedInteger('allocated_days')->change();
                $table->unsignedInteger('used_days')->default(0)->change();
            });

            Schema::table('leave_applications', function (Blueprint $table) {
                $table->unsignedInteger('total_days')->default(1)->change();
            });
        }
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
