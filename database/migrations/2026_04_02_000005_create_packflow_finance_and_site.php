<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('sales_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('draft')->index();
            $table->date('issued_on');
            $table->date('due_on')->nullable();
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('tax_percent', 5, 2)->default(15);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->decimal('paid', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');
            $table->decimal('quantity', 14, 3);
            $table->decimal('unit_price', 14, 4);
            $table->decimal('line_total', 14, 2);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->date('paid_on');
            $table->decimal('amount', 14, 2);
            $table->string('method')->default('bank');
            $table->string('reference')->nullable();
            $table->timestamps();
        });

        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->date('entry_date');
            $table->string('memo')->nullable();
            $table->nullableMorphs('source');
            $table->timestamps();
        });

        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained()->cascadeOnDelete();
            $table->string('account');
            $table->decimal('debit', 14, 2)->default(0);
            $table->decimal('credit', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('job_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_order_id')->constrained()->cascadeOnDelete();
            $table->decimal('planned_material', 14, 2)->default(0);
            $table->decimal('actual_material', 14, 2)->default(0);
            $table->decimal('planned_conversion', 14, 2)->default(0);
            $table->decimal('actual_conversion', 14, 2)->default(0);
            $table->decimal('waste_cost', 14, 2)->default(0);
            $table->decimal('planned_total', 14, 2)->default(0);
            $table->decimal('actual_total', 14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('reporter_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->nullableMorphs('subject');
            $table->string('category')->index();
            $table->string('severity')->default('medium');
            $table->string('status')->default('open')->index();
            $table->string('title');
            $table->text('description');
            $table->text('ai_suggestion')->nullable();
            $table->string('ai_provider')->nullable();
            $table->timestamp('escalated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('issue_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('knowledge_articles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('title_bn')->nullable();
            $table->string('category')->index();
            $table->text('problem');
            $table->text('solution');
            $table->text('solution_bn')->nullable();
            $table->boolean('is_public')->default(false);
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('title_bn')->nullable();
            $table->longText('body')->nullable();
            $table->longText('body_bn')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description')->nullable();
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('title_bn')->nullable();
            $table->text('excerpt')->nullable();
            $table->longText('body');
            $table->longText('body_bn')->nullable();
            $table->boolean('is_published')->default(false)->index();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        Schema::create('case_studies', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('title_bn')->nullable();
            $table->string('client')->nullable();
            $table->string('pack_type')->nullable();
            $table->text('summary')->nullable();
            $table->longText('body')->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('author');
            $table->string('company')->nullable();
            $table->string('role')->nullable();
            $table->text('quote');
            $table->text('quote_bn')->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });

        Schema::create('quote_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained()->nullOnDelete();
            $table->string('company');
            $table->string('contact_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('pack_type')->nullable();
            $table->text('specification')->nullable();
            $table->unsignedInteger('quantity')->nullable();
            $table->string('status')->default('new');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_requests');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('case_studies');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('knowledge_articles');
        Schema::dropIfExists('issue_comments');
        Schema::dropIfExists('issues');
        Schema::dropIfExists('job_costs');
        Schema::dropIfExists('journal_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('invoices');
    }
};
