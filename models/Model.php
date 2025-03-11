<?php
namespace Model;

use PDO;

class Model
{
    protected PDO $db;
    protected string $table;

    public function __construct(PDO $db, string $table)
    {
        $this->db = $db;
        $this->table = $table;
    }

    public function findById(int $id, bool $fetchAsObject = true)
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $fetchAsObject ? $stmt->fetch(PDO::FETCH_OBJ) : $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findAll(bool $fetchAsObject = true): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table}");
        return $fetchAsObject ? $stmt->fetchAll(PDO::FETCH_OBJ) : $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $columns, ...$values): ?int
    {
        $columnsStr = implode(', ', $columns);
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $sql = "INSERT INTO {$this->table} ($columnsStr) VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);

        if ($stmt->execute($values)) {
            return (int)$this->db->lastInsertId();
        }
        return null;
    }

    public function update(int $id, array $columns, ...$values): bool
    {
        $setPart = implode(', ', array_map(fn($col) => "$col = ?", $columns));
        $sql = "UPDATE {$this->table} SET $setPart WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([...$values, $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
