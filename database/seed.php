<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../includes/database.php';

$pdo = database();
$schema = file_get_contents(__DIR__ . '/schema.sql');
if ($schema === false) {
    throw new RuntimeException('Não foi possível ler schema.sql.');
}
$pdo->exec($schema);

$categories = [
    ['Fé', 'Sobre acreditar mesmo quando nem tudo faz sentido.'],
    ['Humor', 'Leveza e humanidade no caminho.'],
    ['Vida Real', 'Cotidiano, escolhas e amadurecimento.'],
    ['Reflexões', 'Textos para diminuir o barulho de fora.'],
    ['Relacionamentos', 'Aprender a amar melhor.'],
    ['Espiritualidade', 'Práticas e perguntas da vida com Deus.'],
    ['Em Construção', 'Recomeços e processos.'],
    ['Cotidiano', 'A fé nas pequenas coisas.'],
];
$categoryStatement = $pdo->prepare('INSERT INTO categories (name, slug, description) VALUES (:name, :slug, :description) ON CONFLICT(name) DO UPDATE SET description = excluded.description');
foreach ($categories as [$name, $description]) {
    $categoryStatement->execute(['name' => $name, 'slug' => slugify_local($name), 'description' => $description]);
}

$adminEmail = getenv('ADMIN_EMAIL') ?: 'admin@example.local';
$adminPassword = getenv('ADMIN_PASSWORD');
$temporaryPasswordGenerated = false;
if (!is_string($adminPassword) || $adminPassword === '') {
    if (APP_ENV === 'production') {
        throw new RuntimeException('Defina ADMIN_PASSWORD antes de executar o seed em produção.');
    }
    $adminPassword = rtrim(strtr(base64_encode(random_bytes(24)), '+/', '-_'), '=');
    $temporaryPasswordGenerated = true;
}
if (mb_strlen($adminPassword) < 16) {
    throw new RuntimeException('ADMIN_PASSWORD deve possuir ao menos 16 caracteres.');
}
$userStatement = $pdo->prepare('INSERT INTO users (name, email, password_hash, role, active) VALUES (:name, :email, :password_hash, :role, 1) ON CONFLICT(email) DO UPDATE SET name = excluded.name, password_hash = excluded.password_hash, active = 1');
$passwordAlgorithm = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
$userStatement->execute(['name' => 'Administrador local', 'email' => $adminEmail, 'password_hash' => password_hash($adminPassword, $passwordAlgorithm), 'role' => 'admin']);

$categoryIds = array_column($pdo->query('SELECT id, name FROM categories')->fetchAll(), 'id', 'name');
$legacyPosts = require __DIR__ . '/../data/posts.php';
$postStatement = $pdo->prepare('INSERT INTO posts (title, slug, subtitle, excerpt, content, category_id, status, featured, reading_time, published_at, created_at, updated_at) VALUES (:title, :slug, :subtitle, :excerpt, :content, :category_id, :status, :featured, :reading_time, :published_at, :created_at, :updated_at) ON CONFLICT(slug) DO NOTHING');
foreach ($legacyPosts as $post) {
    $words = str_word_count(strip_tags($post['content']), 0, 'áàâãéêíóôõúçÁÀÂÃÉÊÍÓÔÕÚÇ');
    $publishedAt = $post['date'] . ' 09:00:00';
    $postStatement->execute([
        'title' => $post['title'], 'slug' => $post['slug'], 'subtitle' => $post['subtitle'], 'excerpt' => $post['excerpt'],
        'content' => $post['content'], 'category_id' => $categoryIds[$post['category']], 'status' => 'published',
        'featured' => $post['featured'] ? 1 : 0, 'reading_time' => max(1, (int) ceil($words / 200)),
        'published_at' => $publishedAt, 'created_at' => $publishedAt, 'updated_at' => $publishedAt,
    ]);
}

$defaults = [
    'site_name' => SITE_NAME, 'site_description' => SITE_DESCRIPTION, 'instagram_url' => INSTAGRAM_URL,
    'author_name' => 'Autor das Crônicas', 'author_bio' => 'Eu também estou em construção.',
    'contact_email' => '', 'newsletter_enabled' => '1', 'posts_per_page' => '9',
];
$settingStatement = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value) ON CONFLICT(setting_key) DO NOTHING');
foreach ($defaults as $key => $value) {
    $settingStatement->execute(['key' => $key, 'value' => $value]);
}

echo "Banco inicializado em: " . DATABASE_PATH . PHP_EOL;
echo "Admin local: {$adminEmail}" . PHP_EOL;
echo $temporaryPasswordGenerated ? "Senha local temporária gerada: {$adminPassword}" . PHP_EOL : "Senha definida via ADMIN_PASSWORD." . PHP_EOL;

function slugify_local(string $value): string
{
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
    return trim(strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '-', $ascii)), '-');
}
