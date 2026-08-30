<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
$meta = page_meta(['title' => 'Todas as crônicas — ' . site_name(), 'description' => 'Crônicas sobre fé, humor, relacionamentos e vida real.', 'canonical' => url('cronicas.php')]);
$category = trim((string) ($_GET['categoria'] ?? ''));
$search = trim((string) ($_GET['busca'] ?? ''));
$page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$perPage = max(3, min(24, (int) setting('posts_per_page', '9')));
$total = post_repository()->countPublished($category ?: null, $search ?: null);
$pageCount = max(1, (int) ceil($total / $perPage));
if ($page > $pageCount) $page = $pageCount;
$listedPosts = post_repository()->published($perPage, ($page - 1) * $perPage, $category ?: null, $search ?: null);
$categories = array_values(array_filter(
    category_repository()->all(),
    static fn(array $item): bool => strcasecmp((string) $item['name'], 'Reflexões') !== 0
));
require __DIR__ . '/includes/header.php';
?>
<main id="conteudo">
    <header class="page-hero container"><span class="eyebrow">Arquivo da obra</span><h1>Todas as <em>crônicas</em></h1><p>Textos para rir, pensar, crescer e caminhar com Deus — um parágrafo de cada vez.</p></header>
    <section class="archive container section" aria-label="Lista de crônicas">
        <div class="archive-tools">
            <div class="filters" aria-label="Filtrar por categoria"><a class="<?= $category === '' ? 'active' : '' ?>" href="cronicas.php">Todas</a><?php foreach ($categories as $item): ?><a class="<?= $category === $item['name'] ? 'active' : '' ?>" href="cronicas.php?categoria=<?= rawurlencode($item['name']) ?>"><?= e($item['name']) ?></a><?php endforeach; ?></div>
            <form class="search-field" method="get"><?php if ($category): ?><input type="hidden" name="categoria" value="<?= e($category) ?>"><?php endif; ?><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg><label class="sr-only" for="archive-search">Buscar uma crônica</label><input id="archive-search" name="busca" value="<?= e($search) ?>" type="search" placeholder="Buscar uma crônica..."><button class="sr-only" type="submit">Buscar</button></form>
        </div>
        <p class="results-count" aria-live="polite"><?= $total ?> <?= $total === 1 ? 'crônica encontrada' : 'crônicas encontradas' ?></p>
        <?php if ($listedPosts): ?><div class="posts-grid archive-grid"><?php foreach ($listedPosts as $post) { require __DIR__ . '/components/post-card.php'; } ?></div><?php else: ?><p class="empty-state">Nenhuma crônica apareceu por aqui. Tente outra busca.</p><?php endif; ?>
        <?php if ($pageCount > 1): ?><nav class="pagination" aria-label="Paginação"><?php for ($i = 1; $i <= $pageCount; $i++): ?><a href="?<?= e(http_build_query(['categoria' => $category, 'busca' => $search, 'page' => $i])) ?>" <?= $i === $page ? 'aria-current="page"' : '' ?>><?= $i ?></a><?php endfor; ?></nav><?php endif; ?>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
