<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/admin-functions.php';
require_admin();

$filters = [
    'search' => trim((string) ($_GET['search'] ?? '')),
    'status' => trim((string) ($_GET['status'] ?? '')),
    'category_id' => filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT) ?: '',
    'featured' => isset($_GET['featured']) ? (string) $_GET['featured'] : '',
    'date' => trim((string) ($_GET['date'] ?? '')),
];
$posts = post_repository()->adminList($filters, 100);
$categories = category_repository()->all();
$hasFilters = count(array_filter($filters, static fn(mixed $value): bool => $value !== '')) > 0;
$resultLabel = count($posts) === 1 ? 'crônica encontrada' : 'crônicas encontradas';
$adminTitle = 'Crônicas';
$adminSection = 'posts';
require __DIR__ . '/../includes/admin-header.php';
?>
<section class="admin-panel posts-list-panel">
    <div class="admin-page-head posts-page-head">
        <div>
            <p class="admin-kicker">Arquivo editorial</p>
            <h1>Crônicas</h1>
            <p>Gerencie, revise e publique os conteúdos do site.</p>
        </div>
        <a class="admin-button primary posts-create-button" href="<?= e(admin_url('posts/create.php')) ?>"><span aria-hidden="true">+</span> Nova crônica</a>
    </div>

    <div class="filter-heading">
        <div>
            <p class="admin-kicker">Refine o arquivo</p>
            <h2>Filtrar crônicas</h2>
        </div>
        <?php if ($hasFilters): ?>
            <a class="clear-filters" href="<?= e(admin_url('posts/index.php')) ?>">× Limpar filtros</a>
        <?php endif; ?>
    </div>

    <form class="filter-bar posts-filter-bar" method="get">
        <label class="filter-control filter-search">
            <span>Busca</span>
            <span class="filter-input-wrap"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg><input type="search" name="search" value="<?= e($filters['search']) ?>" placeholder="Buscar por título, slug ou conteúdo..."></span>
        </label>
        <label class="filter-control">
            <span>Status</span>
            <select name="status"><option value="">Todos</option><?php foreach (['draft', 'scheduled', 'published', 'archived'] as $status): ?><option value="<?= $status ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>><?= e(status_label($status)) ?></option><?php endforeach; ?></select>
        </label>
        <label class="filter-control">
            <span>Categoria</span>
            <select name="category_id"><option value="">Todas</option><?php foreach ($categories as $category): ?><option value="<?= $category['id'] ?>" <?= (int) $filters['category_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select>
        </label>
        <label class="filter-control">
            <span>Destaque</span>
            <select name="featured"><option value="">Todos</option><option value="1" <?= $filters['featured'] === '1' ? 'selected' : '' ?>>Em destaque</option><option value="0" <?= $filters['featured'] === '0' ? 'selected' : '' ?>>Sem destaque</option></select>
        </label>
        <label class="filter-control">
            <span>Publicação</span>
            <input type="date" name="date" value="<?= e($filters['date']) ?>">
        </label>
        <button class="admin-button filter-submit" type="submit">Aplicar filtros <span>→</span></button>
    </form>

    <div class="list-summary">
        <p><strong><?= count($posts) ?></strong> <?= e($resultLabel) ?></p>
        <?php if ($hasFilters): ?><span>Exibindo uma seleção do arquivo</span><?php else: ?><span>Ordenadas pela atualização mais recente</span><?php endif; ?>
    </div>

    <?php if (!$posts): ?>
        <div class="admin-empty posts-empty-state"><span class="empty-state-icon" aria-hidden="true">□</span><strong>Nenhuma crônica encontrada.</strong><?php if ($hasFilters): ?><span>Nenhuma crônica corresponde aos filtros selecionados.</span><a class="admin-button" href="<?= e(admin_url('posts/index.php')) ?>">Limpar filtros</a><?php else: ?><span>Você ainda não publicou nenhum conteúdo.</span><a class="admin-button primary posts-create-button" href="<?= e(admin_url('posts/create.php')) ?>">+ Criar primeira crônica</a><?php endif; ?></div>
    <?php else: ?>
        <div class="table-scroll posts-table-wrap">
            <table class="posts-table">
                <colgroup><col class="title-column"><col class="category-column"><col class="status-column"><col class="featured-column"><col class="publication-column"><col class="actions-column"></colgroup>
                <thead><tr><th>Título</th><th>Categoria</th><th>Status</th><th>Destaque</th><th>Publicação</th><th>Ações</th></tr></thead>
                <tbody>
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td class="post-title-cell" data-label="Crônica"><a href="<?= e(admin_url('posts/edit.php?id=' . $post['id'])) ?>"><?= e($post['title']) ?></a><small><?= e($post['slug']) ?></small></td>
                        <td data-label="Categoria"><span class="category-label"><?= e($post['category']) ?></span></td>
                        <td data-label="Status"><span class="status-badge <?= e($post['status']) ?>"><?= e(status_label($post['status'])) ?></span></td>
                        <td data-label="Destaque"><?php if ($post['featured']): ?><span class="featured-badge">★ Destaque</span><?php else: ?><span class="muted-dash">—</span><?php endif; ?></td>
                        <td data-label="Publicação"><time class="publication-date" datetime="<?= e($post['published_at'] ?? '') ?>"><?= e(admin_format_date($post['published_at'])) ?></time></td>
                        <td data-label="Ações">
                            <div class="post-actions">
                                <a class="edit-action" href="<?= e(admin_url('posts/edit.php?id=' . $post['id'])) ?>">Editar</a>
                                <details class="action-menu">
                                    <summary aria-label="Mais ações para <?= e($post['title']) ?>" aria-haspopup="menu" aria-expanded="false">•••</summary>
                                    <div class="action-popover" role="menu">
                                        <a role="menuitem" href="<?= e(admin_url('posts/preview.php?id=' . $post['id'])) ?>" target="_blank">Visualizar <span>↗</span></a>
                                        <form action="<?= e(admin_url('posts/action.php')) ?>" method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $post['id'] ?>"><button role="menuitem" name="action" value="duplicate">Duplicar</button></form>
                                        <?php if ($post['status'] !== 'published'): ?><form action="<?= e(admin_url('posts/action.php')) ?>" method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $post['id'] ?>"><button role="menuitem" name="action" value="publish">Publicar</button></form><?php endif; ?>
                                        <?php if ($post['status'] !== 'archived'): ?><form action="<?= e(admin_url('posts/action.php')) ?>" method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $post['id'] ?>"><button role="menuitem" name="action" value="archive">Arquivar</button></form><?php endif; ?>
                                        <form action="<?= e(admin_url('posts/delete.php')) ?>" method="post" data-delete-form data-delete-title="<?= e($post['title']) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $post['id'] ?>"><button role="menuitem" class="danger-action" type="submit">Excluir</button></form>
                                    </div>
                                </details>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <dialog class="delete-dialog" data-delete-dialog aria-labelledby="delete-dialog-title" aria-describedby="delete-dialog-description">
        <form method="dialog" class="delete-dialog-card">
            <span class="delete-dialog-icon" aria-hidden="true">×</span>
            <p class="admin-kicker">Ação permanente</p>
            <h2 id="delete-dialog-title">Excluir crônica?</h2>
            <p id="delete-dialog-description"><strong data-delete-title></strong> será excluída permanentemente.</p>
            <div class="delete-dialog-actions"><button class="admin-button" value="cancel">Cancelar</button><button class="admin-button danger-confirm" value="confirm">Excluir crônica</button></div>
        </form>
    </dialog>
</section>
<?php require __DIR__ . '/../includes/admin-footer.php'; ?>
