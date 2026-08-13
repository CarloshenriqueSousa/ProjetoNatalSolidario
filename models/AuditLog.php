<?php
/**
 * Audit Log Model for system alterations history tracking
 */
class AuditLog extends BaseModel {

    /**
     * Write new entry to audit logs
     */
    public function log($userId, $action, $details, $productName = null, $batchCode = null, $quantity = null) {
        $stmt = $this->db->prepare("
            INSERT INTO historico (usuario_id, acao, detalhes, produto_nome, lote_codigo, quantidade) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $userId,
            $action,
            $details,
            $productName,
            $batchCode,
            $quantity
        ]);
    }

    /**
     * Retrieve audit history with filter options
     */
    public function getAll($filters = []) {
        $sql = "
            SELECT h.*, u.nome as usuario_nome, u.tipo as usuario_tipo
            FROM historico h
            JOIN usuarios u ON h.usuario_id = u.id
            WHERE 1=1
        ";
        
        $params = [];

        if (!empty($filters['usuario_id'])) {
            $sql .= " AND h.usuario_id = :usuario_id";
            $params['usuario_id'] = $filters['usuario_id'];
        }

        if (!empty($filters['acao'])) {
            $sql .= " AND h.acao = :acao";
            $params['acao'] = $filters['acao'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (h.detalhes LIKE :search OR h.produto_nome LIKE :search OR h.lote_codigo LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY h.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
