<?php
require_once __DIR__ . '/../config/database.php';

class Familia {
    public static function getAll(): array {
        $db = Database::getInstance();
        $sql = "SELECT f.*, u.nome as usuario_entrega_nome 
                FROM familias f
                LEFT JOIN usuarios u ON f.usuario_entrega_id = u.id
                ORDER BY f.id DESC";
        return $db->query($sql)->fetchAll();
    }

    public static function criar(array $dados): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO familias (nome_responsavel, quantidade_membros, quantidade_filhos, endereco, status_entrega) 
                              VALUES (:nome, :membros, :filhos, :end, 'pendente')");
        return $stmt->execute([
            ':nome' => htmlspecialchars($dados['nome_responsavel'], ENT_QUOTES, 'UTF-8'),
            ':membros' => (int)$dados['quantidade_membros'],
            ':filhos' => (int)$dados['quantidade_filhos'],
            ':end' => htmlspecialchars($dados['endereco'], ENT_QUOTES, 'UTF-8')
        ]);
    }

    public static function registrarEntrega(int $id, int $usuarioId): bool {
        $db = Database::getInstance();
        $stmt = $db->prepare("UPDATE familias SET status_entrega = 'entregue', data_entrega = NOW(), usuario_entrega_id = :uid WHERE id = :id");
        return $stmt->execute([':uid' => $usuarioId, ':id' => $id]);
    }
}
