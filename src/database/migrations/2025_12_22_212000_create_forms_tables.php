<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->jsonb('recipients');
            $table->text('success_message')->nullable();
            $table->jsonb('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index('is_active');
        });

        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('label');
            $table->string('name');
            $table->boolean('is_required')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->jsonb('options')->nullable();
            $table->jsonb('validation_rules')->nullable();
            $table->timestamps();

            $table->unique(['form_id', 'name']);
            $table->index(['form_id', 'sort_order']);
            $table->index('is_active');
        });

        Schema::create('form_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('new');
            $table->boolean('is_spam')->default(false);
            $table->jsonb('payload');
            $table->jsonb('meta')->nullable();
            $table->string('reply_to')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['form_id', 'status']);
            $table->index('is_spam');
            $table->index('created_at');
        });

        Schema::create('form_submission_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_submission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->string('field_name')->nullable();
            $table->timestamps();

            $table->index('field_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submission_files');
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('forms');
    }
};
