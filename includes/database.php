<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function database(): PDO
{
    static $pdo;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $directory = dirname(DATABASE_PATH);
    if (!is_dir($directory) && !mkdir($directory, 0770, true) && !is_dir($directory)) {
        throw new RuntimeException('Não foi possível criar o diretório do banco de dados.');
    }

    $pdo = new PDO('sqlite:' . DATABASE_PATH, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    if (DIRECTORY_SEPARATOR === '/') {
        @chmod(DATABASE_PATH, 0660);
    }
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA busy_timeout = 5000');
    initialize_database_schema($pdo);

    return $pdo;
}

function initialize_database_schema(PDO $pdo): void
{
    $statement = $pdo->prepare(
        "SELECT 1 FROM sqlite_master WHERE type = 'table' AND name = :table_name LIMIT 1"
    );
    $statement->execute(['table_name' => 'posts']);
    if ($statement->fetchColumn() !== false) {
        return;
    }

    $schemaPath = dirname(__DIR__) . '/database/schema.sql';
    $schema = file_get_contents($schemaPath);
    if ($schema === false || trim($schema) === '') {
        throw new RuntimeException('Não foi possível carregar o schema do banco de dados.');
    }

    try {
        $pdo->beginTransaction();
        $pdo->exec($schema);
        $pdo->commit();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw new RuntimeException('Não foi possível inicializar o banco de dados.', 0, $exception);
    }
}
