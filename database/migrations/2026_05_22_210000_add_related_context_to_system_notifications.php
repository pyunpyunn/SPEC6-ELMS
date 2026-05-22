<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_notifications', function (Blueprint $table): void {
            if (! Schema::hasColumn('system_notifications', 'related_type')) {
                $table->string('related_type')->nullable()->after('type');
            }

            if (! Schema::hasColumn('system_notifications', 'related_id')) {
                $table->unsignedBigInteger('related_id')->nullable()->after('related_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('system_notifications', function (Blueprint $table): void {
            if (Schema::hasColumn('system_notifications', 'related_id')) {
                $table->dropColumn('related_id');
            }

            if (Schema::hasColumn('system_notifications', 'related_type')) {
                $table->dropColumn('related_type');
            }
        });
    }
};
