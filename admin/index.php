<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
redirect(is_admin() ? admin_url('dashboard.php') : admin_url('login.php'));
