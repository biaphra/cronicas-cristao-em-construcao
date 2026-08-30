<?php
declare(strict_types=1);

final class PostRepository
{
    private const PUBLIC_CONDITION = "(p.status = 'published' OR (p.status = 'scheduled' AND p.published_at <= :public_now))";

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function published(int $limit = 50, int $offset = 0, ?string $category = null, ?string $search = null): array
    {
        $where = [self::PUBLIC_CONDITION];
        $params = ['public_now' => date('Y-m-d H:i:s')];
        if ($category) {
            $where[] = 'c.name = :category';
            $params['category'] = $category;
        }
        if ($search) {
            $where[] = '(p.title LIKE :search OR p.excerpt LIKE :search OR p.content LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }
        $sql = $this->baseSelect() . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY p.published_at DESC, p.id DESC LIMIT :limit OFFSET :offset';
        $statement = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value);
        }
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();
        return array_map($this->hydrate(...), $statement->fetchAll());
    }

    public function countPublished(?string $category = null, ?string $search = null): int
    {
        $where = [self::PUBLIC_CONDITION];
        $params = ['public_now' => date('Y-m-d H:i:s')];
        if ($category) {
            $where[] = 'c.name = :category';
            $params['category'] = $category;
        }
        if ($search) {
            $where[] = '(p.title LIKE :search OR p.excerpt LIKE :search OR p.content LIKE :search)';
            $params['search'] = '%' . $search . '%';
        }
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM posts p JOIN categories c ON c.id = p.category_id WHERE ' . implode(' AND ', $where));
        $statement->execute($params);
        return (int) $statement->fetchColumn();
    }

    public function featured(): ?array
    {
        $statement = $this->pdo->prepare($this->baseSelect() . ' WHERE ' . self::PUBLIC_CONDITION . ' AND p.featured = 1 ORDER BY p.published_at DESC, p.id DESC LIMIT 1');
        $statement->execute(['public_now' => date('Y-m-d H:i:s')]);
        $row = $statement->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function findPublishedBySlug(string $slug): ?array
    {
        $statement = $this->pdo->prepare($this->baseSelect() . ' WHERE ' . self::PUBLIC_CONDITION . ' AND p.slug = :slug LIMIT 1');
        $statement->execute(['slug' => $slug, 'public_now' => date('Y-m-d H:i:s')]);
        $row = $statement->fetch();
        return $row ? $this->hydrate($row) : null;
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare($this->baseSelect() . ' WHERE p.id = :id LIMIT 1');
        $statement->execute(['id' => $id]);
        $row = $statement->fetch();
        if (!$row) {
            return null;
        }
        $post = $this->hydrate($row);
        $post['tags'] = $this->tagsFor($id);
        return $post;
    }

    public function related(int $postId, int $categoryId, int $limit = 3): array
    {
        $statement = $this->pdo->prepare($this->baseSelect() . ' WHERE ' . self::PUBLIC_CONDITION . ' AND p.id != :id ORDER BY (p.category_id = :category_id) DESC, p.published_at DESC LIMIT :limit');
        $statement->bindValue(':id', $postId, PDO::PARAM_INT);
        $statement->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':public_now', date('Y-m-d H:i:s'));
        $statement->execute();
        return array_map($this->hydrate(...), $statement->fetchAll());
    }

    public function adminList(array $filters = [], int $limit = 50): array
    {
        $where = ['1 = 1'];
        $params = [];
        if (!empty($filters['search'])) {
            $where[] = '(p.title LIKE :search OR p.slug LIKE :search OR p.content LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }
        if (!empty($filters['status'])) {
            $where[] = 'p.status = :status';
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['category_id'])) {
            $where[] = 'p.category_id = :category_id';
            $params['category_id'] = (int) $filters['category_id'];
        }
        if (($filters['featured'] ?? '') !== '') {
            $where[] = 'p.featured = :featured';
            $params['featured'] = (int) $filters['featured'];
        }
        if (!empty($filters['date'])) {
            $where[] = 'DATE(p.published_at) = :date';
            $params['date'] = $filters['date'];
        }
        $statement = $this->pdo->prepare($this->baseSelect() . ' WHERE ' . implode(' AND ', $where) . ' ORDER BY p.updated_at DESC LIMIT :limit');
        foreach ($params as $key => $value) {
            $statement->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        return array_map($this->hydrate(...), $statement->fetchAll());
    }

    public function counts(): array
    {
        $counts = ['total' => 0, 'published' => 0, 'draft' => 0, 'scheduled' => 0, 'archived' => 0];
        foreach ($this->pdo->query('SELECT status, COUNT(*) AS total FROM posts GROUP BY status')->fetchAll() as $row) {
            $counts[$row['status']] = (int) $row['total'];
            $counts['total'] += (int) $row['total'];
        }
        return $counts;
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO posts (title, slug, subtitle, excerpt, content, category_id, featured_image, status, featured, reading_time, meta_title, meta_description, og_image, published_at) VALUES (:title, :slug, :subtitle, :excerpt, :content, :category_id, :featured_image, :status, :featured, :reading_time, :meta_title, :meta_description, :og_image, :published_at)';
        $this->pdo->beginTransaction();
        try {
            if ((int) $data['featured'] === 1) {
                $this->pdo->exec('UPDATE posts SET featured = 0');
            }
            $statement = $this->pdo->prepare($sql);
            $statement->execute($this->postParams($data));
            $id = (int) $this->pdo->lastInsertId();
            $this->syncTags($id, $data['tags'] ?? '');
            $this->pdo->commit();
            return $id;
        } catch (Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    public function update(int $id, array $data): void
    {
        $sql = 'UPDATE posts SET title = :title, slug = :slug, subtitle = :subtitle, excerpt = :excerpt, content = :content, category_id = :category_id, featured_image = :featured_image, status = :status, featured = :featured, reading_time = :reading_time, meta_title = :meta_title, meta_description = :meta_description, og_image = :og_image, published_at = :published_at, updated_at = CURRENT_TIMESTAMP WHERE id = :id';
        $this->pdo->beginTransaction();
        try {
            if ((int) $data['featured'] === 1) {
                $statement = $this->pdo->prepare('UPDATE posts SET featured = 0 WHERE id != :id');
                $statement->execute(['id' => $id]);
            }
            $statement = $this->pdo->prepare($sql);
            $statement->execute(['id' => $id] + $this->postParams($data));
            $this->syncTags($id, $data['tags'] ?? '');
            $this->pdo->commit();
        } catch (Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }

    public function delete(int $id): void
    {
        $statement = $this->pdo->prepare('DELETE FROM posts WHERE id = :id');
        $statement->execute(['id' => $id]);
    }

    public function changeStatus(int $id, string $status): void
    {
        $publishedAt = $status === 'published' ? ', published_at = COALESCE(published_at, CURRENT_TIMESTAMP)' : '';
        $statement = $this->pdo->prepare("UPDATE posts SET status = :status{$publishedAt}, updated_at = CURRENT_TIMESTAMP WHERE id = :id");
        $statement->execute(['status' => $status, 'id' => $id]);
    }

    public function duplicate(int $id): int
    {
        $post = $this->find($id);
        if (!$post) {
            throw new RuntimeException('Crônica não encontrada.');
        }
        $post['title'] .= ' — cópia';
        $post['slug'] = $this->uniqueSlug($post['slug'] . '-copia');
        $post['status'] = 'draft';
        $post['featured'] = 0;
        $post['published_at'] = null;
        $post['tags'] = implode(', ', array_column($post['tags'], 'name'));
        return $this->create($post);
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM posts WHERE slug = :slug' . ($exceptId ? ' AND id != :id' : '');
        $statement = $this->pdo->prepare($sql);
        $params = ['slug' => $slug];
        if ($exceptId) {
            $params['id'] = $exceptId;
        }
        $statement->execute($params);
        return (int) $statement->fetchColumn() > 0;
    }

    public function uniqueSlug(string $base): string
    {
        $slug = $base;
        $counter = 2;
        while ($this->slugExists($slug)) {
            $slug = $base . '-' . $counter++;
        }
        return $slug;
    }

    private function baseSelect(): string
    {
        return 'SELECT p.*, c.name AS category, c.slug AS category_slug FROM posts p JOIN categories c ON c.id = p.category_id';
    }

    private function hydrate(array $row): array
    {
        $row['id'] = (int) $row['id'];
        $row['category_id'] = (int) $row['category_id'];
        $row['featured'] = (int) $row['featured'];
        $row['reading_minutes'] = (int) $row['reading_time'];
        $row['reading_time'] = $row['reading_minutes'] . ' min';
        $row['date'] = $row['published_at'] ?: $row['created_at'];
        return $row;
    }

    private function postParams(array $data): array
    {
        return [
            'title' => $data['title'], 'slug' => $data['slug'], 'subtitle' => $data['subtitle'] ?: null,
            'excerpt' => $data['excerpt'], 'content' => $data['content'], 'category_id' => (int) $data['category_id'],
            'featured_image' => $data['featured_image'] ?: null, 'status' => $data['status'], 'featured' => (int) $data['featured'],
            'reading_time' => (int) $data['reading_time'], 'meta_title' => $data['meta_title'] ?: null,
            'meta_description' => $data['meta_description'] ?: null, 'og_image' => $data['og_image'] ?: null,
            'published_at' => $data['published_at'] ?: null,
        ];
    }

    private function syncTags(int $postId, string $tagList): void
    {
        $this->pdo->prepare('DELETE FROM post_tags WHERE post_id = :id')->execute(['id' => $postId]);
        $names = array_unique(array_filter(array_map('trim', explode(',', $tagList))));
        $insertTag = $this->pdo->prepare('INSERT INTO tags (name, slug) VALUES (:name, :slug) ON CONFLICT DO NOTHING');
        $findTag = $this->pdo->prepare('SELECT id FROM tags WHERE name = :name OR slug = :slug LIMIT 1');
        $attach = $this->pdo->prepare('INSERT OR IGNORE INTO post_tags (post_id, tag_id) VALUES (:post_id, :tag_id)');
        foreach ($names as $name) {
            $insertTag->execute(['name' => $name, 'slug' => slugify($name)]);
            $findTag->execute(['name' => $name, 'slug' => slugify($name)]);
            $attach->execute(['post_id' => $postId, 'tag_id' => (int) $findTag->fetchColumn()]);
        }
    }

    private function tagsFor(int $postId): array
    {
        $statement = $this->pdo->prepare('SELECT t.* FROM tags t JOIN post_tags pt ON pt.tag_id = t.id WHERE pt.post_id = :id ORDER BY t.name');
        $statement->execute(['id' => $postId]);
        return $statement->fetchAll();
    }
}
