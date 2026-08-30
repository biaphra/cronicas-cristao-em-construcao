<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('contato.php');
}
if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
    set_flash('error', 'Sua sessão expirou. Atualize a página e tente novamente.');
    redirect('contato.php');
}
if (!empty($_POST['website'])) {
    redirect('contato.php');
}
$lastSubmission = (int) ($_SESSION['last_contact'] ?? 0);
if (time() - $lastSubmission < 30) {
    set_flash('error', 'Aguarde alguns segundos antes de enviar outra mensagem.');
    redirect('contato.php');
}

$name = trim((string) ($_POST['name'] ?? ''));
$email = clean_header((string) ($_POST['email'] ?? ''));
$subject = trim((string) ($_POST['subject'] ?? ''));
$message = trim((string) ($_POST['message'] ?? ''));
$allowedSubjects = ['Convites', 'Parcerias', 'Publicidade', 'Eventos', 'Projetos editoriais', 'Outros'];

if (mb_strlen($name) < 2 || mb_strlen($name) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($subject, $allowedSubjects, true) || mb_strlen($message) < 10 || mb_strlen($message) > 3000) {
    set_flash('error', 'Confira os campos. A mensagem precisa ter ao menos 10 caracteres.');
    redirect('contato.php');
}

$record = json_encode([
    'created_at' => date(DATE_ATOM), 'name' => $name, 'email' => $email,
    'subject' => $subject, 'message' => $message,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;
$saved = file_put_contents(PRIVATE_STORAGE_PATH . '/messages.jsonl', $record, FILE_APPEND | LOCK_EX);
if ($saved === false) {
    set_flash('error', 'Não foi possível registrar sua mensagem agora. Tente novamente mais tarde.');
    redirect('contato.php');
}
$_SESSION['last_contact'] = time();
set_flash('success', 'Mensagem recebida. Obrigado por escrever com calma.');
redirect('contato.php');
