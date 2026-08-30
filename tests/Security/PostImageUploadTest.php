<?php
declare(strict_types=1);

function fail(string $message): never
{
    fwrite(STDERR, "FALHA: {$message}" . PHP_EOL);
    exit(1);
}

function waitForServer(int $port): void
{
    for ($attempt = 0; $attempt < 50; $attempt++) {
        $socket = @fsockopen('127.0.0.1', $port, $errorCode, $errorMessage, 0.1);
        if (is_resource($socket)) {
            fclose($socket);
            return;
        }
        usleep(100000);
    }
    fail('O servidor de teste de upload não iniciou.');
}

function upload(string $filePath, int $port): array
{
    $pipes = [];
    $process = proc_open(
        ['curl', '--silent', '--show-error', '--output', '-', '--write-out', "\n%{http_code}", '--form', 'image=@' . $filePath, 'http://127.0.0.1:' . $port],
        [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
        $pipes
    );
    if (!is_resource($process)) {
        fail('Não foi possível enviar o arquivo de teste.');
    }
    $output = stream_get_contents($pipes[1]);
    $errorOutput = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    if (proc_close($process) !== 0) {
        fail('O upload HTTP falhou: ' . trim((string) $errorOutput));
    }
    [$body, $status] = array_pad(explode("\n", trim((string) $output), 2), 2, '');
    return [(int) $status, json_decode($body, true, 512, JSON_THROW_ON_ERROR)];
}

$projectRoot = dirname(__DIR__, 2);
$temporaryDirectory = sys_get_temp_dir() . '/blog-upload-test-' . bin2hex(random_bytes(8));
$uploadDirectory = $temporaryDirectory . '/uploads';
if (!mkdir($temporaryDirectory, 0700, true) && !is_dir($temporaryDirectory)) {
    fail('Não foi possível criar o diretório temporário.');
}

$validImage = $temporaryDirectory . '/valid.png';
$invalidImage = $temporaryDirectory . '/invalid.jpg';
file_put_contents($validImage, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true));
file_put_contents($invalidImage, '<?php echo "executado";');

$socket = stream_socket_server('tcp://127.0.0.1:0', $errorCode, $errorMessage);
if (!is_resource($socket)) {
    fail('Não foi possível reservar uma porta local.');
}
$address = stream_socket_get_name($socket, false);
fclose($socket);
$port = (int) substr((string) strrchr((string) $address, ':'), 1);

$environment = getenv();
if (!is_array($environment)) {
    $environment = [];
}
$environment['APP_ENV'] = 'production';
$environment['DATABASE_PATH'] = $temporaryDirectory . '/database.sqlite';
$environment['PRIVATE_STORAGE_PATH'] = $temporaryDirectory;
$environment['POST_UPLOAD_PATH'] = $uploadDirectory;

$serverPipes = [];
$server = proc_open(
    [PHP_BINARY, '-S', '127.0.0.1:' . $port, $projectRoot . '/tests/Fixtures/upload-endpoint.php'],
    [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
    $serverPipes,
    $projectRoot,
    $environment
);
if (!is_resource($server)) {
    fail('Não foi possível iniciar o servidor de teste.');
}

try {
    waitForServer($port);
    [$validStatus, $validResponse] = upload($validImage, $port);
    if ($validStatus !== 200 || ($validResponse['ok'] ?? false) !== true) {
        fail('Uma imagem PNG válida foi rejeitada.');
    }
    $savedFiles = is_dir($uploadDirectory) ? array_values(array_diff(scandir($uploadDirectory) ?: [], ['.', '..'])) : [];
    if (count($savedFiles) !== 1 || !preg_match('/^[a-f0-9]{32}\.png$/', $savedFiles[0])) {
        fail('O upload válido não recebeu um nome aleatório seguro.');
    }

    [$invalidStatus, $invalidResponse] = upload($invalidImage, $port);
    if ($invalidStatus !== 422 || ($invalidResponse['ok'] ?? true) !== false) {
        fail('Um arquivo executável disfarçado de imagem não foi rejeitado.');
    }
    if (count(array_values(array_diff(scandir($uploadDirectory) ?: [], ['.', '..']))) !== 1) {
        fail('O upload inválido criou um arquivo no armazenamento.');
    }
} finally {
    proc_terminate($server);
    foreach ($serverPipes as $pipe) {
        if (is_resource($pipe)) {
            fclose($pipe);
        }
    }
    proc_close($server);
}

unlink($uploadDirectory . '/' . $savedFiles[0]);
rmdir($uploadDirectory);
unlink($validImage);
unlink($invalidImage);
if (is_file($temporaryDirectory . '/database.sqlite')) {
    unlink($temporaryDirectory . '/database.sqlite');
}
rmdir($temporaryDirectory);

echo 'OK: uploads reais aceitam PNG válido e rejeitam executável disfarçado.' . PHP_EOL;
