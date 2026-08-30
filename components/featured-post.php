<?php
/** @var array $post */
?>
<article class="featured-card reveal">
    <div class="featured-index" aria-hidden="true">CRÔNICA Nº <?= str_pad((string) $post['id'], 3, '0', STR_PAD_LEFT) ?></div>
    <div class="featured-copy">
        <div class="post-card-meta"><span><?= e($post['category']) ?> &amp; vida real</span><span><?= e(format_date($post['date'])) ?></span></div>
        <h3><?= e($post['title']) ?></h3>
        <p><?= e($post['excerpt']) ?></p>
        <span class="reading-time"><?= e($post['reading_time']) ?> de leitura</span>
        <a class="button button-dark" href="cronica.php?slug=<?= e($post['slug']) ?>">Continuar lendo <span>→</span></a>
    </div>
    <a class="featured-visual" href="cronica.php?slug=<?= e($post['slug']) ?>" aria-label="Ler <?= e($post['title']) ?>"<?php if ($post['featured_image']): ?> style="background-image:linear-gradient(rgba(39,41,35,.35),rgba(39,41,35,.35)),url('<?= e($post['featured_image']) ?>');background-size:cover;background-position:center"<?php endif; ?>>
        <span class="visual-number">14</span><span class="visual-line"></span><em>tempo<br>&amp; confiança</em>
    </a>
</article>
