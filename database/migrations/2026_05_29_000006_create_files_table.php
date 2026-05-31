<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('note_id')->nullable()->constrained()->nullOnDelete();
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('path');
            $table->string('mime_type')->nullable()->index();
            $table->string('extension')->nullable()->index();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('hash', 128)->nullable()->index();
            $table->text('description')->nullable();
            $table->longText('extracted_text')->nullable();
            $table->longText('ocr_text')->nullable();
            $table->string('indexing_status')->default('en_attente')->index();
            $table->string('extraction_status')->default('non_traite')->index();
            $table->string('ocr_status')->default('non_traite')->index();
            $table->timestamps();

            $table->index(['project_id', 'indexing_status']);
            $table->index(['note_id', 'indexing_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
