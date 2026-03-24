<?php

declare(strict_types=1);

namespace App\Support;

use DOMDocument;
use DOMXPath;
use RuntimeException;
use ZipArchive;

final class WordImporter
{
    public function parseCollection(string $type, string $listFile, string $infoFile): array
    {
        $names = $this->parseNames($listFile);
        $paragraphs = $this->parseParagraphs($infoFile);

        $entries = [];
        $current = null;

        foreach ($paragraphs as $paragraph) {
            $text = normalized_whitespace($paragraph['text']);

            if (preg_match('/^(\d+)\.?\s*/u', $text, $matches)) {
                if ($current !== null) {
                    $entries[] = $this->finalizeEntry($type, $current);
                }

                $index = (int) $matches[1];
                $name = $names[$index] ?? preg_replace('/^(\d+)\.?\s*/u', '', $text) ?? '';
                $bodyText = trim(preg_replace('/^(\d+)\.?\s*/u', '', $text) ?? '');
                $headline = $this->stripLeadingName($bodyText, $name);

                $current = [
                    'type' => $type,
                    'index' => $index,
                    'full_name' => $name,
                    'headline' => ltrim($headline, "–—- "),
                    'paragraphs' => [],
                    'image_target' => $paragraph['images'][0] ?? null,
                    'source_docx' => $infoFile,
                ];

                continue;
            }

            if ($current === null) {
                continue;
            }

            if ($current['image_target'] === null && !empty($paragraph['images'])) {
                $current['image_target'] = $paragraph['images'][0];
            }

            if ($text !== '') {
                $current['paragraphs'][] = $text;
            }
        }

        if ($current !== null) {
            $entries[] = $this->finalizeEntry($type, $current);
        }

        return $entries;
    }

    public function extractImage(string $docxFile, string $internalPath): ?array
    {
        $zip = new ZipArchive();

        if ($zip->open($docxFile) !== true) {
            throw new RuntimeException('Cannot open Word file: ' . $docxFile);
        }

        $content = $zip->getFromName($internalPath);
        $zip->close();

        if ($content === false) {
            return null;
        }

        $extension = pathinfo($internalPath, PATHINFO_EXTENSION) ?: 'jpg';

        return [
            'content' => $content,
            'extension' => strtolower($extension),
        ];
    }

    private function parseNames(string $docxFile): array
    {
        $paragraphs = $this->parseParagraphs($docxFile);
        $names = [];

        foreach ($paragraphs as $paragraph) {
            $text = normalized_whitespace($paragraph['text']);

            if (preg_match('/^(\d+)\.\s*(.+)$/u', $text, $matches)) {
                $names[(int) $matches[1]] = normalized_whitespace($matches[2]);
            }
        }

        return $names;
    }

    private function finalizeEntry(string $type, array $entry): array
    {
        $awardNote = '';
        $body = [];
        $yearAwarded = null;

        foreach ($entry['paragraphs'] as $paragraph) {
            if (preg_match('/Поч[её]тн(ый|ая|ое|ые)\s+(доктор|профессор).+БГТУ/iu', $paragraph)) {
                $awardNote = $paragraph;

                if (preg_match('/\((\d{4})\)/', $paragraph, $matches)) {
                    $yearAwarded = (int) $matches[1];
                }

                continue;
            }

            $body[] = $paragraph;
        }

        $headline = normalized_whitespace($entry['headline']);

        if ($headline === '' && !empty($body)) {
            $headline = array_shift($body);
        }

        return [
            'type' => $type,
            'source_index' => $entry['index'],
            'full_name' => normalized_whitespace($entry['full_name']),
            'slug' => slugify(sort_name($entry['full_name'])),
            'sort_name' => sort_name($entry['full_name']),
            'alphabet_letter' => alphabet_letter($entry['full_name']),
            'headline' => $headline,
            'biography' => implode("\n\n", array_map('normalized_whitespace', $body)),
            'award_note' => $awardNote,
            'year_awarded' => $yearAwarded,
            'image_target' => $entry['image_target'],
            'source_docx' => $entry['source_docx'],
            'is_published' => 1,
        ];
    }

    private function parseParagraphs(string $docxFile): array
    {
        $zip = new ZipArchive();

        if ($zip->open($docxFile) !== true) {
            throw new RuntimeException('Cannot open Word file: ' . $docxFile);
        }

        $documentXml = $zip->getFromName('word/document.xml');
        $relsXml = $zip->getFromName('word/_rels/document.xml.rels');
        $zip->close();

        if ($documentXml === false || $relsXml === false) {
            throw new RuntimeException('Broken Word file: ' . $docxFile);
        }

        $relationships = $this->parseRelationships($relsXml);
        $document = new DOMDocument();
        $document->loadXML($documentXml);
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
        $xpath->registerNamespace('a', 'http://schemas.openxmlformats.org/drawingml/2006/main');
        $xpath->registerNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');

        $paragraphs = [];

        foreach ($xpath->query('//w:body/w:p') ?: [] as $paragraphNode) {
            $texts = [];

            foreach ($xpath->query('.//w:t', $paragraphNode) ?: [] as $textNode) {
                $texts[] = $textNode->textContent;
            }

            $images = [];

            foreach ($xpath->query('.//a:blip', $paragraphNode) ?: [] as $blipNode) {
                $relationId = $blipNode->getAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'embed');

                if ($relationId !== '' && isset($relationships[$relationId])) {
                    $images[] = $relationships[$relationId];
                }
            }

            $paragraphs[] = [
                'text' => implode('', $texts),
                'images' => array_values(array_unique($images)),
            ];
        }

        return $paragraphs;
    }

    private function parseRelationships(string $xml): array
    {
        $document = new DOMDocument();
        $document->loadXML($xml);
        $xpath = new DOMXPath($document);
        $xpath->registerNamespace('r', 'http://schemas.openxmlformats.org/package/2006/relationships');

        $relationships = [];

        foreach ($xpath->query('//r:Relationship') ?: [] as $node) {
            $id = $node->attributes?->getNamedItem('Id')?->nodeValue ?? '';
            $target = $node->attributes?->getNamedItem('Target')?->nodeValue ?? '';

            if ($id !== '' && $target !== '') {
                $relationships[$id] = 'word/' . ltrim($target, '/');
            }
        }

        return $relationships;
    }

    private function stripLeadingName(string $text, string $name): string
    {
        $escapedName = preg_quote($name, '/');
        $stripped = preg_replace('/^' . $escapedName . '\s*/u', '', $text);

        if ($stripped !== null && $stripped !== $text) {
            return normalized_whitespace($stripped);
        }

        return normalized_whitespace($text);
    }
}
