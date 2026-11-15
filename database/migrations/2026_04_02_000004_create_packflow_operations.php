<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_center_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('status')->default('running')->index();
            $table->date('installed_on')->nullable();
            $table->decimal('oee_target', 5, 2)->default(75);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('sales_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sales_order_line_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('planned')->index();
            $table->decimal('quantity', 14, 3);
            $table->decimal('good_qty', 14, 3)->default(0);
            $table->decimal('waste_qty', 14, 3)->default(0);
            $table->date('planned_start')->nullable()->index();
            $table->date('planned_end')->nullable();
            $table->string('priority')->default('normal');
            $table->string('group_key')->nullable()->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('production_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('routing_step_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('work_center_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('machine_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('sequence');
            $table->string('name');
            $table->string('status')->default('pending')->index();
            $table->decimal('planned_qty', 14, 3)->default(0);
            $table->decimal('good_qty', 14, 3)->default(0);
            $table->decimal('waste_qty', 14, 3)->default(0);
            $table->decimal('downtime_minutes', 8, 2)->default(0);
            $table->string('downtime_reason')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['production_order_id', 'sequence']);
        });

        Schema::create('material_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained()->restrictOnDelete();
            $table->decimal('required_qty', 14, 3);
            $table->decimal('reserved_qty', 14, 3)->default(0);
            $table->decimal('issued_qty', 14, 3)->default(0);
            $table->decimal('shortage_qty', 14, 3)->default(0);
            $table->string('uom', 10)->default('kg');
            $table->timestamps();
        });

        Schema::create('schedule_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('machine_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('work_center_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('starts_at')->index();
            $table->timestamp('ends_at');
            $table->string('label')->nullable();
            $table->timestamps();
        });

        Schema::create('inspections', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('production_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('inspector_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('kind')->default('final')->index();
            $table->string('result')->default('pending')->index();
            $table->timestamp('inspected_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('inspection_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->constrained()->cascadeOnDelete();
            $table->string('checkpoint');
            $table->string('spec')->nullable();
            $table->string('actual')->nullable();
            $table->string('result')->default('pass');
            $table->timestamps();
        });

        Schema::create('defects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inspection_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('production_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('machine_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lot_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('code');
            $table->string('description');
            $table->decimal('qty', 14, 3)->default(0);
            $table->string('severity')->default('major');
            $table->timestamps();
        });

        Schema::create('corrective_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('defect_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('status')->default('open')->index();
            $table->date('due_on')->nullable();
            $table->timestamps();
        });

        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sales_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('status')->default('open')->index();
            $table->string('severity')->default('major');
            $table->timestamps();
        });

        Schema::create('maintenance_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedSmallInteger('interval_days')->default(30);
            $table->date('last_done_on')->nullable();
            $table->date('next_due_on')->nullable()->index();
            $table->text('tasks')->nullable();
            $table->timestamps();
        });

        Schema::create('maintenance_orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('maintenance_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('kind')->default('breakdown');
            $table->string('status')->default('open')->index();
            $table->string('title');
            $table->text('resolution')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('downtime_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->cascadeOnDelete();
            $table->foreignId('production_stage_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reason');
            $table->decimal('minutes', 8, 2);
            $table->timestamp('started_at')->nullable();
            $table->timestamps();
            $table->index(['machine_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('downtime_events');
        Schema::dropIfExists('maintenance_orders');
        Schema::dropIfExists('maintenance_plans');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('corrective_actions');
        Schema::dropIfExists('defects');
        Schema::dropIfExists('inspection_items');
        Schema::dropIfExists('inspections');
        Schema::dropIfExists('schedule_slots');
        Schema::dropIfExists('material_requirements');
        Schema::dropIfExists('production_stages');
        Schema::dropIfExists('production_orders');
        Schema::dropIfExists('machines');
    }
};
