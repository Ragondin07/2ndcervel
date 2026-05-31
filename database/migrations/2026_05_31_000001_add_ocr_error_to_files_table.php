<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('files', function (Blueprint $table): void {
            $table->text('ocr_error')->nullable()->after('ocr_status');
        });

        DB::table('files')
            ->where('ocr_status', 'non_traite')
            ->whereIn('extension', ['jpg', 'jpeg', 'png', 'webp', 'pdf'])
            ->update(['ocr_status' => 'en_attente']);

        DB::table('files')
            ->where('ocr_status', 'non_traite')
            ->update(['ocr_status' => 'non_supporte']);
    }

    public function down(): void
    {
        Schema::table('files', function (Blueprint $table): void {
            $table->dropColumn('ocr_error');
        });
    }
};
