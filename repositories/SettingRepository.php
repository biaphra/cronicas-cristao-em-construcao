<?php
declare(strict_types=1);

final class SettingRepository
{
    private ?array $cache = null;

    public function __construct(private readonly PDO $pdo)
    {
    }

    public function all(): array
    {
        if ($this->cache === null) {
            $rows = $this->pdo->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
            $this->cache = array_column($rows, 'setting_value', 'setting_key');
        }
        return $this->cache;
    }

    public function get(string $key, ?string $default = null): ?string
    {
        return $this->all()[$key] ?? $default;
    }

    public function saveMany(array $settings): void
    {
        $statement = $this->pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (:key, :value) ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value');
        $this->pdo->beginTransaction();
        try {
            foreach ($settings as $key => $value) {
                $statement->execute(['key' => $key, 'value' => (string) $value]);
            }
            $this->pdo->commit();
            $this->cache = null;
        } catch (Throwable $exception) {
            $this->pdo->rollBack();
            throw $exception;
        }
    }
}
