<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('positions')) {
            Schema::create('positions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('department_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->timestamps();
                $table->unique(['department_id', 'name']);
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('pending_employee_id')->constrained()->nullOnDelete();
            }

            if (! Schema::hasColumn('users', 'position_id')) {
                $table->foreignId('position_id')->nullable()->after('department_id')->constrained('positions')->nullOnDelete();
            }
        });

        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'position_id')) {
                $table->foreignId('position_id')->nullable()->after('department_id')->constrained('positions')->nullOnDelete();
            }
        });

        $this->backfillExistingEmployeePositions();
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            if (Schema::hasColumn('employees', 'position_id')) {
                $table->dropConstrainedForeignId('position_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'position_id')) {
                $table->dropConstrainedForeignId('position_id');
            }

            if (Schema::hasColumn('users', 'department_id')) {
                $table->dropConstrainedForeignId('department_id');
            }
        });

        Schema::dropIfExists('positions');
    }

    private function backfillExistingEmployeePositions(): void
    {
        if (! Schema::hasTable('employees') || ! Schema::hasColumn('employees', 'department_id') || ! Schema::hasColumn('employees', 'position')) {
            return;
        }

        DB::table('employees')
            ->whereNotNull('department_id')
            ->whereNotNull('position')
            ->orderBy('id')
            ->get(['id', 'user_id', 'department_id', 'position'])
            ->each(function ($employee): void {
                $positionName = trim((string) $employee->position);

                if ($positionName === '') {
                    return;
                }

                $position = DB::table('positions')
                    ->where('department_id', $employee->department_id)
                    ->where('name', $positionName)
                    ->first();

                if (! $position) {
                    $positionId = DB::table('positions')->insertGetId([
                        'department_id' => $employee->department_id,
                        'name' => $positionName,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $positionId = $position->id;
                }

                DB::table('employees')->where('id', $employee->id)->update([
                    'position_id' => $positionId,
                    'updated_at' => now(),
                ]);

                if ($employee->user_id) {
                    DB::table('users')->where('id', $employee->user_id)->update([
                        'department_id' => $employee->department_id,
                        'position_id' => $positionId,
                        'updated_at' => now(),
                    ]);
                }
            });
    }
};
