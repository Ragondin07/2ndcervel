<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('decisions', function (Blueprint $table): void {
            $table->timestamp('archived_at')->nullable()->after('status')->index();
        });

        Schema::table('actions', function (Blueprint $table): void {
            $table->timestamp('archived_at')->nullable()->after('completed_at')->index();
        });

        Schema::table('files', function (Blueprint $table): void {
            $table->timestamp('archived_at')->nullable()->after('ocr_status')->index();
        });
    }

    public function down(): void
    {
        Schema::table('decisions', function (Blueprint $table): void {
            $table->dropColumn('archived_at');
        });

        Schema::table('actions', function (Blueprint $table): void {
            $table->dropColumn('archived_at');
        });

        Schema::table('files', function (Blueprint $table): void {
            $table->dropColumn('archived_at');
        });
    }
};
