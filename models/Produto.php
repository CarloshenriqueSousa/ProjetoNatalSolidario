<?php
require_once __DIR__ . '/../config/database.php';

class Produto {

    public static function getAll(): array {
        $db = Database::getInstance();
        $sql = "SELECT lp.*, t.nome as turma_nome, u.nome as usuario_nome 
                FROM lotes_produtos lp
                JOIN turmas t ON lp.turma_id = t.id
                JOIN usuarios u ON lp.usuario_registro_id = u.id
                ORDER BY lp.id DESC";
        return $db->query($sql)->fetchAll();
    }

    public static function getByTurma(int $turmaId): array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT lp.*, t.nome as turma_nome, u.nome as usuario_nome 
                              FROM lotes_produtos lp
                              JOIN turmas t ON lp.turma_id = t.id
                              JOIN usuarios u ON lp.usuario_registro_id = u.id
                              WHERE lp.turma_id = :turma_id
                              ORDER BY lp.id DESC");
        $stmt->execute([':turma_id' => $turmaId]);
        return $stmt->fetchAll();
    }

    public static function criarLote(array $dados, array $detalhes): bool {
        $db = Database::getInstance();
        $db->beginTransaction();

        try {
            $codigo = 'LOTE-' . strtoupper(substr(uniqid(), -6));
            $stmt = $db->prepare("INSERT INTO lotes_produtos (codigo_lote, turma_id, usuario_registro_id, categoria) 
                                  VALUES (:codigo, :turma_id, :usuario_id, :categoria)");
            $stmt->execute([
                ':codigo' => $codigo,
                ':turma_id' => $dados['turma_id'],
                ':usuario_id' => $dados['usuario_registro_id'],
                ':categoria' => $dados['categoria']
            ]);
            $loteId = (int)$db->lastInsertId();

            if ($dados['categoria'] === 'roupa') {
                $stmtR = $db->prepare("INSERT INTO produtos_roupas (lote_id, quantidade, qualidade, faixa_etaria) VALUES (:lote_id, :qtd, :qual, :faixa)");
                $stmtR->execute([
                    ':lote_id' => $loteId,
                    ':qtd' => $detalhes['quantidade'],
                    ':qual' => $detalhes['qualidade'],
                    ':faixa' => $detalhes['faixa_etaria']
                ]);
            } elseif ($dados['categoria'] === 'brinquedo') {
                $stmtB = $db->prepare("INSERT INTO produtos_brinquedos (lote_id, quantidade, faixa_etaria) VALUES (:lote_id, :qtd, :faixa)");
                $stmtB->execute([
                    ':lote_id' => $loteId,
                    ':qtd' => $detalhes['quantidade'],
                    ':faixa' => $detalhes['faixa_etaria']
                ]);
            } elseif ($dados['categoria'] === 'alimento') {
                $stmtA = $db->prepare("INSERT INTO produtos_alimentos (lote_id, quantidade, tipo_alimento, data_validade, qualidade) VALUES (:lote_id, :qtd, :tipo, :validade, :qual)");
                $stmtA->execute([
                    ':lote_id' => $loteId,
                    ':qtd' => $detalhes['quantidade'],
                    ':tipo' => $detalhes['tipo_alimento'],
                    ':validade' => $detalhes['data_validade'] ?: null,
                    ':qual' => $detalhes['qualidade']
                ]);
            } elseif ($dados['categoria'] === 'higiene') {
                $stmtH = $db->prepare("INSERT INTO produtos_higiene (lote_id, quantidade, descricao) VALUES (:lote_id, :qtd, :desc)");
                $stmtH->execute([
                    ':lote_id' => $loteId,
                    ':qtd' => $detalhes['quantidade'],
                    ':desc' => htmlspecialchars($detalhes['descricao'] ?? '', ENT_QUOTES, 'UTF-8')
                ]);
            }

            // Registrar Movimentação de Estoque (Entrada)
            $stmtM = $db->prepare("INSERT INTO movimentacoes_estoque (lote_id, tipo, quantidade, motivo, usuario_id) VALUES (:lote_id, 'entrada', :qtd, 'Cadastro Inicial', :uid)");
            $stmtM->execute([
                ':lote_id' => $loteId,
                ':qtd' => $detalhes['quantidade'],
                ':uid' => $dados['usuario_registro_id']
            ]);

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            return false;
        }
    }
}
