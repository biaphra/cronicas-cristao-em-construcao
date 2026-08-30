<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/admin-functions.php';
require_admin();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT) ?: 0;
if (!post_repository()->find($id)) { set_flash('error', 'Crônica não encontrada.'); redirect(admin_url('posts/index.php')); }
redirect(url('cronica.php?preview=' . $id));
