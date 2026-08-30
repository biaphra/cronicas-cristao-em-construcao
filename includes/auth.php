<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function is_admin(): bool
{
    if (empty($_SESSION['admin_user']['id'])) {
        return false;
    }
    $lastActivity = (int) ($_SESSION['admin_last_activity'] ?? 0);
    if ($lastActivity > 0 && time() - $lastActivity > SESSION_TIMEOUT) {
        logout_admin();
        return false;
    }
    $_SESSION['admin_last_activity'] = time();
    return true;
}

function require_admin(): void
{
    if (!is_admin()) {
        set_flash('error', 'Faça login para acessar o painel.');
        redirect(admin_url('login.php'));
    }
}

function attempt_login(string $email, string $password): bool
{
    $statement = database()->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = :email AND active = 1 LIMIT 1');
    $statement->execute(['email' => mb_strtolower(trim($email))]);
    $user = $statement->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) {
        password_verify($password, '$2y$12$abcdefghijklmnopqrstuuI95CkufPx8BhRXdyQh8.GZ3QBMzW');
        return false;
    }
    session_regenerate_id(true);
    unset($user['password_hash']);
    $_SESSION['admin_user'] = $user;
    $_SESSION['admin_last_activity'] = time();
    return true;
}

function logout_admin(): void
{
    unset($_SESSION['admin_user'], $_SESSION['admin_last_activity']);
    session_regenerate_id(true);
}

function admin_user(): ?array
{
    return is_admin() ? $_SESSION['admin_user'] : null;
}
