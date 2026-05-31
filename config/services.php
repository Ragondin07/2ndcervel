<?php

return [
    'tika' => [
        'url' => env('TIKA_URL', 'http://tika:9998'),
        'timeout' => (int) env('TIKA_TIMEOUT', 60),
    ],

    'ocr' => [
        'languages' => env('OCR_LANGUAGES', 'fra+eng'),
        'timeout' => (int) env('OCR_TIMEOUT', 120),
        'max_pdf_pages' => (int) env('OCR_MAX_PDF_PAGES', 20),
        'pdf_dpi' => (int) env('OCR_PDF_DPI', 200),
    ],
];
