<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/admin-functions.php';
require_admin();
$categories = category_repository()->all();
$adminTitle = 'Categorias'; $adminSection = 'categories';
require __DIR__ . '/../includes/admin-header.php';
?>
<div class="admin-page-head"><div><p class="admin-kicker">Organização</p><h1>Categorias <em>editoriais.</em></h1><p>Organize os assuntos da casa sem perder a simplicidade.</p></div></div>
<div class="category-layout"><section class="admin-form-section"><h2>Nova categoria</h2><form class="admin-fields" action="<?= e(admin_url('categories/create.php')) ?>" method="post"><?= csrf_field() ?><label class="admin-field">Nome *<input type="text" name="name" maxlength="100" required></label><label class="admin-field">Slug<input type="text" name="slug" maxlength="120" placeholder="gerado pelo nome"></label><label class="admin-field">Descrição<textarea name="description" rows="4"></textarea></label><button class="admin-button primary" type="submit">Criar categoria</button></form></section>
<section class="admin-panel"><div class="panel-head"><div><p class="admin-kicker">Lista atual</p><h2><?= count($categories) ?> categorias</h2></div></div><div class="table-scroll"><table><thead><tr><th>Nome</th><th>Slug</th><th>Crônicas</th><th>Ações</th></tr></thead><tbody><?php foreach ($categories as $category): ?><tr><td><strong><?= e($category['name']) ?></strong><small><?= e($category['description']) ?></small></td><td><?= e($category['slug']) ?></td><td><?= $category['post_count'] ?></td><td><div class="row-actions"><a href="<?= e(admin_url('categories/edit.php?id=' . $category['id'])) ?>">Editar</a><form class="inline-form" action="<?= e(admin_url('categories/delete.php')) ?>" method="post" data-confirm="Excluir esta categoria?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $category['id'] ?>"><button class="link-button" type="submit" <?= $category['post_count'] ? 'disabled title="Categoria vinculada a crônicas"' : '' ?>>Excluir</button></form></div></td></tr><?php endforeach; ?></tbody></table></div></section></div>
<?php require __DIR__ . '/../includes/admin-footer.php'; ?>
