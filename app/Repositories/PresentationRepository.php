<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use PDOException;

final class PresentationRepository
{
    private ?bool $tableExists = null;

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function publishedSlides(string $sectionKey): array
    {
        if (!$this->exists()) {
            return [];
        }

        $statement = $this->pdo->prepare(
            'SELECT * FROM presentation_slides WHERE section_key = :section_key AND is_published = 1 ORDER BY sort_order ASC, id ASC'
        );
        $statement->execute(['section_key' => $sectionKey]);

        return array_map([$this, 'mapSlide'], $statement->fetchAll());
    }

    public function adminSlides(string $sectionKey): array
    {
        if (!$this->exists()) {
            return [];
        }

        $statement = $this->pdo->prepare(
            'SELECT * FROM presentation_slides WHERE section_key = :section_key ORDER BY sort_order ASC, id ASC'
        );
        $statement->execute(['section_key' => $sectionKey]);

        return $statement->fetchAll();
    }

    public function countsBySection(): array
    {
        if (!$this->exists()) {
            return [];
        }

        $statement = $this->pdo->query(
            'SELECT section_key, COUNT(*) AS total, SUM(CASE WHEN is_published = 1 THEN 1 ELSE 0 END) AS published_total
             FROM presentation_slides
             GROUP BY section_key'
        );

        $counts = [];

        foreach ($statement->fetchAll() as $row) {
            $counts[$row['section_key']] = [
                'total' => (int) $row['total'],
                'published_total' => (int) $row['published_total'],
            ];
        }

        return $counts;
    }

    public function find(int $id): ?array
    {
        if (!$this->exists()) {
            return null;
        }

        $statement = $this->pdo->prepare('SELECT * FROM presentation_slides WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $slide = $statement->fetch();

        return $slide ?: null;
    }

    public function save(array $data): int
    {
        if (!$this->exists()) {
            throw new PDOException('Table presentation_slides does not exist.');
        }

        $payload = [
            'section_key' => (string) $data['section_key'],
            'title' => normalized_whitespace((string) ($data['title'] ?? '')),
            'alt_text' => normalized_whitespace((string) ($data['alt_text'] ?? '')),
            'image_path' => (string) ($data['image_path'] ?? ''),
            'source_filename' => normalized_whitespace((string) ($data['source_filename'] ?? '')),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_published' => !empty($data['is_published']) ? 1 : 0,
        ];

        if (!empty($data['id'])) {
            $payload['id'] = (int) $data['id'];

            $statement = $this->pdo->prepare(
                'UPDATE presentation_slides
                 SET section_key = :section_key, title = :title, alt_text = :alt_text, image_path = :image_path,
                     source_filename = :source_filename, sort_order = :sort_order, is_published = :is_published,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );
            $statement->execute($payload);

            return $payload['id'];
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO presentation_slides (
                section_key, title, alt_text, image_path, source_filename, sort_order, is_published, created_at, updated_at
            ) VALUES (
                :section_key, :title, :alt_text, :image_path, :source_filename, :sort_order, :is_published, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            )'
        );
        $statement->execute($payload);

        return (int) $this->pdo->lastInsertId();
    }

    public function delete(int $id): void
    {
        if (!$this->exists()) {
            return;
        }

        $statement = $this->pdo->prepare('DELETE FROM presentation_slides WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function replaceSection(string $sectionKey, array $slides): void
    {
        if (!$this->exists()) {
            throw new PDOException('Table presentation_slides does not exist.');
        }

        $this->pdo->beginTransaction();

        try {
            $delete = $this->pdo->prepare('DELETE FROM presentation_slides WHERE section_key = :section_key');
            $delete->execute(['section_key' => $sectionKey]);

            foreach ($slides as $slide) {
                $slide['section_key'] = $sectionKey;
                $this->save($slide);
            }

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    public function exists(): bool
    {
        if ($this->tableExists !== null) {
            return $this->tableExists;
        }

        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $statement = $this->pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'presentation_slides'");
            $this->tableExists = (bool) $statement?->fetchColumn();

            return $this->tableExists;
        }

        $statement = $this->pdo->query("SHOW TABLES LIKE 'presentation_slides'");
        $this->tableExists = (bool) $statement?->fetchColumn();

        return $this->tableExists;
    }

    private function mapSlide(array $slide): array
    {
        return [
            'id' => (int) $slide['id'],
            'index' => (int) $slide['sort_order'],
            'filename' => $slide['source_filename'] !== '' ? $slide['source_filename'] : basename((string) $slide['image_path']),
            'url' => (string) $slide['image_path'],
            'alt' => $slide['alt_text'] !== '' ? (string) $slide['alt_text'] : (string) ($slide['title'] ?: 'Слайд'),
            'title' => (string) ($slide['title'] ?? ''),
        ];
    }
}
