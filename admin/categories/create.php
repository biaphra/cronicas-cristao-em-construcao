<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/admin-functions.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect(admin_url('categories/index.php'));
try {
    verify_csrf();
    $name = trim((string) ($_POST['name'] ?? ''));
    $slug = slugify(trim((string) ($_POST['slug'] ?? $name)));
    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) throw new RuntimeException('Informe um nome válido.');
    if (category_repository()->slugExists($slug)) throw new RuntimeException('Já existe uma categoria com este slug.');
    category_repository()->create(['name' => $name, 'slug' => $slug, 'description' => trim((string) ($_POST['description'] ?? ''))]);
    set_flash('success', 'Categoria criada.');
} catch (Throwable $exception) {
    log_error($exception); set_flash('error', $exception instanceof RuntimeException ? $exception->getMessage() : 'Não foi possível criar a categoria.');
}
redirect(admin_url('categories/index.php'));
