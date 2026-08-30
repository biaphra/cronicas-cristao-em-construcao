<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/admin-functions.php';
require_admin();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
$post = post_repository()->find($id);
if (!$post) { set_flash('error', 'Crônica não encontrada.'); redirect(admin_url('posts/index.php')); }
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        [$errors, $data] = validate_post_input($_POST, $_FILES, $post);
        if (!$errors) {
            post_repository()->update($id, $data);
            set_flash('success', 'Alterações salvas.');
            redirect(admin_url('posts/edit.php?id=' . $id));
        }
        $post = ['id' => $id] + $data;
    } catch (Throwable $exception) {
        log_error($exception);
        $errors[] = 'Não foi possível salvar as alterações.';
    }
}
$categories = category_repository()->all();
$adminTitle = 'Editar crônica'; $adminSection = 'posts';
require __DIR__ . '/../includes/admin-header.php';
?>
<div class="admin-page-head"><div><p class="admin-kicker">Revisar</p><h1>Editar <em>crônica.</em></h1><p>Última atualização: <?= e(format_date($post['updated_at'] ?? date('Y-m-d'))) ?>.</p></div></div>
<?php require __DIR__ . '/_form.php'; require __DIR__ . '/../includes/admin-footer.php'; ?>
