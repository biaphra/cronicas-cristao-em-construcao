<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../repositories/PostRepository.php';
require_once __DIR__ . '/../repositories/CategoryRepository.php';
require_once __DIR__ . '/../repositories/SettingRepository.php';

function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return 'assets/' . ltrim($path, '/');
}

function admin_url(string $path = ''): string
{
    return url('admin/' . ltrim($path, '/'));
}

function current_page(): string
{
    return basename((string) ($_SERVER['SCRIPT_NAME'] ?? 'index.php'));
}

function nav_active(string $page): string
{
    return current_page() === $page ? ' aria-current="page"' : '';
}

function posts(): array
{
    return post_repository()->published();
}

function find_post(string $slug): ?array
{
    return post_repository()->findPublishedBySlug($slug);
}

function featured_post(): ?array
{
    return post_repository()->featured() ?? post_repository()->published(1)[0] ?? null;
}

function format_date(string $date): string
{
    $months = [1 => 'JAN', 'FEV', 'MAR', 'ABR', 'MAI', 'JUN', 'JUL', 'AGO', 'SET', 'OUT', 'NOV', 'DEZ'];
    $value = new DateTimeImmutable($date);
    return $value->format('d') . ' ' . $months[(int) $value->format('n')] . ' ' . $value->format('Y');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_is_valid(?string $token): bool
{
    return is_string($token) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        throw new RuntimeException('Token de segurança inválido. Atualize a página e tente novamente.');
    }
}

function clean_header(string $value): string
{
    return trim(str_replace(["\r", "\n"], '', $value));
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function redirect(string $path): never
{
    header('Location: ' . $path, true, 303);
    exit;
}

function page_meta(array $overrides = []): array
{
    return array_merge([
        'title' => setting('site_name', SITE_NAME),
        'description' => setting('site_description', SITE_DESCRIPTION),
        'canonical' => url(ltrim(current_page(), '/')),
        'type' => 'website',
        'image' => url('assets/img/social-card.svg'),
        'robots' => 'index,follow',
    ], $overrides);
}

function site_name(): string
{
    return setting('site_name', SITE_NAME) ?: SITE_NAME;
}

function post_repository(): PostRepository
{
    static $repository;
    return $repository ??= new PostRepository(database());
}

function category_repository(): CategoryRepository
{
    static $repository;
    return $repository ??= new CategoryRepository(database());
}

function setting_repository(): SettingRepository
{
    static $repository;
    return $repository ??= new SettingRepository(database());
}

function setting(string $key, ?string $default = null): ?string
{
    try {
        return setting_repository()->get($key, $default);
    } catch (PDOException) {
        return $default;
    }
}

function slugify(string $value): string
{
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
    $slug = strtolower((string) preg_replace('/[^a-zA-Z0-9]+/', '-', $ascii));
    return trim($slug, '-') ?: 'cronica';
}

function calculate_reading_time(string $html): int
{
    $text = trim((string) preg_replace('/\s+/', ' ', strip_tags($html)));
    if ($text === '') {
        return 1;
    }
    return max(1, (int) ceil(count(preg_split('/\s+/u', $text) ?: []) / 200));
}

function sanitize_post_html(string $html): string
{
    $allowedTags = ['p', 'h2', 'h3', 'strong', 'em', 'blockquote', 'ul', 'ol', 'li', 'a', 'br', 'hr', 'div', 'span', 'cite'];
    $document = new DOMDocument('1.0', 'UTF-8');
    libxml_use_internal_errors(true);
    $document->loadHTML('<?xml encoding="utf-8" ?><div id="content-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    $root = $document->getElementById('content-root');
    if (!$root) {
        return '';
    }
    $walker = function (DOMNode $node) use (&$walker, $allowedTags): void {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if ($child instanceof DOMElement) {
                if (!in_array(strtolower($child->tagName), $allowedTags, true)) {
                    if (in_array(strtolower($child->tagName), ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math'], true)) {
                        $node->removeChild($child);
                        continue;
                    }
                    while ($child->firstChild) {
                        $node->insertBefore($child->firstChild, $child);
                    }
                    $node->removeChild($child);
                    continue;
                }
                foreach (iterator_to_array($child->attributes) as $attribute) {
                    $name = strtolower($attribute->name);
                    $keep = $name === 'href' && $child->tagName === 'a' || $name === 'class' && in_array($child->tagName, ['p', 'div'], true);
                    if (!$keep) {
                        $child->removeAttribute($attribute->name);
                    }
                }
                if ($child->tagName === 'a') {
                    $href = trim($child->getAttribute('href'));
                    if (!preg_match('~^(https?://|mailto:|/)~i', $href)) {
                        $child->removeAttribute('href');
                    } else {
                        $child->setAttribute('rel', 'noopener noreferrer');
                    }
                }
                if ($child->hasAttribute('class') && !in_array($child->getAttribute('class'), ['lead', 'verse'], true)) {
                    $child->removeAttribute('class');
                }
                $walker($child);
            }
        }
    };
    $walker($root);
    $output = '';
    foreach ($root->childNodes as $child) {
        $output .= $document->saveHTML($child);
    }
    return trim($output);
}

function log_error(Throwable $exception): void
{
    $directory = dirname(LOG_PATH);
    if (!is_dir($directory)) {
        mkdir($directory, 0770, true);
    }
    error_log(sprintf("[%s] %s in %s:%d\n", date(DATE_ATOM), $exception->getMessage(), $exception->getFile(), $exception->getLine()), 3, LOG_PATH);
}

function status_label(string $status): string
{
    return match ($status) {
        'published' => 'Publicado',
        'scheduled' => 'Agendado',
        'archived' => 'Arquivado',
        default => 'Rascunho',
    };
}
