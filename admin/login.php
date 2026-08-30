<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
if (is_admin()) {
    redirect(admin_url('dashboard.php'));
}
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $email = clean_header((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        if (attempt_login($email, $password)) {
            redirect(admin_url('dashboard.php'));
        }
        usleep(350000);
        $error = 'E-mail ou senha inválidos.';
    } catch (Throwable $exception) {
        log_error($exception);
        $error = 'Não foi possível entrar agora. Tente novamente.';
    }
}
$flash = get_flash();
?>
<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Entrar — Administração</title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Newsreader:opsz,wght@6..72,500;6..72,600&display=swap" rel="stylesheet"><link rel="stylesheet" href="<?= e(admin_url('assets/css/admin.css')) ?>"></head>
<body class="login-body"><main class="login-card"><div class="login-brand"><span>C</span><strong>Crônicas<small>painel editorial</small></strong></div><p class="admin-kicker">Acesso reservado</p><h1>Bem-vindo<br><em>de volta.</em></h1><p>Entre para escrever, revisar e publicar.</p><?php if ($error || $flash): ?><div class="admin-alert error" role="alert"><?= e($error ?: $flash['message']) ?></div><?php endif; ?><form method="post" novalidate><?= csrf_field() ?><label>E-mail<input type="email" name="email" autocomplete="username" required autofocus></label><label>Senha<input type="password" name="password" autocomplete="current-password" required></label><button class="admin-button primary" type="submit">Entrar no painel <span>→</span></button></form><a href="<?= e(url()) ?>">← Voltar ao site</a></main></body></html>
