<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class PersonRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function countPublishedByType(): array
    {
        $statement = $this->pdo->query(
            'SELECT type, COUNT(*) AS total FROM people WHERE is_published = 1 GROUP BY type'
        );

        $counts = ['doctor' => 0, 'professor' => 0];

        foreach ($statement->fetchAll() as $row) {
            $counts[$row['type']] = (int) $row['total'];
        }

        return $counts;
    }

    public function all(string $type, array $filters = []): array
    {
        $conditions = ['type = :type'];
        $params = ['type' => $type];
        $search = normalized_whitespace((string) ($filters['search'] ?? ''));

        if (($filters['published_only'] ?? true) === true) {
            $conditions[] = 'is_published = 1';
        }

        if (!empty($filters['letter'])) {
            $conditions[] = 'alphabet_letter = :letter';
            $params['letter'] = mb_strtoupper((string) $filters['letter']);
        }

        $sql = sprintf(
            'SELECT * FROM people WHERE %s ORDER BY sort_name ASC, full_name ASC',
            implode(' AND ', $conditions)
        );

        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);

        $people = $statement->fetchAll();

        if ($search === '') {
            return $people;
        }

        $needle = mb_strtolower($search);

        return array_values(array_filter($people, static function (array $person) use ($needle): bool {
            $haystack = mb_strtolower(normalized_whitespace(implode(' ', [
                (string) ($person['full_name'] ?? ''),
                (string) ($person['headline'] ?? ''),
                (string) ($person['biography'] ?? ''),
                (string) ($person['award_note'] ?? ''),
                (string) ($person['search_text'] ?? ''),
            ])));

            return str_contains($haystack, $needle);
        }));
    }

    public function letters(string $type): array
    {
        $statement = $this->pdo->prepare(
            'SELECT DISTINCT alphabet_letter FROM people WHERE type = :type AND is_published = 1 ORDER BY alphabet_letter ASC'
        );
        $statement->execute(['type' => $type]);

        return array_map(
            static fn (array $row): string => $row['alphabet_letter'],
            $statement->fetchAll()
        );
    }

    public function findBySlug(string $type, string $slug): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT * FROM people WHERE type = :type AND slug = :slug AND is_published = 1 LIMIT 1'
        );
        $statement->execute(['type' => $type, 'slug' => $slug]);
        $person = $statement->fetch();

        return $person ?: null;
    }

    public function adminList(?string $type = null): array
    {
        if ($type) {
            $statement = $this->pdo->prepare('SELECT * FROM people WHERE type = :type ORDER BY type ASC, sort_name ASC');
            $statement->execute(['type' => $type]);

            return $statement->fetchAll();
        }

        $statement = $this->pdo->query('SELECT * FROM people ORDER BY type ASC, sort_name ASC');

        return $statement->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM people WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $person = $statement->fetch();

        return $person ?: null;
    }

    public function save(array $data): int
    {
        $yearAwarded = normalized_whitespace((string) ($data['year_awarded'] ?? ''));
        $yearAwardedValue = ($yearAwarded !== '' && (int) $yearAwarded > 0) ? (int) $yearAwarded : null;

        $payload = [
            'type' => $data['type'],
            'full_name' => normalized_whitespace($data['full_name'] ?? ''),
            'slug' => $data['slug'] !== '' ? slugify($data['slug']) : slugify(sort_name((string) ($data['full_name'] ?? ''))),
            'sort_name' => normalized_whitespace($data['sort_name'] !== '' ? $data['sort_name'] : sort_name($data['full_name'])),
            'alphabet_letter' => alphabet_letter($data['alphabet_letter'] !== '' ? $data['alphabet_letter'] : $data['full_name']),
            'headline' => normalized_whitespace($data['headline'] ?? ''),
            'biography' => trim((string) ($data['biography'] ?? '')),
            'award_note' => normalized_whitespace($data['award_note'] ?? ''),
            'image_path' => $data['image_path'] ?? null,
            'year_awarded' => $yearAwardedValue,
            'source_index' => $data['source_index'] !== '' ? (int) $data['source_index'] : null,
            'is_published' => !empty($data['is_published']) ? 1 : 0,
        ];

        $payload['search_text'] = normalized_whitespace(implode(' ', [
            $payload['full_name'],
            $payload['headline'],
            $payload['biography'],
            $payload['award_note'],
        ]));

        if (!empty($data['id'])) {
            $payload['id'] = (int) $data['id'];

            $statement = $this->pdo->prepare(
                'UPDATE people
                 SET type = :type, full_name = :full_name, slug = :slug, sort_name = :sort_name,
                     alphabet_letter = :alphabet_letter, headline = :headline, biography = :biography,
                     award_note = :award_note, image_path = :image_path, year_awarded = :year_awarded,
                     source_index = :source_index, search_text = :search_text, is_published = :is_published,
                     updated_at = CURRENT_TIMESTAMP
                 WHERE id = :id'
            );
            $statement->execute($payload);

            return $payload['id'];
        }

        $statement = $this->pdo->prepare(
            'INSERT INTO people (
                type, full_name, slug, sort_name, alphabet_letter, headline, biography,
                award_note, image_path, year_awarded, source_index, search_text, is_published,
                created_at, updated_at
            ) VALUES (
                :type, :full_name, :slug, :sort_name, :alphabet_letter, :headline, :biography,
                :award_note, :image_path, :year_awarded, :source_index, :search_text, :is_published,
                CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            )'
        );
        $statement->execute($payload);

        return (int) $this->pdo->lastInsertId();
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM people WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function replaceImported(array $records): void
    {
        $this->pdo->beginTransaction();

        try {
            $this->pdo->exec('DELETE FROM people');

            foreach ($records as $record) {
                $this->save($record);
            }

            $this->pdo->commit();
        } catch (\Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }
}
