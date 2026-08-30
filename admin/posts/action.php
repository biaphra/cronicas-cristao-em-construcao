<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/admin-functions.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(admin_url('posts/index.php'));
try {
    verify_csrf();
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT) ?: 0;
    $action = (string) ($_POST['action'] ?? '');
    if (!post_repository()->find($id)) throw new RuntimeException('Crônica não encontrada.');
    match ($action) {
        'publish' => post_repository()->changeStatus($id, 'published'),
        'archive' => post_repository()->changeStatus($id, 'archived'),
        'duplicate' => post_repository()->duplicate($id),
        default => throw new RuntimeException('Ação inválida.'),
    };
    set_flash('success', match ($action) { 'publish' => 'Crônica publicada.', 'archive' => 'Crônica arquivada.', default => 'Cópia criada como rascunho.' });
} catch (Throwable $exception) {
    log_error($exception); set_flash('error', $exception instanceof RuntimeException ? $exception->getMessage() : 'Não foi possível concluir a ação.');
}
redirect(admin_url('posts/index.php'));
