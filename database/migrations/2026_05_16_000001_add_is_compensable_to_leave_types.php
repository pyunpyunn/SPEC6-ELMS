<?php

use App\Models\LeaveType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            if (! Schema::hasColumn('leave_types', 'is_compensable')) {
                $table->boolean('is_compensable')->default(false)->after('requires_approval');
            }
        });

        LeaveType::query()
            ->get()
            ->filter(fn (LeaveType $leaveType) => strtolower($leaveType->name) === 'vacation leave')
            ->each(fn (LeaveType $leaveType) => $leaveType->update(['is_compensable' => true]));
    }

    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            if (Schema::hasColumn('leave_types', 'is_compensable')) {
                $table->dropColumn('is_compensable');
            }
        });
    }
};
