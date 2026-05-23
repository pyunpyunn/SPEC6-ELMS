<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            if (Schema::hasColumn('employees', 'department')) {
                $table->dropColumn('department');
            }

            if (Schema::hasColumn('employees', 'position')) {
                $table->dropColumn('position');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            if (! Schema::hasColumn('employees', 'department')) {
                $table->string('department')->nullable()->after('manager_id');
            }

            if (! Schema::hasColumn('employees', 'position')) {
                $table->string('position')->nullable()->after('department');
            }
        });
    }
};
