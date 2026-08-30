<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';

$previewId = filter_input(INPUT_GET, 'preview', FILTER_VALIDATE_INT) ?: 0;
if ($previewId) {
    require_once __DIR__ . '/includes/auth.php';
    if (!is_admin()) { http_response_code(404); require __DIR__ . '/404.php'; exit; }
    $post = post_repository()->find($previewId);
} else {
    $slug = trim((string) ($_GET['slug'] ?? ''));
    $post = find_post($slug);
}
if (!$post) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}
$socialImage = $post['og_image'] ?: $post['featured_image'];
$socialImage = $socialImage ? (filter_var($socialImage, FILTER_VALIDATE_URL) ? $socialImage : url($socialImage)) : url('assets/img/social-card.svg');
$meta = page_meta([
    'title' => $post['title'] . ' — ' . site_name(),
    'description' => $post['excerpt'],
    'canonical' => url('cronica.php?slug=' . rawurlencode($post['slug'])),
    'type' => 'article',
    'image' => $socialImage,
    'robots' => $previewId ? 'noindex,nofollow' : 'index,follow',
]);
$schema = [
    '@context' => 'https://schema.org', '@type' => 'BlogPosting',
    'headline' => $post['title'], 'description' => $post['excerpt'],
    'author' => ['@type' => 'Person', 'name' => setting('author_name', 'Autor das Crônicas')],
    'datePublished' => $post['date'], 'dateModified' => $post['date'],
    'mainEntityOfPage' => $meta['canonical'], 'image' => $meta['image'],
    'publisher' => ['@type' => 'Organization', 'name' => site_name()],
];
$bodyClass = 'article-page';
require __DIR__ . '/includes/header.php';
$related = post_repository()->related($post['id'], $post['category_id'], 3);
?>
<main id="conteudo">
    <article class="article" data-article>
        <header class="article-header container">
            <a class="back-link" href="cronicas.php">← Voltar às crônicas</a>
            <div class="article-category"><span><?= e($post['category']) ?></span><i></i><span>CRÔNICA Nº <?= str_pad((string) $post['id'], 3, '0', STR_PAD_LEFT) ?></span></div>
            <h1><?= e($post['title']) ?></h1>
            <p><?= e($post['subtitle']) ?></p>
            <div class="article-byline"><div><span>POR</span><strong><?= e(setting('author_name', 'Autor das Crônicas')) ?></strong></div><div><span>PUBLICADO EM</span><time datetime="<?= e($post['date']) ?>"><?= e(format_date($post['date'])) ?></time></div><div><span>LEITURA</span><strong><?= e($post['reading_time']) ?></strong></div></div>
        </header>
        <?php if ($previewId): ?><div class="container form-message success" role="status">Preview administrativo — esta crônica ainda pode não estar publicada.</div><?php endif; ?>
        <?php if ($post['featured_image']): ?><figure class="article-cover container"><img src="<?= e($post['featured_image']) ?>" alt="" loading="eager"></figure><?php endif; ?>
        <div class="article-controls container"><button type="button" data-reading-mode aria-pressed="false">◫ Modo leitura</button><button type="button" data-font-decrease aria-label="Diminuir texto">A−</button><button type="button" data-font-increase aria-label="Aumentar texto">A+</button></div>
        <div class="article-body" data-article-body><?= $post['content'] ?></div>
        <footer class="article-end container">
            <span class="end-mark" aria-hidden="true">✦</span><h2>Essa crônica conversou com você?</h2><p>Talvez ela também converse com alguém que você conhece.</p>
            <div class="share-actions"><a class="button button-outline" href="https://wa.me/?text=<?= rawurlencode($post['title'] . ' — ' . $meta['canonical']) ?>" target="_blank" rel="noopener">Compartilhar no WhatsApp</a><button class="button button-outline" type="button" data-copy-link data-url="<?= e($meta['canonical']) ?>">Copiar link</button></div><p class="copy-status" data-copy-status role="status"></p>
        </footer>
    </article>
    <section class="section related" aria-labelledby="related-title"><div class="container"><div class="section-heading"><div><span class="eyebrow">Continue por aqui</span><h2 id="related-title">Leia <em>também</em></h2></div></div><div class="posts-grid"><?php foreach ($related as $post) { require __DIR__ . '/components/post-card.php'; } ?></div></div></section>
    <?php require __DIR__ . '/components/newsletter.php'; ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
