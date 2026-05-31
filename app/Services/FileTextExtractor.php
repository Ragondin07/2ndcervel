<?php

namespace App\Services;

use App\Models\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class FileTextExtractor
{
    private const SUPPORTED_EXTENSIONS = [
        'pdf',
        'docx',
        'xlsx',
        'ods',
        'csv',
        'txt',
        'md',
        'markdown',
        'pptx',
    ];

    public function supports(File $file): bool
    {
        return in_array(strtolower((string) $file->extension), self::SUPPORTED_EXTENSIONS, true);
    }

    public function extract(File $file): ?string
    {
        if (! $this->supports($file)) {
            return null;
        }

        $disk = Storage::disk('uploads');

        if (! $disk->exists($file->path)) {
            throw new RuntimeException('Le fichier original est introuvable sur le stockage.');
        }

        $absolutePath = $disk->path($file->path);
        $contents = file_get_contents($absolutePath);

        if ($contents === false) {
            throw new RuntimeException('Impossible de lire le fichier original.');
        }

        try {
            $response = Http::timeout((int) config('services.tika.timeout', 60))
                ->accept('text/plain')
                ->withBody($contents, $file->mime_type ?: 'application/octet-stream')
                ->put(rtrim((string) config('services.tika.url'), '/').'/tika');
        } catch (Throwable $exception) {
            throw new RuntimeException('Tika est indisponible : '.$exception->getMessage(), previous: $exception);
        }

        if (! $response->successful()) {
            throw new RuntimeException('Tika a retourne une erreur HTTP '.$response->status().'.');
        }

        $text = trim($response->body());

        return $text !== '' ? $text : null;
    }
}
