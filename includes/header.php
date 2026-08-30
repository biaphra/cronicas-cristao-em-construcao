<?php
declare(strict_types=1);

$meta = page_meta($meta ?? []);
$bodyClass = $bodyClass ?? '';
?>
<!doctype html>
<html lang="pt-BR" data-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($meta['title']) ?></title>
    <meta name="description" content="<?= e($meta['description']) ?>">
    <meta name="theme-color" content="#f4f0e8">
    <meta name="robots" content="<?= e($meta['robots']) ?>">
    <link rel="canonical" href="<?= e($meta['canonical']) ?>">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:type" content="<?= e($meta['type']) ?>">
    <meta property="og:title" content="<?= e($meta['title']) ?>">
    <meta property="og:description" content="<?= e($meta['description']) ?>">
    <meta property="og:url" content="<?= e($meta['canonical']) ?>">
    <meta property="og:image" content="<?= e($meta['image']) ?>">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="icon" href="assets/img/favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600&family=Newsreader:opsz,wght@6..72,400;6..72,500;6..72,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/main.css">
    <?php if (!empty($schema)): ?>
        <script type="application/ld+json"><?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
    <?php endif; ?>
    <script>document.documentElement.dataset.theme=localStorage.getItem('theme')||((matchMedia('(prefers-color-scheme: dark)').matches)?'dark':'light');</script>
</head>
<body class="<?= e($bodyClass) ?>">
<a class="skip-link" href="#conteudo">Pular para o conteúdo</a>
<div class="reading-progress" data-reading-progress hidden></div>
<header class="site-header" data-header>
    <div class="container header-inner">
        <a class="brand" href="index.php" aria-label="<?= e(site_name()) ?> — início">
            <span class="brand-mark" aria-hidden="true">C</span>
            <span class="brand-text"><strong>Crônicas</strong><small>de um cristão em construção</small></span>
        </a>
        <nav class="main-nav" id="main-nav" aria-label="Navegação principal" data-nav>
            <a href="index.php"<?= nav_active('index.php') ?>>Início</a>
            <a href="cronicas.php"<?= nav_active('cronicas.php') ?>>Crônicas</a>
            <a href="sobre.php"<?= nav_active('sobre.php') ?>>Sobre</a>
            <a href="contato.php"<?= nav_active('contato.php') ?>>Contato</a>
        </nav>
        <div class="header-actions">
            <button class="icon-button theme-toggle" type="button" aria-label="Alternar tema" title="Alternar tema" data-theme-toggle>
                <span class="theme-icon" aria-hidden="true">◐</span>
            </button>
            <a class="instagram-link" href="<?= e(setting('instagram_url', INSTAGRAM_URL)) ?>" target="_blank" rel="noopener" aria-label="Instagram">
                <svg aria-hidden="true" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/></svg>
            </a>
            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="main-nav" aria-label="Abrir menu" data-menu-toggle><span></span><span></span></button>
        </div>
    </div>
</header>
