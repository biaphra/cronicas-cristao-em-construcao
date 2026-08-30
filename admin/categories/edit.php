<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/admin-functions.php';
require_admin();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$category = category_repository()->find($id);
if (!$category) { set_flash('error', 'Categoria não encontrada.'); redirect(admin_url('categories/index.php')); }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $name = trim((string) ($_POST['name'] ?? '')); $slug = slugify(trim((string) ($_POST['slug'] ?? $name)));
        if (mb_strlen($name) < 2 || mb_strlen($name) > 100) $errors[] = 'Informe um nome válido.';
        if (category_repository()->slugExists($slug, $id)) $errors[] = 'Já existe uma categoria com este slug.';
        $category = ['id' => $id, 'name' => $name, 'slug' => $slug, 'description' => trim((string) ($_POST['description'] ?? ''))];
        if (!$errors) { category_repository()->update($id, $category); set_flash('success', 'Categoria atualizada.'); redirect(admin_url('categories/edit.php?id=' . $id)); }
    } catch (Throwable $exception) { log_error($exception); $errors[] = 'Não foi possível atualizar a categoria.'; }
}
$adminTitle = 'Editar categoria'; $adminSection = 'categories'; require __DIR__ . '/../includes/admin-header.php';
?>
<div class="admin-page-head"><div><p class="admin-kicker">Organização</p><h1>Editar <em>categoria.</em></h1></div></div><?php if ($errors): ?><div class="admin-alert error"><?= e(implode(' ', $errors)) ?></div><?php endif; ?><section class="admin-form-section" style="max-width:700px"><form class="admin-fields" method="post"><?= csrf_field() ?><label class="admin-field">Nome *<input name="name" value="<?= e($category['name']) ?>" maxlength="100" required></label><label class="admin-field">Slug *<input name="slug" value="<?= e($category['slug']) ?>" maxlength="120" required></label><label class="admin-field">Descrição<textarea name="description" rows="5"><?= e($category['description']) ?></textarea></label><div class="row-actions"><button class="admin-button primary">Salvar alterações</button><a class="admin-button" href="<?= e(admin_url('categories/index.php')) ?>">Voltar</a></div></form></section>
<?php require __DIR__ . '/../includes/admin-footer.php'; ?>
