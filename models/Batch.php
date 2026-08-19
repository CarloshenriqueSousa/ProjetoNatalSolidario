<?php
/**
 * Batch (Lote) Model handles batch codes, creator credentials, and product aggregations
 */
class Batch extends BaseModel {

    public function getAll() {
        $stmt = $this->db->query("
            SELECT l.*, u.nome as responsavel_nome, 
                   COUNT(p.id) as total_produtos,
                   COALESCE(SUM(p.quantidade), 0) as total_itens
            FROM lotes l
            JOIN usuarios u ON l.usuario_id = u.id
            LEFT JOIN produtos p ON p.lote_id = l.id
            GROUP BY l.id
            ORDER BY l.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT l.*, u.nome as responsavel_nome
            FROM lotes l
            JOIN usuarios u ON l.usuario_id = u.id
            WHERE l.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByCode($code) {
        $stmt = $this->db->prepare("SELECT * FROM lotes WHERE codigo = ?");
        $stmt->execute([$code]);
        return $stmt->fetch();
    }

    public function create($code, $userId) {
        // Validate unique code
        if ($this->getByCode($code)) {
            throw new Exception("O código de lote '{$code}' já existe.");
        }
        
        $stmt = $this->db->prepare("INSERT INTO lotes (codigo, usuario_id) VALUES (?, ?)");
        $stmt->execute([$code, $userId]);
        return $this->db->lastInsertId();
    }

    public function delete($id) {
        // Since database FK constraint has ON DELETE CASCADE for products in the batch,
        // deleting a batch will delete all its products.
        // Wait, is that what we want? The requirements say:
        // "Chaves estrangeiras, Integridade referencial... Editar registros, Exclusão registros."
        // Yes, cascading delete for batch makes sense, or we can check if it has products and restrict it.
        // Let's restrict it if it contains products to prevent accidental data loss, or just cascade.
        // The foreign key constraint we wrote is ON DELETE CASCADE. So it will cascade.
        $stmt = $this->db->prepare("DELETE FROM lotes WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
