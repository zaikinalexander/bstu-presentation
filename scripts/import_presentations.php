<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Support\PresentationCatalog;

foreach (PresentationCatalog::sections() as $sectionKey => $section) {
    $slides = [];

    foreach (PresentationCatalog::defaultSlides($sectionKey) as $slide) {
        $slides[] = [
            'title' => 'Слайд ' . (int) $slide['index'],
            'alt_text' => (string) $slide['alt'],
            'image_path' => (string) $slide['url'],
            'source_filename' => (string) $slide['filename'],
            'sort_order' => (int) $slide['index'],
            'is_published' => 1,
        ];
    }

    presentation_repository()->replaceSection($sectionKey, $slides);
}

fwrite(STDOUT, "Presentation slides imported.\n");
