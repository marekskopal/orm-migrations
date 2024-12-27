<?php

declare(strict_types=1);

namespace MarekSkopal\ORM\Migrations\Migration;

use MarekSkopal\ORM\Utils\NameUtils;
use PDO;

readonly class MigrationRepository
{
    public const string MigrationTable = '__migrations';

    public function __construct(private PDO $pdo)
    {
    }

    public function createMigrationTable(): void
    {
        $this->pdo->query(
            'CREATE TABLE IF NOT EXISTS ' . NameUtils::escape(
                self::MigrationTable,
            ) . ' (id INT PRIMARY KEY AUTO_INCREMENT, name VARCHAR(255) NOT NULL)',
        );
    }

    /** @return list<array{id: int, name: string}> */
    public function getFinishedMigrations(): array
    {
        $query = $this->pdo->query('SELECT * FROM ' . NameUtils::escape(self::MigrationTable));
        if ($query === false) {
            throw new \RuntimeException('Failed to fetch migrations');
        }

        // @phpstan-ignore-next-line return.type
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function insertMigration(string $name): void
    {
        $this->pdo->query('INSERT INTO ' . NameUtils::escape(self::MigrationTable) . ' (name) VALUES (\'' . $name . '\')');
    }
}
