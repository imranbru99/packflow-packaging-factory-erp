<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('kind')->default('raw');
            $table->string('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->unique(['warehouse_id', 'code']);
            $table->timestamps();
        });

        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('name_bn')->nullable();
            $table->string('category')->index();
            $table->string('uom', 10)->default('kg');
            $table->decimal('gsm', 8, 2)->nullable();
            $table->decimal('width_mm', 8, 2)->nullable();
            $table->string('flute', 8)->nullable();
            $table->decimal('reorder_level', 14, 3)->default(0);
            $table->decimal('standard_cost', 14, 4)->default(0);
            $table->string('barcode')->nullable()->index();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('lots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('lot_no');
            $table->string('barcode')->nullable()->index();
            $table->decimal('qty_on_hand', 14, 3)->default(0);
            $table->decimal('qty_reserved', 14, 3)->default(0);
            $table->decimal('unit_cost', 14, 4)->default(0);
            $table->decimal('gsm', 8, 2)->nullable();
            $table->decimal('reel_width_mm', 8, 2)->nullable();
            $table->decimal('reel_weight_kg', 14, 3)->nullable();
            $table->date('received_on')->nullable()->index();
            $table->date('expires_on')->nullable();
            $table->timestamps();
            $table->unique(['material_id', 'lot_no']);
            $table->index(['material_id', 'received_on']);
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lot_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30)->index();
            $table->decimal('qty', 14, 3);
            $table->decimal('unit_cost', 14, 4)->default(0);
            $table->nullableMorphs('reference');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_bn')->nullable();
            $table->string('slug')->unique();
            $table->string('type')->index();
            $table->text('summary')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sku')->unique();
            $table->string('name');
            $table->string('name_bn')->nullable();
            $table->string('slug')->unique();
            $table->string('type')->index();
            $table->decimal('length_mm', 8, 2)->nullable();
            $table->decimal('width_mm', 8, 2)->nullable();
            $table->decimal('height_mm', 8, 2)->nullable();
            $table->decimal('gusset_mm', 8, 2)->nullable();
            $table->decimal('board_gsm', 8, 2)->nullable();
            $table->string('flute', 8)->nullable();
            $table->string('film_structure')->nullable();
            $table->unsignedTinyInteger('ink_colors')->default(0);
            $table->json('finishing')->nullable();
            $table->text('description')->nullable();
            $table->text('description_bn')->nullable();
            $table->decimal('standard_cost', 14, 2)->default(0);
            $table->decimal('list_price', 14, 2)->default(0);
            $table->string('barcode')->nullable()->index();
            $table->boolean('is_public')->default(false);
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('sku')->unique();
            $table->string('name');
            $table->json('attributes')->nullable();
            $table->decimal('list_price', 14, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('specifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('value');
            $table->string('unit')->nullable();
            $table->timestamps();
        });

        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('kind')->default('film');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('recipe_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained()->cascadeOnDelete();
            $table->decimal('gsm', 8, 2)->default(0);
            $table->unsignedSmallInteger('sequence')->default(1);
            $table->timestamps();
        });

        Schema::create('toolings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('kind')->index();
            $table->string('status')->default('available')->index();
            $table->decimal('cost', 14, 2)->default(0);
            $table->unsignedInteger('impressions')->default(0);
            $table->string('location')->nullable();
            $table->timestamps();
        });

        Schema::create('boms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('material_id')->constrained()->restrictOnDelete();
            $table->decimal('qty_per', 14, 4);
            $table->string('uom', 10)->default('kg');
            $table->decimal('scrap_percent', 5, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('work_centers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name');
            $table->string('process')->index();
            $table->decimal('capacity_per_hour', 12, 2)->default(0);
            $table->decimal('rate_per_minute', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('routings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('routing_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('work_center_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('sequence');
            $table->string('name');
            $table->decimal('setup_minutes', 8, 2)->default(20);
            $table->decimal('speed_per_minute', 12, 2)->default(60);
            $table->unsignedTinyInteger('crew')->default(2);
            $table->timestamps();
        });

        Schema::create('finished_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->decimal('qty', 14, 3)->default(0);
            $table->timestamps();
            $table->unique(['product_id', 'warehouse_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finished_stocks');
        Schema::dropIfExists('routing_steps');
        Schema::dropIfExists('routings');
        Schema::dropIfExists('work_centers');
        Schema::dropIfExists('bom_items');
        Schema::dropIfExists('boms');
        Schema::dropIfExists('toolings');
        Schema::dropIfExists('recipe_components');
        Schema::dropIfExists('recipes');
        Schema::dropIfExists('specifications');
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('lots');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('locations');
        Schema::dropIfExists('warehouses');
    }
};
