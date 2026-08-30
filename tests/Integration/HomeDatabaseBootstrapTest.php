<?php
declare(strict_types=1);

function fail(string $message): never
{
    fwrite(STDERR, "FALHA: {$message}" . PHP_EOL);
    exit(1);
}

$projectRoot = dirname(__DIR__, 2);
$temporaryDirectory = sys_get_temp_dir() . '/blog-home-test-' . bin2hex(random_bytes(8));
if (!mkdir($temporaryDirectory, 0700, true) && !is_dir($temporaryDirectory)) {
    fail('Não foi possível criar o diretório temporário.');
}

$databasePath = $temporaryDirectory . '/database.sqlite';
$environment = getenv();
if (!is_array($environment)) {
    $environment = [];
}
$environment['APP_ENV'] = 'production';
$environment['DATABASE_PATH'] = $databasePath;
$environment['PRIVATE_STORAGE_PATH'] = $temporaryDirectory;
$environment['SITE_URL'] = 'http://localhost:8000';

$pipes = [];
$process = proc_open(
    [PHP_BINARY, $projectRoot . '/index.php'],
    [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
    $pipes,
    $projectRoot,
    $environment
);
if (!is_resource($process)) {
    fail('Não foi possível executar a página inicial.');
}

$output = stream_get_contents($pipes[1]);
$errorOutput = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);
$exitCode = proc_close($process);

if ($exitCode !== 0 || str_contains((string) $output, 'Fatal error')) {
    fail('A home falhou com um banco novo: ' . trim((string) $errorOutput));
}
if (!str_contains((string) $output, '<main id="conteudo">') || !str_contains((string) $output, '</html>')) {
    fail('A home não foi renderizada por completo.');
}

$pdo = new PDO('sqlite:' . $databasePath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$requiredTables = ['categories', 'posts', 'post_tags', 'settings', 'tags', 'users'];
$tables = $pdo->query("SELECT name FROM sqlite_master WHERE type = 'table'")->fetchAll(PDO::FETCH_COLUMN);
foreach ($requiredTables as $requiredTable) {
    if (!in_array($requiredTable, $tables, true)) {
        fail("A tabela {$requiredTable} não foi inicializada.");
    }
}
$pdo = null;

$permissions = fileperms($databasePath);
if ($permissions === false || ($permissions & 0007) !== 0) {
    fail('O SQLite novo ficou acessível a outros usuários do sistema.');
}

unlink($databasePath);
rmdir($temporaryDirectory);

echo 'OK: a home inicializa um SQLite novo e renderiza sem erro fatal.' . PHP_EOL;
