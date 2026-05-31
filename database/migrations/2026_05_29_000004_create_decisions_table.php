<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('decisions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('source_note_id')->nullable()->constrained('notes')->nullOnDelete();
            $table->string('title');
            $table->longText('decision');
            $table->longText('justification')->nullable();
            $table->longText('alternatives')->nullable();
            $table->longText('risks')->nullable();
            $table->longText('impact')->nullable();
            $table->string('status')->default('proposee')->index();
            $table->timestamps();

            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('decisions');
    }
};
