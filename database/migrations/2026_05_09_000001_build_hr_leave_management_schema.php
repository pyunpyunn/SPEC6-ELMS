<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['pending', 'active', 'inactive'])->default('pending')->after('role');
            }
        });

        if (! Schema::hasTable('departments')) {
            Schema::create('departments', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name')->unique();
                $table->text('description')->nullable();
                $table->foreignId('manager_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        Schema::table('employees', function (Blueprint $table) {
            if (! Schema::hasColumn('employees', 'employee_id')) {
                $table->string('employee_id')->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('employees', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            }
            if (! Schema::hasColumn('employees', 'manager_id')) {
                $table->foreignId('manager_id')->nullable()->after('department_id')->constrained('employees')->nullOnDelete();
            }
            if (! Schema::hasColumn('employees', 'first_name')) {
                $table->string('first_name')->nullable()->after('manager_id');
            }
            if (! Schema::hasColumn('employees', 'last_name')) {
                $table->string('last_name')->nullable()->after('first_name');
            }
            if (! Schema::hasColumn('employees', 'phone')) {
                $table->string('phone')->nullable()->after('contact_info');
            }
            if (! Schema::hasColumn('employees', 'address')) {
                $table->string('address')->nullable()->after('phone');
            }
            if (! Schema::hasColumn('employees', 'daily_rate')) {
                $table->decimal('daily_rate', 10, 2)->default(1000)->after('address');
            }
            if (! Schema::hasColumn('employees', 'employment_status')) {
                $table->enum('employment_status', ['active', 'resigned', 'terminated'])->default('active')->after('daily_rate');
            }
        });

        Schema::table('leave_types', function (Blueprint $table) {
            if (! Schema::hasColumn('leave_types', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }
            if (! Schema::hasColumn('leave_types', 'requires_proof')) {
                $table->boolean('requires_proof')->default(false)->after('requires_approval');
            }
            if (! Schema::hasColumn('leave_types', 'proof_rules')) {
                $table->string('proof_rules')->nullable()->after('requires_proof');
            }
            if (! Schema::hasColumn('leave_types', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('proof_rules');
            }
        });

        if (Schema::hasColumn('leave_types', 'slug')) {
            DB::table('leave_types')->orderBy('id')->get()->groupBy('name')->each(function ($rows) {
                $rows->skip(1)->each(fn ($row) => DB::table('leave_types')->where('id', $row->id)->delete());
            });

            DB::table('leave_types')->orderBy('id')->get()->each(function ($type) {
                $slug = Str::slug($type->name);
                DB::table('leave_types')->where('id', $type->id)->update(['slug' => $slug ?: 'leave-type-'.$type->id]);
            });
        }

        if (! Schema::hasTable('leave_balances')) {
            Schema::create('leave_balances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
                $table->foreignId('leave_type_id')->constrained()->cascadeOnDelete();
                $table->unsignedInteger('year');
                $table->decimal('allocated_days', 8, 2);
                $table->decimal('used_days', 8, 2)->default(0);
                $table->timestamps();
                $table->unique(['employee_id', 'leave_type_id', 'year']);
            });
        }

        Schema::table('leave_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('leave_applications', 'total_days')) {
                $table->decimal('total_days', 8, 2)->default(1)->after('end_date');
            }
            if (! Schema::hasColumn('leave_applications', 'reviewed_by')) {
                $table->foreignId('reviewed_by')->nullable()->after('remarks')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('leave_applications', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            }
            if (! Schema::hasColumn('leave_applications', 'proof_path')) {
                $table->string('proof_path')->nullable()->after('reviewed_at');
            }
        });

        if (! Schema::hasTable('system_notifications')) {
            Schema::create('system_notifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->text('body');
                $table->string('type')->default('info');
                $table->string('action_url')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('system_notifications');
        Schema::dropIfExists('leave_balances');
        Schema::dropIfExists('departments');
    }
};
