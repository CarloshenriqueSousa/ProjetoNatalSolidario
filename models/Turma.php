<?php
require_once __DIR__ . '/../config/database.php';

class Turma {
    public static function getAll(): array {
        $db = Database::getInstance();
        return $db->query("SELECT * FROM turmas WHERE ativo = 1 ORDER BY nome ASC")->fetchAll();
    }

    public static function getById(int $id): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM turmas WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }
}
