<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$returnTo = clean_header((string) ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
if (!str_starts_with($returnTo, SITE_URL) && !preg_match('~^[a-z-]+\.php~i', $returnTo)) {
    $returnTo = 'index.php';
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_is_valid($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Não foi possível validar o formulário. Tente novamente.');
    redirect($returnTo);
}
$lastSubmission = (int) ($_SESSION['last_newsletter'] ?? 0);
if (time() - $lastSubmission < 15) {
    set_flash('error', 'Aguarde alguns segundos antes de tentar novamente.');
    redirect($returnTo);
}
$name = trim((string) ($_POST['name'] ?? ''));
$email = clean_header((string) ($_POST['email'] ?? ''));
if (mb_strlen($name) < 2 || mb_strlen($name) > 80 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'Preencha um nome e um e-mail válidos.');
    redirect($returnTo);
}
$record = json_encode(['created_at' => date(DATE_ATOM), 'name' => $name, 'email' => mb_strtolower($email)], JSON_UNESCAPED_UNICODE) . PHP_EOL;
$saved = file_put_contents(PRIVATE_STORAGE_PATH . '/subscribers.jsonl', $record, FILE_APPEND | LOCK_EX);
if ($saved !== false) {
    $_SESSION['last_newsletter'] = time();
}
set_flash($saved === false ? 'error' : 'success', $saved === false ? 'Não foi possível concluir agora. Tente novamente.' : 'Cadastro recebido. A próxima crônica encontra você por e-mail.');
redirect($returnTo);
