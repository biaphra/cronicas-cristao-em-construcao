<?php
declare(strict_types=1);
require_once __DIR__ . '/../../includes/auth.php';
require_admin();
$adminTitle = $adminTitle ?? 'Painel';
$adminSection = $adminSection ?? '';
$flash = get_flash();
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title><?= e($adminTitle) ?> — Administração</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Newsreader:opsz,wght@6..72,500;6..72,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(admin_url('assets/css/admin.css')) ?>">
</head>
<body class="admin-body">
<a class="admin-skip" href="#admin-content">Pular para o conteúdo</a>
<?php require __DIR__ . '/admin-sidebar.php'; ?>
<div class="admin-shell">
    <header class="admin-topbar"><button type="button" class="sidebar-toggle" data-sidebar-toggle aria-label="Abrir menu" aria-expanded="false">☰</button><div><span>ÁREA EDITORIAL</span><strong><?= e($adminTitle) ?></strong></div><div class="admin-user"><span><?= e(admin_user()['name'] ?? '') ?></span><span class="avatar" aria-hidden="true"><?= e(mb_substr(admin_user()['name'] ?? 'A', 0, 1)) ?></span></div></header>
    <main class="admin-content" id="admin-content">
        <?php if ($flash): ?><div class="admin-alert <?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?><button type="button" aria-label="Fechar mensagem" data-dismiss>×</button></div><?php endif; ?>
