<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/admin-functions.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(admin_url('categories/index.php'));
try {
    verify_csrf(); $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
    if (!category_repository()->delete($id)) throw new RuntimeException('A categoria possui crônicas vinculadas e não pode ser excluída.');
    set_flash('success', 'Categoria excluída.');
} catch (Throwable $exception) { log_error($exception); set_flash('error', $exception instanceof RuntimeException ? $exception->getMessage() : 'Não foi possível excluir.'); }
redirect(admin_url('categories/index.php'));
