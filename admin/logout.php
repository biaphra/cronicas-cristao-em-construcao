<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        logout_admin();
    } catch (Throwable $exception) {
        log_error($exception);
    }
}
redirect(admin_url('login.php'));
