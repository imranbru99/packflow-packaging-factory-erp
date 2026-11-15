<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('name_bn')->nullable();
            $table->timestamps();
        });

        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_bn')->nullable();
            $table->time('starts_at');
            $table->time('ends_at');
            $table->unsignedSmallInteger('break_minutes')->default(30);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('operator')->index();
            $table->string('phone')->nullable();
            $table->string('employee_code')->nullable()->unique();
            $table->string('designation')->nullable();
            $table->string('locale', 5)->default('en');
            $table->boolean('is_active')->default(true);
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('customer_id')->nullable()->index();
            $table->json('preferences')->nullable();
        });

        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_bn')->nullable();
            $table->string('tagline')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('Bangladesh');
            $table->string('currency', 3)->default('BDT');
            $table->string('tax_name')->default('VAT');
            $table->decimal('tax_percent', 5, 2)->default(15);
            $table->string('timezone')->default('Asia/Dhaka');
            $table->string('invoice_prefix', 10)->default('INV');
            $table->timestamps();
        });

        Schema::create('module_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('sequences', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->unsignedInteger('value')->default(0);
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->nullableMorphs('subject');
            $table->string('event', 40);
            $table->json('properties')->nullable();
            $table->timestamps();
            $table->index('created_at');
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('status')->default('pending')->index();
            $table->text('reason')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('overtime_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('worked_on');
            $table->decimal('hours', 5, 2);
            $table->string('reason')->nullable();
            $table->string('status')->default('pending')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_entries');
        Schema::dropIfExists('leave_requests');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('sequences');
        Schema::dropIfExists('module_settings');
        Schema::dropIfExists('company_settings');
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropConstrainedForeignId('shift_id');
            $table->dropColumn(['role', 'phone', 'employee_code', 'designation', 'locale', 'is_active', 'customer_id', 'preferences']);
        });
        Schema::dropIfExists('shifts');
        Schema::dropIfExists('departments');
    }
};
