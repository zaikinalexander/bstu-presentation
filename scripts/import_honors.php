<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/bootstrap.php';

use App\Support\WordImporter;

$importer = new WordImporter();

$definitions = [
    'doctor' => [
        'list' => base_path('Поч. доктора. Список.docx'),
        'info' => base_path('Поч. доктора. Информация.docx'),
    ],
    'professor' => [
        'list' => base_path('Поч. профессора. Список.docx'),
        'info' => base_path('Поч. профессора. Информация.docx'),
    ],
];

$records = [];

foreach ($definitions as $type => $files) {
    $entries = $importer->parseCollection($type, $files['list'], $files['info']);

    foreach ($entries as $entry) {
        if (!empty($entry['image_target'])) {
            $image = $importer->extractImage($entry['source_docx'], $entry['image_target']);

            if ($image !== null) {
                $directory = public_path('uploads/imported/' . $type);

                if (!is_dir($directory)) {
                    mkdir($directory, 0775, true);
                }

                $filename = $entry['slug'] . '.' . $image['extension'];
                file_put_contents($directory . DIRECTORY_SEPARATOR . $filename, $image['content']);
                $entry['image_path'] = 'uploads/imported/' . $type . '/' . $filename;
            }
        }

        unset($entry['image_target'], $entry['source_docx']);
        $records[] = $entry;
    }
}

person_repository()->replaceImported($records);

fwrite(STDOUT, sprintf("Imported %d records.\n", count($records)));
