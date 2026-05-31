<?php

namespace App\Services;

use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class OcrExtractor
{
    public const SUPPORTED_EXTENSIONS = [
        'jpg',
        'jpeg',
        'png',
        'webp',
        'pdf',
    ];

    public function supports(File $file): bool
    {
        return self::supportsExtension($file->extension);
    }

    public static function supportsExtension(?string $extension): bool
    {
        return in_array(strtolower((string) $extension), self::SUPPORTED_EXTENSIONS, true);
    }

    public function extract(File $file): ?string
    {
        if (! $this->supports($file)) {
            return null;
        }

        $disk = Storage::disk('uploads');

        if (! $disk->exists($file->path)) {
            throw new RuntimeException('Le fichier original est introuvable sur le stockage uploads.');
        }

        $absolutePath = $disk->path($file->path);

        if (! is_readable($absolutePath)) {
            throw new RuntimeException('Le fichier original existe mais n est pas lisible par le worker. Verifiez les permissions uploads.');
        }
        $extension = strtolower((string) $file->extension);

        $text = $extension === 'pdf'
            ? $this->extractPdf($absolutePath)
            : $this->extractImage($absolutePath, $extension);

        $text = trim($text);

        return $text !== '' ? $text : null;
    }

    private function extractImage(string $absolutePath, string $extension): string
    {
        if ($extension !== 'webp') {
            return $this->runTesseract($absolutePath);
        }

        return $this->withTemporaryDirectory(function (string $directory) use ($absolutePath): string {
            $convertedPath = $directory.'/image.png';
            $this->convertWebp($absolutePath, $convertedPath);

            return $this->runTesseract($convertedPath);
        });
    }

    private function convertWebp(string $absolutePath, string $convertedPath): void
    {
        try {
            $this->runProcess(['magick', $absolutePath, $convertedPath], 'Conversion WebP impossible.');
        } catch (RuntimeException) {
            $this->runProcess(['convert', $absolutePath, $convertedPath], 'Conversion WebP impossible.');
        }
    }

    private function extractPdf(string $absolutePath): string
    {
        return $this->withTemporaryDirectory(function (string $directory) use ($absolutePath): string {
            $prefix = $directory.'/page';
            $maxPages = max(1, (int) config('services.ocr.max_pdf_pages', 20));

            $this->runProcess([
                'pdftoppm',
                '-png',
                '-r',
                (string) config('services.ocr.pdf_dpi', 200),
                '-f',
                '1',
                '-l',
                (string) $maxPages,
                $absolutePath,
                $prefix,
            ], 'Conversion PDF impossible.');

            $pages = glob($prefix.'-*.png') ?: [];
            sort($pages, SORT_NATURAL);

            if ($pages === []) {
                throw new RuntimeException('Aucune page PDF exploitable pour l OCR.');
            }

            $texts = [];
            foreach ($pages as $page) {
                $texts[] = $this->runTesseract($page);
            }

            return implode("\n\n", $texts);
        });
    }

    private function runTesseract(string $imagePath): string
    {
        return $this->runProcess([
            'tesseract',
            $imagePath,
            'stdout',
            '-l',
            (string) config('services.ocr.languages', 'fra+eng'),
        ], 'OCR Tesseract impossible.');
    }

    private function runProcess(array $command, string $failureMessage): string
    {
        $process = new Process($command);
        $process->setTimeout((int) config('services.ocr.timeout', 120));
        $process->run();

        if (! $process->isSuccessful()) {
            $details = trim($process->getErrorOutput()) ?: trim($process->getOutput());

            throw new RuntimeException($failureMessage.($details !== '' ? ' '.$details : ''));
        }

        return $process->getOutput();
    }

    /**
     * @template T
     *
     * @param callable(string): T $callback
     * @return T
     */
    private function withTemporaryDirectory(callable $callback): mixed
    {
        $directory = storage_path('framework/cache/ocr/'.Str::uuid());

        if (! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException('Impossible de preparer le dossier temporaire OCR. Verifiez storage/framework/cache.');
        }

        if (! is_writable($directory)) {
            throw new RuntimeException('Le dossier temporaire OCR n est pas accessible en ecriture. Verifiez les permissions storage.');
        }

        try {
            return $callback($directory);
        } finally {
            $this->deleteDirectory($directory);
        }
    }

    private function deleteDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        foreach (scandir($directory) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory.'/'.$item;

            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($directory);
    }
}
