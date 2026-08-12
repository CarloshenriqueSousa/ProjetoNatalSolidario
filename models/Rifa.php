<?php
require_once __DIR__ . '/../config/database.php';

class Rifa {

    public static function getAll(): array {
        $db = Database::getInstance();
        $sql = "SELECT r.*, t.nome AS turma_nome, u.nome AS usuario_entrega_nome, p.valor_entregue, p.diferenca
                FROM lotes_rifas r
                JOIN turmas t ON r.turma_id = t.id
                JOIN usuarios u ON r.usuario_entrega_id = u.id
                LEFT JOIN prestacao_rifas p ON p.lote_rifa_id = r.id
                ORDER BY r.id DESC";
        return $db->query($sql)->fetchAll();
    }

    public static function getByTurma(int $turmaId): array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT r.*, t.nome AS turma_nome, u.nome AS usuario_entrega_nome, p.valor_entregue, p.diferenca
                              FROM lotes_rifas r
                              JOIN turmas t ON r.turma_id = t.id
                              JOIN usuarios u ON r.usuario_entrega_id = u.id
                              LEFT JOIN prestacao_rifas p ON p.lote_rifa_id = r.id
                              WHERE r.turma_id = :turma_id
                              ORDER BY r.id DESC");
        $stmt->execute([':turma_id' => $turmaId]);
        return $stmt->fetchAll();
    }

    public static function getById(int $id): ?array {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT r.*, t.nome AS turma_nome, u.nome AS usuario_entrega_nome,
                                     p.quantidade_vendida, p.quantidade_devolvida, p.quantidade_perdida,
                                     p.valor_esperado, p.valor_entregue, p.diferenca, p.observacoes
                              FROM lotes_rifas r
                              JOIN turmas t ON r.turma_id = t.id
                              JOIN usuarios u ON r.usuario_entrega_id = u.id
                              LEFT JOIN prestacao_rifas p ON p.lote_rifa_id = r.id
                              WHERE r.id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $res = $stmt->fetch();
        return $res ?: null;
    }

    public static function criarLote(array $data): int {
        $db = Database::getInstance();
        $stmt = $db->prepare("INSERT INTO lotes_rifas 
            (turma_id, lider_nome, quantidade_entregue, valor_unitario, data_prevista_prestacao, status, usuario_entrega_id)
            VALUES (:turma_id, :lider_nome, :quantidade_entregue, :valor_unitario, :data_prevista_prestacao, 'entregue', :usuario_entrega_id)");
        
        $stmt->execute([
            ':turma_id' => $data['turma_id'],
            ':lider_nome' => $data['lider_nome'],
            ':quantidade_entregue' => $data['quantidade_entregue'],
            ':valor_unitario' => $data['valor_unitario'],
            ':data_prevista_prestacao' => $data['data_prevista_prestacao'] ?: null,
            ':usuario_entrega_id' => $data['usuario_entrega_id']
        ]);

        return (int)$db->lastInsertId();
    }

    public static function processarPrestacao(int $loteId, array $dados, int $usuarioRecebimentoId): array {
        $db = Database::getInstance();
        $lote = self::getById($loteId);

        if (!$lote) {
            return ['success' => false, 'error' => 'Lote de rifas não encontrado.'];
        }

        $qtdEntregue = (int)$lote['quantidade_entregue'];
        $valorUnitario = (float)$lote['valor_unitario'];

        $qtdVendida = (int)$dados['quantidade_vendida'];
        $qtdDevolvida = (int)$dados['quantidade_devolvida'];
        $qtdPerdida = (int)$dados['quantidade_perdida'];
        $valorEntregue = (float)$dados['valor_entregue'];

        // 1. Validação de Quantidades
        $totalConferido = $qtdVendida + $qtdDevolvida + $qtdPerdida;
        if ($totalConferido !== $qtdEntregue) {
            return [
                'success' => false,
                'error' => "A soma das quantidades (Vendidas: {$qtdVendida} + Devolvidas: {$qtdDevolvida} + Perdidas: {$qtdPerdida} = {$totalConferido}) diverge da quantidade entregue ({$qtdEntregue})."
            ];
        }

        // 2. Validação de Valores
        $valorCalculado = $qtdVendida * $valorUnitario;
        $valorEsperadoTotal = $qtdEntregue * $valorUnitario;
        $diferenca = $valorEntregue - $valorCalculado;

        // 3. Definição do Status
        if ($diferenca == 0.00 && $qtdPerdida === 0) {
            $novoStatus = 'prestacao_realizada';
        } else {
            $novoStatus = 'com_divergencia';
        }

        // Início da Transação PDO
        $db->beginTransaction();

        try {
            // Inserir ou atualizar na tabela prestacao_rifas
            $stmt = $db->prepare("INSERT INTO prestacao_rifas 
                (lote_rifa_id, quantidade_vendida, quantidade_devolvida, quantidade_perdida, valor_esperado, valor_entregue, diferenca, usuario_recebimento_id, observacoes)
                VALUES (:lote_id, :q_vend, :q_dev, :q_per, :v_esp, :v_ent, :dif, :u_rec, :obs)
                ON DUPLICATE KEY UPDATE 
                quantidade_vendida = VALUES(quantidade_vendida),
                quantidade_devolvida = VALUES(quantidade_devolvida),
                quantidade_perdida = VALUES(quantidade_perdida),
                valor_esperado = VALUES(valor_esperado),
                valor_entregue = VALUES(valor_entregue),
                diferenca = VALUES(diferenca),
                usuario_recebimento_id = VALUES(usuario_recebimento_id),
                observacoes = VALUES(observacoes)");

            $stmt->execute([
                ':lote_id' => $loteId,
                ':q_vend' => $qtdVendida,
                ':q_dev' => $qtdDevolvida,
                ':q_per' => $qtdPerdida,
                ':v_esp' => $valorCalculado,
                ':v_ent' => $valorEntregue,
                ':dif'   => $diferenca,
                ':u_rec' => $usuarioRecebimentoId,
                ':obs'   => htmlspecialchars($dados['observacoes'] ?? '', ENT_QUOTES, 'UTF-8')
            ]);

            // Atualizar status do lote
            $stmtUp = $db->prepare("UPDATE lotes_rifas SET status = :status WHERE id = :id");
            $stmtUp->execute([':status' => $novoStatus, ':id' => $loteId]);

            // Lançamento Automático no Financeiro
            $origem = "Prestação de Contas Rifa - " . $lote['turma_nome'];
            $stmtFin = $db->prepare("INSERT INTO financeiro_movimentacoes 
                (tipo, origem_destino, descricao, valor, lote_rifa_id, usuario_id)
                VALUES ('entrada', :origem, :desc, :valor, :lote_id, :uid)");

            $stmtFin->execute([
                ':origem'  => $origem,
                ':desc'    => "Arrecadação de Rifa ({$qtdVendida} cartelas vendidas). Diferença apurada: R$ " . number_format($diferenca, 2, ',', '.'),
                ':valor'   => $valorEntregue,
                ':lote_id' => $loteId,
                ':uid'     => $usuarioRecebimentoId
            ]);

            $db->commit();

            return [
                'success' => true,
                'status' => $novoStatus,
                'diferenca' => $diferenca,
                'valor_calculado' => $valorCalculado
            ];

        } catch (Exception $e) {
            $db->rollBack();
            return ['success' => false, 'error' => 'Erro ao registrar prestação de contas: ' . $e->getMessage()];
        }
    }
}
