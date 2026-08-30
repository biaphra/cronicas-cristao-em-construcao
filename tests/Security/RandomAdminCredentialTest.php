<?php
declare(strict_types=1);

function fail(string $message): never
{
    fwrite(STDERR, "FALHA: {$message}" . PHP_EOL);
    exit(1);
}

function runSeed(string $projectRoot, ?string $configuredPassword = null): array
{
    $temporaryDirectory = sys_get_temp_dir() . '/blog-seed-test-' . bin2hex(random_bytes(8));
    if (!mkdir($temporaryDirectory, 0700, true) && !is_dir($temporaryDirectory)) {
        fail('Não foi possível criar o diretório temporário.');
    }

    $databasePath = $temporaryDirectory . '/database.sqlite';
    $environment = getenv();
    if (!is_array($environment)) {
        $environment = [];
    }
    if ($configuredPassword === null) {
        unset($environment['ADMIN_PASSWORD']);
    } else {
        $environment['ADMIN_PASSWORD'] = $configuredPassword;
    }
    $environment['APP_ENV'] = 'local';
    $environment['DATABASE_PATH'] = $databasePath;
    $environment['PRIVATE_STORAGE_PATH'] = $temporaryDirectory;
    $environment['ADMIN_EMAIL'] = 'security-test@example.local';

    $pipes = [];
    $process = proc_open(
        [PHP_BINARY, $projectRoot . '/database/seed.php'],
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes,
        $projectRoot,
        $environment
    );
    if (!is_resource($process)) {
        fail('Não foi possível executar o seed de teste.');
    }

    $output = stream_get_contents($pipes[1]);
    $errorOutput = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);
    if ($exitCode !== 0) {
        fail('O seed falhou: ' . trim((string) $errorOutput));
    }
    if ($configuredPassword === null) {
        if (!preg_match('/Senha local temporária gerada: ([A-Za-z0-9_-]{32})/', (string) $output, $matches)) {
            fail('O seed não retornou uma senha aleatória no formato esperado.');
        }
        $password = $matches[1];
    } else {
        if (!str_contains((string) $output, 'Senha definida via ADMIN_PASSWORD.')) {
            fail('O seed não confirmou a credencial fornecida pelo ambiente.');
        }
        $password = $configuredPassword;
    }
    $pdo = new PDO('sqlite:' . $databasePath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $statement = $pdo->prepare('SELECT password_hash FROM users WHERE email = :email LIMIT 1');
    $statement->execute(['email' => 'security-test@example.local']);
    $hash = $statement->fetchColumn();
    if (!is_string($hash) || !password_verify($password, $hash)) {
        fail('A senha gerada não corresponde ao hash persistido.');
    }
    $pdo = null;

    unlink($databasePath);
    rmdir($temporaryDirectory);

    return [$password, $hash];
}

$projectRoot = dirname(__DIR__, 2);
[$firstPassword, $firstHash] = runSeed($projectRoot);
[$secondPassword, $secondHash] = runSeed($projectRoot);
runSeed($projectRoot, 'Credencial-de-teste-2026!');

if (hash_equals($firstPassword, $secondPassword) || hash_equals($firstHash, $secondHash)) {
    fail('Execuções independentes reutilizaram a mesma credencial.');
}

echo "OK: o seed aceita ADMIN_PASSWORD e gera credenciais locais únicas quando necessário." . PHP_EOL;
