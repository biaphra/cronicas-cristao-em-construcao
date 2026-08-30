<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/admin-functions.php';
require_admin();
$post = post_form_defaults();
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        [$errors, $post] = validate_post_input($_POST, $_FILES);
        if (!$errors) {
            $id = post_repository()->create($post);
            set_flash('success', 'Crônica criada com sucesso.');
            redirect(admin_url('posts/edit.php?id=' . $id));
        }
    } catch (Throwable $exception) {
        log_error($exception);
        $errors[] = 'Não foi possível salvar a crônica.';
    }
}
$categories = category_repository()->all();
$adminTitle = 'Nova crônica'; $adminSection = 'posts';
require __DIR__ . '/../includes/admin-header.php';
?>
<div class="admin-page-head"><div><p class="admin-kicker">Escrever</p><h1>Nova <em>crônica.</em></h1><p>Rascunhe, revise e escolha quando publicar.</p></div></div>
<?php require __DIR__ . '/_form.php'; require __DIR__ . '/../includes/admin-footer.php'; ?>
