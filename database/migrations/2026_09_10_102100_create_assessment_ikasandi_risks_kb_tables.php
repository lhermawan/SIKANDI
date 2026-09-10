<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assessment_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->decimal('weight', 5, 2)->default(1.00);
            $table->integer('order_num')->default(1);
            $table->timestamps();
        });

        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('assessment_categories')->cascadeOnDelete();
            $table->string('code')->unique();
            $table->text('question');
            $table->text('explanation')->nullable();
            $table->text('guidance')->nullable();
            $table->decimal('max_score', 5, 2)->default(100.00);
            $table->boolean('is_active')->default(true);
            $table->integer('order_num')->default(1);
            $table->timestamps();
        });

        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->string('assessment_code')->unique()->index(); // e.g. ASM-2026-001
            $table->string('title');
            $table->year('year');
            $table->string('period')->default('Tahunan');
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('assessor_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('status', [
                'draft', 'submitted', 'in_review', 'verified', 'published',
            ])->default('draft')->index();

            $table->decimal('compliance_score', 5, 2)->default(0.00);
            $table->decimal('risk_score', 5, 2)->default(0.00);
            $table->dateTime('verified_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('assessment_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('assessment_questions')->cascadeOnDelete();
            $table->enum('answer', ['compliant', 'partial', 'non_compliant', 'not_applicable'])->default('non_compliant');
            $table->decimal('score', 5, 2)->default(0.00);
            $table->text('notes')->nullable();
            $table->text('recommendation')->nullable();
            $table->timestamps();

            $table->unique(['assessment_id', 'question_id']);
        });

        Schema::create('assessment_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_answer_id')->constrained('assessment_answers')->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->integer('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('risks', function (Blueprint $table) {
            $table->id();
            $table->string('risk_code')->unique()->index(); // e.g. RSK-2026-001
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('organization_id')->constrained('organizations')->cascadeOnDelete();
            $table->foreignId('ci_id')->nullable()->constrained('configuration_items')->nullOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();

            $table->string('threat')->nullable();
            $table->string('vulnerability')->nullable();
            $table->unsignedTinyInteger('likelihood')->default(1); // 1-5
            $table->unsignedTinyInteger('impact')->default(1); // 1-5
            $table->unsignedTinyInteger('risk_score')->default(1); // 1-25
            $table->enum('risk_level', ['critical', 'high', 'medium', 'low'])->default('low')->index();

            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['identified', 'analyzed', 'treating', 'monitoring', 'closed'])->default('identified');
            $table->date('due_date')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('risk_treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('risk_id')->constrained('risks')->cascadeOnDelete();
            $table->enum('strategy', ['mitigate', 'transfer', 'avoid', 'accept'])->default('mitigate');
            $table->text('action_plan');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('knowledge_articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('category', [
                'network', 'server', 'website', 'security', 'application', 'hardware', 'sop', 'troubleshooting',
            ])->default('sop')->index();
            $table->longText('content');
            $table->foreignId('author_id')->constrained('users');
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_code')->unique()->index();
            $table->string('title');
            $table->enum('category', [
                'sop', 'policy', 'topology', 'inventory', 'assessment', 'incident', 'other',
            ])->default('other');
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->nullOnDelete();
            $table->foreignId('ci_id')->nullable()->constrained('configuration_items')->nullOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->string('file_path');
            $table->integer('file_size')->nullable();
            $table->string('version')->default('1.0');
            $table->boolean('is_confidential')->default(false);
            $table->foreignId('uploaded_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('knowledge_articles');
        Schema::dropIfExists('risk_treatments');
        Schema::dropIfExists('risks');
        Schema::dropIfExists('assessment_evidences');
        Schema::dropIfExists('assessment_answers');
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('assessment_questions');
        Schema::dropIfExists('assessment_categories');
    }
};
