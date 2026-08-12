<?php
/**
 * Product Model handling polymorphic entries (Clothing, Toys, Food) and Inventory
 */
class Product extends BaseModel {

    /**
     * Retrieve all products with details joined based on type and filters
     */
    public function getAll($filters = []) {
        $sql = "
            SELECT p.*, 
                   t.nome as turma_nome, 
                   l.codigo as lote_codigo,
                   rd.qualidade as roupa_qualidade, rd.faixa_etaria as roupa_faixa_etaria,
                   bd.faixa_etaria as brinquedo_faixa_etaria,
                   ad.categoria as alimento_categoria, ad.qualidade as alimento_qualidade, ad.data_validade as alimento_data_validade
            FROM produtos p
            JOIN turmas t ON p.turma_id = t.id
            JOIN lotes l ON p.lote_id = l.id
            LEFT JOIN roupa_detalhes rd ON rd.produto_id = p.id AND p.tipo = 'roupa'
            LEFT JOIN brinquedo_detalhes bd ON bd.produto_id = p.id AND p.tipo = 'brinquedo'
            LEFT JOIN alimento_detalhes ad ON ad.produto_id = p.id AND p.tipo = 'alimento'
            WHERE 1=1
        ";
        
        $params = [];

        if (!empty($filters['tipo'])) {
            $sql .= " AND p.tipo = :tipo";
            $params['tipo'] = $filters['tipo'];
        }

        if (!empty($filters['turma_id'])) {
            $sql .= " AND p.turma_id = :turma_id";
            $params['turma_id'] = $filters['turma_id'];
        }

        if (!empty($filters['lote_id'])) {
            $sql .= " AND p.lote_id = :lote_id";
            $params['lote_id'] = $filters['lote_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (p.nome LIKE :search OR l.codigo LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        // Validity filters for Food
        if (isset($filters['validade'])) {
            if ($filters['validade'] === 'vencidos') {
                $sql .= " AND p.tipo = 'alimento' AND ad.data_validade < CURRENT_DATE()";
            } elseif ($filters['validade'] === 'proximos') {
                // Near expiration: next 30 days
                $sql .= " AND p.tipo = 'alimento' AND ad.data_validade >= CURRENT_DATE() AND ad.data_validade <= DATE_ADD(CURRENT_DATE(), INTERVAL 30 DAY)";
            }
        }

        $sql .= " ORDER BY p.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get single product with detailed attributes by ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT p.*, 
                   t.nome as turma_nome, 
                   l.codigo as lote_codigo,
                   rd.qualidade as roupa_qualidade, rd.faixa_etaria as roupa_faixa_etaria,
                   bd.faixa_etaria as brinquedo_faixa_etaria,
                   ad.categoria as alimento_categoria, ad.qualidade as alimento_qualidade, ad.data_validade as alimento_data_validade
            FROM produtos p
            JOIN turmas t ON p.turma_id = t.id
            JOIN lotes l ON p.lote_id = l.id
            LEFT JOIN roupa_detalhes rd ON rd.produto_id = p.id AND p.tipo = 'roupa'
            LEFT JOIN brinquedo_detalhes bd ON bd.produto_id = p.id AND p.tipo = 'brinquedo'
            LEFT JOIN alimento_detalhes ad ON ad.produto_id = p.id AND p.tipo = 'alimento'
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create product polymorphically inside transaction
     */
    public function create($data, $userId) {
        $this->db->beginTransaction();
        try {
            // 1. Insert generic product fields
            $stmt = $this->db->prepare("
                INSERT INTO produtos (tipo, nome, quantidade, lote_id, turma_id) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $data['tipo'],
                $data['nome'],
                $data['quantidade'],
                $data['lote_id'],
                $data['turma_id']
            ]);
            $productId = $this->db->lastInsertId();

            // 2. Insert type-specific detail fields
            if ($data['tipo'] === 'roupa') {
                $stmtDetail = $this->db->prepare("
                    INSERT INTO roupa_detalhes (produto_id, qualidade, faixa_etaria) 
                    VALUES (?, ?, ?)
                ");
                $stmtDetail->execute([
                    $productId,
                    $data['qualidade'],
                    $data['faixa_etaria']
                ]);
            } elseif ($data['tipo'] === 'brinquedo') {
                $stmtDetail = $this->db->prepare("
                    INSERT INTO brinquedo_detalhes (produto_id, faixa_etaria) 
                    VALUES (?, ?)
                ");
                $stmtDetail->execute([
                    $productId,
                    $data['faixa_etaria']
                ]);
            } elseif ($data['tipo'] === 'alimento') {
                $stmtDetail = $this->db->prepare("
                    INSERT INTO alimento_detalhes (produto_id, categoria, qualidade, data_validade) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmtDetail->execute([
                    $productId,
                    $data['categoria'],
                    $data['qualidade'],
                    $data['data_validade']
                ]);
            }

            // 3. Log initial movement (Entrada)
            $stmtMove = $this->db->prepare("
                INSERT INTO movimentacoes (tipo, produto_id, quantidade, usuario_id, motivo) 
                VALUES ('Entrada', ?, ?, ?, ?)
            ");
            $stmtMove->execute([
                $productId,
                $data['quantidade'],
                $userId,
                'Cadastro inicial do produto'
            ]);

            // 4. Log to Audit (Historico)
            $lote = (new Batch())->getById($data['lote_id']);
            $loteCode = $lote ? $lote['codigo'] : '';

            $audit = new AuditLog();
            $audit->log($userId, 'Cadastro', "Produto cadastrado: {$data['nome']} ({$data['tipo']}) com quantidade {$data['quantidade']}", $data['nome'], $loteCode, $data['quantidade']);

            $this->db->commit();
            return $productId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Update product details inside transaction
     */
    public function update($id, $data, $userId) {
        $product = $this->getById($id);
        if (!$product) return false;

        $this->db->beginTransaction();
        try {
            // 1. Update basic product table
            $stmt = $this->db->prepare("
                UPDATE produtos 
                SET nome = ?, lote_id = ?, turma_id = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $data['nome'],
                $data['lote_id'],
                $data['turma_id'],
                $id
            ]);

            // 2. Update details depending on type
            if ($product['tipo'] === 'roupa') {
                $stmtDetail = $this->db->prepare("
                    UPDATE roupa_detalhes 
                    SET qualidade = ?, faixa_etaria = ? 
                    WHERE produto_id = ?
                ");
                $stmtDetail->execute([
                    $data['qualidade'],
                    $data['faixa_etaria'],
                    $id
                ]);
            } elseif ($product['tipo'] === 'brinquedo') {
                $stmtDetail = $this->db->prepare("
                    UPDATE brinquedo_detalhes 
                    SET faixa_etaria = ? 
                    WHERE produto_id = ?
                ");
                $stmtDetail->execute([
                    $data['faixa_etaria'],
                    $id
                ]);
            } elseif ($product['tipo'] === 'alimento') {
                $stmtDetail = $this->db->prepare("
                    UPDATE alimento_detalhes 
                    SET categoria = ?, qualidade = ?, data_validade = ? 
                    WHERE produto_id = ?
                ");
                $stmtDetail->execute([
                    $data['categoria'],
                    $data['qualidade'],
                    $data['data_validade'],
                    $id
                ]);
            }

            // 3. Log to Audit (Historico)
            $lote = (new Batch())->getById($data['lote_id']);
            $loteCode = $lote ? $lote['codigo'] : '';

            $audit = new AuditLog();
            $audit->log($userId, 'Alteração', "Produto editado: {$data['nome']} ({$product['tipo']})", $data['nome'], $loteCode, $product['quantidade']);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Perform Stock In/Out movements
     */
    public function adjustStock($id, $type, $qty, $userId, $reason = '') {
        $product = $this->getById($id);
        if (!$product) return false;

        if ($qty <= 0) {
            throw new Exception("A quantidade deve ser maior que zero.");
        }

        $this->db->beginTransaction();
        try {
            $currentQty = (int)$product['quantidade'];
            
            if ($type === 'Saída') {
                if ($currentQty < $qty) {
                    throw new Exception("Estoque insuficiente. Quantidade atual disponível: {$currentQty}.");
                }
                $newQty = $currentQty - $qty;
            } else { // Entrada
                $newQty = $currentQty + $qty;
            }

            // 1. Update product quantity
            $stmt = $this->db->prepare("UPDATE produtos SET quantidade = ? WHERE id = ?");
            $stmt->execute([$newQty, $id]);

            // 2. Log movement
            $stmtMove = $this->db->prepare("
                INSERT INTO movimentacoes (tipo, produto_id, quantidade, usuario_id, motivo) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmtMove->execute([$type, $id, $qty, $userId, $reason]);

            // 3. Log to Audit
            $auditType = ($type === 'Entrada') ? 'Entrada' : 'Saída';
            $audit = new AuditLog();
            $audit->log($userId, $auditType, "Ajuste de estoque ({$type}): {$product['nome']}. Qtd ajustada: {$qty}. Motivo: {$reason}", $product['nome'], $product['lote_codigo'], $qty);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Delete product inside transaction
     */
    public function delete($id, $userId) {
        $product = $this->getById($id);
        if (!$product) return false;

        $this->db->beginTransaction();
        try {
            // Delete product (cascading will delete the details because of foreign keys)
            $stmt = $this->db->prepare("DELETE FROM produtos WHERE id = ?");
            $stmt->execute([$id]);

            // Log to Audit
            $audit = new AuditLog();
            $audit->log($userId, 'Exclusão', "Produto excluído permanentemente: {$product['nome']}", $product['nome'], $product['lote_codigo'], $product['quantidade']);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Basic statistics for Dashboard
     */
    public function getStats($turmaId = null) {
        $params = [];
        $cond = "";
        
        if ($turmaId !== null) {
            $cond = " AND p.turma_id = :turma_id";
            $params['turma_id'] = $turmaId;
        }

        // Total products (registered product records)
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM produtos p WHERE 1=1" . $cond);
        $stmt->execute($params);
        $totalProducts = $stmt->fetchColumn();

        // Total sum of stock (actual items available)
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(p.quantidade), 0) FROM produtos p WHERE 1=1" . $cond);
        $stmt->execute($params);
        $totalStock = $stmt->fetchColumn();

        // Specific category counts
        $stmt = $this->db->prepare("SELECT COALESCE(SUM(p.quantidade), 0) FROM produtos p WHERE p.tipo = 'roupa'" . $cond);
        $stmt->execute($params);
        $totalClothing = $stmt->fetchColumn();

        $stmt = $this->db->prepare("SELECT COALESCE(SUM(p.quantidade), 0) FROM produtos p WHERE p.tipo = 'brinquedo'" . $cond);
        $stmt->execute($params);
        $totalToys = $stmt->fetchColumn();

        $stmt = $this->db->prepare("SELECT COALESCE(SUM(p.quantidade), 0) FROM produtos p WHERE p.tipo = 'alimento'" . $cond);
        $stmt->execute($params);
        $totalFood = $stmt->fetchColumn();

        // Near expiration count
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM produtos p 
            JOIN alimento_detalhes ad ON ad.produto_id = p.id
            WHERE p.tipo = 'alimento' 
              AND ad.data_validade >= CURRENT_DATE() 
              AND ad.data_validade <= DATE_ADD(CURRENT_DATE(), INTERVAL 30 DAY)" . $cond
        );
        $stmt->execute($params);
        $nearExpiration = $stmt->fetchColumn();

        // Expired count
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM produtos p 
            JOIN alimento_detalhes ad ON ad.produto_id = p.id
            WHERE p.tipo = 'alimento' 
              AND ad.data_validade < CURRENT_DATE()" . $cond
        );
        $stmt->execute($params);
        $expired = $stmt->fetchColumn();

        // Total batches registered in system
        $totalBatches = $this->db->query("SELECT COUNT(*) FROM lotes")->fetchColumn();

        return [
            'total_produtos' => $totalProducts,
            'estoque_atual' => $totalStock,
            'total_roupas' => $totalClothing,
            'total_brinquedos' => $totalToys,
            'total_alimentos' => $totalFood,
            'proximos_vencimento' => $nearExpiration,
            'vencidos' => $expired,
            'total_lotes' => $totalBatches
        ];
    }

    /**
     * Get recent product registrations
     */
    public function getRecentRegistrations($limit = 5, $turmaId = null) {
        $sql = "
            SELECT p.*, t.nome as turma_nome, l.codigo as lote_codigo
            FROM produtos p
            JOIN turmas t ON p.turma_id = t.id
            JOIN lotes l ON p.lote_id = l.id
            WHERE 1=1
        ";
        $params = [];
        if ($turmaId !== null) {
            $sql .= " AND p.turma_id = :turma_id";
            $params['turma_id'] = $turmaId;
        }
        $sql .= " ORDER BY p.created_at DESC LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        foreach ($params as $key => $val) {
            $stmt->bindValue(':' . $key, $val);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get recent inventory transactions
     */
    public function getRecentMovements($limit = 5, $turmaId = null) {
        $sql = "
            SELECT m.*, p.nome as produto_nome, p.tipo as produto_tipo, l.codigo as lote_codigo, u.nome as usuario_nome
            FROM movimentacoes m
            JOIN produtos p ON m.produto_id = p.id
            JOIN lotes l ON p.lote_id = l.id
            JOIN usuarios u ON m.usuario_id = u.id
            WHERE 1=1
        ";
        $params = [];
        if ($turmaId !== null) {
            $sql .= " AND p.turma_id = :turma_id";
            $params['turma_id'] = $turmaId;
        }
        $sql .= " ORDER BY m.created_at DESC LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        foreach ($params as $key => $val) {
            $stmt->bindValue(':' . $key, $val);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
