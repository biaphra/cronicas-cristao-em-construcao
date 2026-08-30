<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/admin-functions.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(admin_url('posts/index.php'));
try {
    verify_csrf();
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
    post_repository()->delete($id);
    set_flash('success', 'Crônica excluída.');
} catch (Throwable $exception) {
    log_error($exception); set_flash('error', 'Não foi possível excluir a crônica.');
}
redirect(admin_url('posts/index.php'));
