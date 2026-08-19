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

        // 1. Verificação automática de divergência de quantidades
        $totalConferido = $qtdVendida + $qtdDevolvida + $qtdPerdida;
        $temDivergenciaQtd = ($totalConferido !== $qtdEntregue);

        if ($temDivergenciaQtd) {
            // Divergência encontrada — registrar mas NÃO bloquear
            // O sistema marca automaticamente como 'com_divergencia'
        }

        // 2. Validação de Valores
        $valorCalculado = $qtdVendida * $valorUnitario;
        $valorEsperadoTotal = $qtdEntregue * $valorUnitario;
        $diferenca = $valorEntregue - $valorCalculado;

        // 3. Verificação automática de atraso na data
        $temAtraso = false;
        if (!empty($lote['data_prevista_prestacao'])) {
            $dataPrevista = new DateTime($lote['data_prevista_prestacao']);
            $hoje = new DateTime();
            $temAtraso = ($hoje > $dataPrevista);
        }

        // 4. Definição automática do Status
        if ($temDivergenciaQtd || abs($diferenca) > 0.01 || $qtdPerdida > 0) {
            $novoStatus = 'com_divergencia';
        } elseif ($temAtraso) {
            $novoStatus = 'em_atraso';
        } else {
            $novoStatus = 'prestacao_realizada';
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

            // Montar observações automáticas
            $obsAuto = '';
            if ($temDivergenciaQtd) {
                $obsAuto .= "[DIVERGÊNCIA] Qtd conferida ({$totalConferido}) ≠ Qtd entregue ({$qtdEntregue}). ";
            }
            if ($temAtraso) {
                $obsAuto .= "[ATRASO] Prestação após a data prevista ({$lote['data_prevista_prestacao']}). ";
            }
            $obsUsuario = htmlspecialchars($dados['observacoes'] ?? '', ENT_QUOTES, 'UTF-8');
            $obsCompleta = trim($obsAuto . $obsUsuario);

            $stmt->execute([
                ':lote_id' => $loteId,
                ':q_vend' => $qtdVendida,
                ':q_dev' => $qtdDevolvida,
                ':q_per' => $qtdPerdida,
                ':v_esp' => $valorCalculado,
                ':v_ent' => $valorEntregue,
                ':dif'   => $diferenca,
                ':u_rec' => $usuarioRecebimentoId,
                ':obs'   => $obsCompleta
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
                'valor_calculado' => $valorCalculado,
                'tem_divergencia' => $temDivergenciaQtd,
                'tem_atraso' => $temAtraso
            ];

        } catch (Exception $e) {
            $db->rollBack();
            return ['success' => false, 'error' => 'Erro ao registrar prestação de contas: ' . $e->getMessage()];
        }
    }

    /**
     * Verifica e atualiza automaticamente lotes com data de prestação vencida para 'em_atraso'.
     * Executado a cada acesso ao Dashboard ou lista de Rifas.
     * Retorna a quantidade de lotes atualizados.
     */
    public static function verificarAtrasos(): int {
        $db = Database::getInstance();

        // Lotes que passaram da data prevista e ainda não foram finalizados/cancelados
        $sql = "UPDATE lotes_rifas 
                SET status = 'em_atraso' 
                WHERE data_prevista_prestacao IS NOT NULL 
                  AND data_prevista_prestacao < CURDATE()
                  AND status NOT IN ('prestacao_realizada', 'com_divergencia', 'em_atraso', 'finalizado', 'cancelado')";

        $stmt = $db->exec($sql);
        return (int)$stmt;
    }

    /**
     * Retorna o resumo de status de todos os lotes de rifas (para gráfico do Dashboard)
     */
    public static function getResumoStatus(): array {
        $db = Database::getInstance();
        $sql = "SELECT status, COUNT(*) AS total FROM lotes_rifas GROUP BY status ORDER BY total DESC";
        return $db->query($sql)->fetchAll();
    }

    /**
     * Retorna resumo de vendas por turma (vendidas vs devolvidas vs pendentes)
     */
    public static function getResumoVendasPorTurma(): array {
        $db = Database::getInstance();
        $sql = "
            SELECT 
                t.id AS turma_id,
                t.nome AS turma_nome,
                COALESCE(SUM(lr.quantidade_entregue), 0) AS total_entregue,
                COALESCE(SUM(p.quantidade_vendida), 0) AS total_vendida,
                COALESCE(SUM(p.quantidade_devolvida), 0) AS total_devolvida,
                COALESCE(SUM(p.quantidade_perdida), 0) AS total_perdida,
                COALESCE(SUM(p.valor_entregue), 0) AS valor_total_arrecadado
            FROM turmas t
            LEFT JOIN lotes_rifas lr ON lr.turma_id = t.id
            LEFT JOIN prestacao_rifas p ON p.lote_rifa_id = lr.id
            WHERE t.ativo = 1
            GROUP BY t.id, t.nome
            ORDER BY valor_total_arrecadado DESC
        ";
        return $db->query($sql)->fetchAll();
    }
}

