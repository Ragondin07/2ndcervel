<?php

namespace App\Jobs;

use App\Models\File;
use App\Services\OcrExtractor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExtractOcrJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 1;

    public function __construct(public int $fileId)
    {
        $this->onQueue('indexing');
    }

    public function handle(OcrExtractor $extractor): void
    {
        $file = File::query()->find($this->fileId);

        if (! $file) {
            Log::warning('ExtractOcrJob skipped missing file.', ['file_id' => $this->fileId]);

            return;
        }

        if (! $extractor->supports($file)) {
            $file->update([
                'ocr_status' => 'non_supporte',
                'ocr_error' => null,
            ]);
            $file->searchable();
            Log::info('OCR skipped unsupported file.', ['file_id' => $file->id]);

            return;
        }

        $file->update([
            'ocr_status' => 'en_cours',
            'ocr_error' => null,
        ]);
        $file->searchable();

        try {
            $text = $extractor->extract($file);
        } catch (\Throwable $exception) {
            $file->update([
                'ocr_status' => 'erreur',
                'ocr_error' => $exception->getMessage(),
            ]);
            $file->searchable();
            Log::warning('OCR extraction failed.', [
                'file_id' => $file->id,
                'error' => $exception->getMessage(),
            ]);

            return;
        }

        $file->update([
            'ocr_status' => 'termine',
            'ocr_text' => $text,
            'ocr_error' => null,
        ]);
        $file->searchable();

        Log::info('OCR extraction completed.', ['file_id' => $file->id]);
    }

    public function failed(?\Throwable $exception): void
    {
        File::query()
            ->whereKey($this->fileId)
            ->update([
                'ocr_status' => 'erreur',
                'ocr_error' => $exception?->getMessage(),
            ]);

        Log::error('OCR extraction job failed.', [
            'file_id' => $this->fileId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
