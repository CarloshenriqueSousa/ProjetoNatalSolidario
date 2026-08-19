<?php
/**
 * Class (Turma) Model handles scoring systems, classroom credentials, and rankings
 */
class ClassModel extends BaseModel {

    public function getAll() {
        $stmt = $this->db->query("
            SELECT t.*, u.login, u.nome as usuario_nome 
            FROM turmas t
            JOIN usuarios u ON t.usuario_id = u.id
            ORDER BY t.nome ASC
        ");
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT t.*, u.login, u.nome as usuario_nome
            FROM turmas t
            JOIN usuarios u ON t.usuario_id = u.id
            WHERE t.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByUserId($userId) {
        $stmt = $this->db->prepare("SELECT * FROM turmas WHERE usuario_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function create($nome, $login, $senha) {
        $this->db->beginTransaction();
        try {
            $userModel = new User();
            
            // Check if login already exists
            if ($userModel->getByLogin($login)) {
                throw new Exception("O login '{$login}' já está em uso.");
            }

            // Create user
            $userId = $userModel->create($nome, $login, $senha, 'turma');

            // Create classroom profile
            $stmt = $this->db->prepare("INSERT INTO turmas (usuario_id, nome) VALUES (?, ?)");
            $stmt->execute([$userId, $nome]);
            $classId = $this->db->lastInsertId();

            $this->db->commit();
            return $classId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function update($id, $nome, $login, $senha = null) {
        $class = $this->getById($id);
        if (!$class) return false;

        $this->db->beginTransaction();
        try {
            // Update associated user
            $userModel = new User();
            
            // Check duplicate login
            $existing = $userModel->getByLogin($login);
            if ($existing && $existing['id'] != $class['usuario_id']) {
                throw new Exception("O login '{$login}' já está em uso por outro usuário.");
            }

            $userModel->update($class['usuario_id'], $nome, $login, $senha);

            // Update class name
            $stmt = $this->db->prepare("UPDATE turmas SET nome = ? WHERE id = ?");
            $stmt->execute([$nome, $id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function delete($id) {
        $class = $this->getById($id);
        if (!$class) return false;

        $this->db->beginTransaction();
        try {
            // Delete associated user, cascading will delete the class profile due to foreign key
            $userModel = new User();
            $userModel->delete($class['usuario_id']);
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Calculates class rankings dynamically based on points configuration
     */
    public function getRanking() {
        $stmt = $this->db->query("
            SELECT t.id, t.nome, 
                   COALESCE(SUM(p.quantidade), 0) as total_quantidade,
                   COALESCE(SUM(p.quantidade * pc.pontos_por_unidade), 0) AS total_pontos
            FROM turmas t
            LEFT JOIN produtos p ON p.turma_id = t.id
            LEFT JOIN pontuacao_config pc ON pc.tipo_produto = p.tipo
            GROUP BY t.id, t.nome
            ORDER BY total_pontos DESC, t.nome ASC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get score config multipliers
     */
    public function getScoringConfig() {
        $stmt = $this->db->query("SELECT tipo_produto, pontos_por_unidade FROM pontuacao_config");
        $rows = $stmt->fetchAll();
        
        $config = [];
        foreach ($rows as $row) {
            $config[$row['tipo_produto']] = (int)$row['pontos_por_unidade'];
        }
        
        // Ensure defaults if empty
        $defaults = ['alimento' => 5, 'roupa' => 10, 'brinquedo' => 15];
        return array_merge($defaults, $config);
    }

    /**
     * Set score config multipliers
     */
    public function updateScoringConfig($config) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO pontuacao_config (tipo_produto, pontos_por_unidade) 
                VALUES (?, ?) 
                ON DUPLICATE KEY UPDATE pontos_por_unidade = VALUES(pontos_por_unidade)
            ");
            foreach ($config as $tipo => $pontos) {
                $stmt->execute([$tipo, (int)$pontos]);
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
