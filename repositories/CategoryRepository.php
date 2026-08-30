<?php
declare(strict_types=1);

final class CategoryRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function all(): array
    {
        return $this->pdo->query('SELECT c.*, COUNT(p.id) AS post_count FROM categories c LEFT JOIN posts p ON p.category_id = c.id GROUP BY c.id ORDER BY c.name')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM categories WHERE id = :id');
        $statement->execute(['id' => $id]);
        return $statement->fetch() ?: null;
    }

    public function create(array $data): int
    {
        $statement = $this->pdo->prepare('INSERT INTO categories (name, slug, description) VALUES (:name, :slug, :description)');
        $statement->execute(['name' => $data['name'], 'slug' => $data['slug'], 'description' => $data['description']]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $statement = $this->pdo->prepare('UPDATE categories SET name = :name, slug = :slug, description = :description, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $statement->execute(['id' => $id, 'name' => $data['name'], 'slug' => $data['slug'], 'description' => $data['description']]);
    }

    public function delete(int $id): bool
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM posts WHERE category_id = :id');
        $statement->execute(['id' => $id]);
        if ((int) $statement->fetchColumn() > 0) {
            return false;
        }
        $statement = $this->pdo->prepare('DELETE FROM categories WHERE id = :id');
        return $statement->execute(['id' => $id]);
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM categories WHERE slug = :slug' . ($exceptId ? ' AND id != :id' : '');
        $statement = $this->pdo->prepare($sql);
        $params = ['slug' => $slug];
        if ($exceptId) {
            $params['id'] = $exceptId;
        }
        $statement->execute($params);
        return (int) $statement->fetchColumn() > 0;
    }
}
