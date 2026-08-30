<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_admin();
$counts = post_repository()->counts();
$categoriesCount = count(category_repository()->all());
$latest = post_repository()->adminList([], 6);
$adminTitle = 'Dashboard';
$adminSection = 'dashboard';
require __DIR__ . '/includes/admin-header.php';
?>
<div class="admin-page-head"><div><p class="admin-kicker">Visão geral da obra</p><h1>Bom trabalho, <em><?= e(explode(' ', admin_user()['name'])[0]) ?>.</em></h1><p>Aqui está o que acontece no seu caderno editorial.</p></div><a class="admin-button primary" href="<?= e(admin_url('posts/create.php')) ?>">+ Nova crônica</a></div>
<section class="stats-grid" aria-label="Resumo editorial"><div><span>Total de crônicas</span><strong><?= $counts['total'] ?></strong></div><div><span>Publicadas</span><strong><?= $counts['published'] ?></strong></div><div><span>Rascunhos</span><strong><?= $counts['draft'] ?></strong></div><div><span>Agendadas</span><strong><?= $counts['scheduled'] ?></strong></div><div><span>Categorias</span><strong><?= $categoriesCount ?></strong></div></section>
<section class="admin-panel"><div class="panel-head"><div><p class="admin-kicker">Movimento recente</p><h2>Últimas crônicas</h2></div><a href="<?= e(admin_url('posts/index.php')) ?>">Ver todas →</a></div><?php if (!$latest): ?><div class="admin-empty">Nenhuma crônica ainda.</div><?php else: ?><div class="table-scroll"><table><thead><tr><th>Título</th><th>Categoria</th><th>Status</th><th>Publicação</th><th>Atualizado</th><th><span class="sr-only">Ações</span></th></tr></thead><tbody><?php foreach ($latest as $post): ?><tr><td><strong><?= e($post['title']) ?></strong><small>/<?= e($post['slug']) ?></small></td><td><?= e($post['category']) ?></td><td><span class="status-badge <?= e($post['status']) ?>"><?= e(status_label($post['status'])) ?></span></td><td><?= e($post['published_at'] ? format_date($post['published_at']) : '—') ?></td><td><?= e(format_date($post['updated_at'])) ?></td><td><a href="<?= e(admin_url('posts/edit.php?id=' . $post['id'])) ?>">Editar</a></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php require __DIR__ . '/includes/admin-footer.php'; ?>
