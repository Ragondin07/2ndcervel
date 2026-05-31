<?php

namespace App\Jobs;

use App\Models\File;
use App\Services\FileTextExtractor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class IndexFileJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(public int $fileId)
    {
        $this->onQueue('indexing');
    }

    public function handle(FileTextExtractor $extractor): void
    {
        $file = File::query()->find($this->fileId);

        if (! $file) {
            Log::warning('IndexFileJob skipped missing file.', ['file_id' => $this->fileId]);

            return;
        }

        $file->update([
            'indexing_status' => 'en_cours',
            'extraction_status' => $extractor->supports($file) ? 'en_cours' : 'non_supporte',
            'extraction_error' => null,
            'ocr_status' => 'non_traite',
        ]);

        if (! $extractor->supports($file)) {
            $file->update([
                'indexing_status' => 'indexe',
                'extraction_status' => 'non_supporte',
                'ocr_status' => 'non_traite',
            ]);
            $file->searchable();
            Log::info('File indexing skipped unsupported extraction.', ['file_id' => $file->id]);

            return;
        }

        try {
            $text = $extractor->extract($file);
        } catch (\Throwable $exception) {
            $file->update([
                'indexing_status' => 'erreur',
                'extraction_status' => 'erreur',
                'extraction_error' => $exception->getMessage(),
                'ocr_status' => 'non_traite',
            ]);
            $file->searchable();
            Log::warning('File text extraction failed.', [
                'file_id' => $file->id,
                'error' => $exception->getMessage(),
            ]);

            return;
        }

        $file->update([
            'indexing_status' => 'indexe',
            'extraction_status' => 'extrait',
            'extracted_text' => $text,
            'extraction_error' => null,
            'ocr_status' => 'non_traite',
        ]);
        $file->searchable();

        Log::info('File indexing job completed.', ['file_id' => $file->id]);
    }

    public function failed(?\Throwable $exception): void
    {
        File::query()
            ->whereKey($this->fileId)
            ->update([
                'indexing_status' => 'erreur',
                'extraction_status' => 'erreur',
                'extraction_error' => $exception?->getMessage(),
            ]);

        Log::error('File indexing job failed.', [
            'file_id' => $this->fileId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
