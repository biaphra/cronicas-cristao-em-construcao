<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';

function validate_post_input(array $input, array $files, ?array $existing = null): array
{
    $errors = [];
    $title = trim((string) ($input['title'] ?? ''));
    $slug = slugify(trim((string) ($input['slug'] ?? $title)));
    $content = sanitize_post_html((string) ($input['content'] ?? ''));
    $excerpt = trim((string) ($input['excerpt'] ?? ''));
    $categoryId = filter_var($input['category_id'] ?? null, FILTER_VALIDATE_INT);
    $status = (string) ($input['status'] ?? 'draft');
    $allowedStatuses = ['draft', 'scheduled', 'published', 'archived'];
    $publishedAt = normalize_datetime((string) ($input['published_at'] ?? ''));

    if (mb_strlen($title) < 3 || mb_strlen($title) > 220) {
        $errors[] = 'O título deve ter entre 3 e 220 caracteres.';
    }
    if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
        $errors[] = 'O slug informado é inválido.';
    }
    if (post_repository()->slugExists($slug, $existing['id'] ?? null)) {
        $errors[] = 'Já existe uma crônica com este slug.';
    }
    if (!$categoryId || !category_repository()->find((int) $categoryId)) {
        $errors[] = 'Selecione uma categoria válida.';
    }
    if (!in_array($status, $allowedStatuses, true)) {
        $errors[] = 'Selecione um status válido.';
    }
    if (mb_strlen(trim(strip_tags($content))) < 20) {
        $errors[] = 'O conteúdo precisa ter ao menos 20 caracteres.';
    }
    if (mb_strlen($excerpt) < 10 || mb_strlen($excerpt) > 500) {
        $errors[] = 'O resumo deve ter entre 10 e 500 caracteres.';
    }
    if ($status === 'scheduled' && !$publishedAt) {
        $errors[] = 'Informe a data de publicação para agendar.';
    }
    if ($status === 'published' && !$publishedAt) {
        $publishedAt = date('Y-m-d H:i:s');
    }

    $featuredImage = $existing['featured_image'] ?? '';
    if (!empty($files['featured_image']['name'])) {
        try {
            $featuredImage = store_post_image($files['featured_image']);
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }
    }
    $ogImage = trim((string) ($input['og_image'] ?? ''));
    if ($ogImage !== '' && !filter_var($ogImage, FILTER_VALIDATE_URL) && !str_starts_with($ogImage, 'uploads/')) {
        $errors[] = 'A imagem Open Graph deve ser uma URL válida ou um caminho de upload.';
    }

    return [$errors, [
        'title' => $title, 'slug' => $slug, 'subtitle' => trim((string) ($input['subtitle'] ?? '')),
        'excerpt' => $excerpt, 'content' => $content, 'category_id' => (int) $categoryId,
        'tags' => trim((string) ($input['tags'] ?? '')), 'status' => $status,
        'published_at' => $publishedAt, 'featured' => isset($input['featured']) ? 1 : 0,
        'reading_time' => calculate_reading_time($content), 'featured_image' => $featuredImage,
        'meta_title' => mb_substr(trim((string) ($input['meta_title'] ?? '')), 0, 220),
        'meta_description' => mb_substr(trim((string) ($input['meta_description'] ?? '')), 0, 320),
        'og_image' => $ogImage,
    ]];
}

function normalize_datetime(string $value): ?string
{
    if ($value === '') {
        return null;
    }
    $date = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $value) ?: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value);
    return $date ? $date->format('Y-m-d H:i:s') : null;
}

function datetime_input(?string $value): string
{
    return $value ? (new DateTimeImmutable($value))->format('Y-m-d\TH:i') : '';
}

function admin_format_date(?string $value): string
{
    if (!$value) {
        return 'Sem data';
    }
    $months = [1 => 'jan.', 'fev.', 'mar.', 'abr.', 'mai.', 'jun.', 'jul.', 'ago.', 'set.', 'out.', 'nov.', 'dez.'];
    $date = new DateTimeImmutable($value);
    return $date->format('d') . ' ' . $months[(int) $date->format('n')] . ' ' . $date->format('Y');
}

function store_post_image(array $file): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Não foi possível receber a imagem.');
    }
    if (($file['size'] ?? 0) > MAX_UPLOAD_BYTES) {
        throw new RuntimeException('A imagem deve ter no máximo 5 MB.');
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Upload de imagem inválido.');
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($types[$mime]) || @getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('Use uma imagem JPG, PNG ou WebP válida.');
    }
    $directory = POST_UPLOAD_PATH;
    if (!is_dir($directory) && !mkdir($directory, 0770, true) && !is_dir($directory)) {
        throw new RuntimeException('Não foi possível preparar a pasta de uploads.');
    }
    $filename = bin2hex(random_bytes(16)) . '.' . $types[$mime];
    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
        throw new RuntimeException('Não foi possível salvar a imagem.');
    }
    return 'uploads/posts/' . $filename;
}

function post_form_defaults(?array $post = null): array
{
    return $post ?? [
        'id' => null, 'title' => '', 'slug' => '', 'subtitle' => '', 'excerpt' => '', 'content' => '<p class="lead"></p>',
        'category_id' => '', 'tags' => [], 'status' => 'draft', 'published_at' => null, 'featured' => 0,
        'featured_image' => '', 'meta_title' => '', 'meta_description' => '', 'og_image' => '',
    ];
}
