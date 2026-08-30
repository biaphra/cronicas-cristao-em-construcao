<?php
/** @var array $post */
?>
<article class="post-card reveal" data-post data-category="<?= e($post['category']) ?>" data-search="<?= e(mb_strtolower($post['title'] . ' ' . $post['excerpt'] . ' ' . $post['category'])) ?>">
    <div class="post-card-meta"><span><?= e($post['category']) ?></span><span><?= e($post['reading_time']) ?> de leitura</span></div>
    <h3><a href="cronica.php?slug=<?= e($post['slug']) ?>"><?= e($post['title']) ?></a></h3>
    <p><?= e($post['excerpt']) ?></p>
    <div class="post-card-footer"><time datetime="<?= e($post['date']) ?>"><?= e(format_date($post['date'])) ?></time><a class="text-link" href="cronica.php?slug=<?= e($post['slug']) ?>" aria-label="Continuar lendo: <?= e($post['title']) ?>">Continuar lendo <span>→</span></a></div>
</article>
