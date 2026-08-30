<?php
declare(strict_types=1);

const SITE_NAME = 'Crônicas de um Cristão em Construção';
const SITE_TAGLINE = 'Fé, humor e vida real.';
const SITE_DESCRIPTION = 'Crônicas sobre fé, tropeços, risadas e a beleza de aprender a caminhar com Deus todos os dias.';
define('SITE_URL', getenv('SITE_URL') ?: 'http://localhost:8000');
const INSTAGRAM_HANDLE = '@cronicas_em_construcao';
const INSTAGRAM_URL = 'https://www.instagram.com/cronicas_em_construcao/';
const SESSION_TIMEOUT = 7200;
const MAX_UPLOAD_BYTES = 5242880;

define('APP_ENV', getenv('APP_ENV') ?: 'local');
define('DATABASE_PATH', getenv('DATABASE_PATH') ?: dirname(__DIR__, 2) . '/blog-storage/database.sqlite');
define('PRIVATE_STORAGE_PATH', getenv('PRIVATE_STORAGE_PATH') ?: dirname(DATABASE_PATH));
define('LOG_PATH', PRIVATE_STORAGE_PATH . '/logs/app.log');
define('POST_UPLOAD_PATH', getenv('POST_UPLOAD_PATH') ?: dirname(__DIR__) . '/uploads/posts');

date_default_timezone_set('America/Sao_Paulo');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('cronicas_session');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

if (APP_ENV === 'local') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
}
