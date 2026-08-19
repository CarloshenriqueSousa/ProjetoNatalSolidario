<?php
/**
 * Pontuacao Model — Motor de Pontuação em Tempo Real
 * 
 * Fórmula: (Rifas vendidas × peso) + (Produtos entregues × peso por categoria) - (Penalidade por atraso)
 * Visível apenas para o Admin.
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Financeiro.php';

class Pontuacao {

    /**
     * Retorna os pesos configuráveis do sistema para pontuação
     */
    public static function getConfiguracaoPesos(): array {
        $configs = Financeiro::getConfiguracoes();

        return [
            'alimento'          => (int)($configs['pontos_alimento_kilo'] ?? 10),
            'roupa'             => (int)($configs['pontos_roupas_lote'] ?? 15),
            'brinquedo'         => (int)($configs['pontos_brinquedo_lote'] ?? 20),
            'higiene'           => (int)($configs['pontos_higiene_lote'] ?? 12),
            'rifa_vendida'      => (int)($configs['pontos_rifa_vendida'] ?? 5),
            'penalidade_atraso' => (int)($configs['penalidade_atraso'] ?? 50),
        ];
    }

    /**
     * Calcula a pontuação detalhada de uma turma específica
     */
    public static function calcularPontuacaoTurma(int $turmaId): array {
        $db = Database::getInstance();
        $pesos = self::getConfiguracaoPesos();

        // 1. Produtos por categoria
        $stmtAli = $db->prepare("SELECT COALESCE(SUM(pa.quantidade), 0) FROM lotes_produtos lp JOIN produtos_alimentos pa ON pa.lote_id = lp.id WHERE lp.turma_id = :tid");
        $stmtAli->execute([':tid' => $turmaId]);
        $totalAlimentos = (int)$stmtAli->fetchColumn();

        $stmtRou = $db->prepare("SELECT COALESCE(SUM(pr.quantidade), 0) FROM lotes_produtos lp JOIN produtos_roupas pr ON pr.lote_id = lp.id WHERE lp.turma_id = :tid");
        $stmtRou->execute([':tid' => $turmaId]);
        $totalRoupas = (int)$stmtRou->fetchColumn();

        $stmtBri = $db->prepare("SELECT COALESCE(SUM(pb.quantidade), 0) FROM lotes_produtos lp JOIN produtos_brinquedos pb ON pb.lote_id = lp.id WHERE lp.turma_id = :tid");
        $stmtBri->execute([':tid' => $turmaId]);
        $totalBrinquedos = (int)$stmtBri->fetchColumn();

        $stmtHig = $db->prepare("SELECT COALESCE(SUM(ph.quantidade), 0) FROM lotes_produtos lp JOIN produtos_higiene ph ON ph.lote_id = lp.id WHERE lp.turma_id = :tid");
        $stmtHig->execute([':tid' => $turmaId]);
        $totalHigiene = (int)$stmtHig->fetchColumn();

        // 2. Rifas vendidas
        $stmtRifas = $db->prepare("SELECT COALESCE(SUM(p.quantidade_vendida), 0) FROM lotes_rifas lr JOIN prestacao_rifas p ON p.lote_rifa_id = lr.id WHERE lr.turma_id = :tid");
        $stmtRifas->execute([':tid' => $turmaId]);
        $totalRifasVendidas = (int)$stmtRifas->fetchColumn();

        // 3. Lotes em atraso (penalidade)
        $stmtAtraso = $db->prepare("SELECT COUNT(*) FROM lotes_rifas WHERE turma_id = :tid AND status IN ('em_atraso', 'com_divergencia')");
        $stmtAtraso->execute([':tid' => $turmaId]);
        $lotesComProblema = (int)$stmtAtraso->fetchColumn();

        // 4. Cálculo final
        $pontosAlimentos = $totalAlimentos * $pesos['alimento'];
        $pontosRoupas = $totalRoupas * $pesos['roupa'];
        $pontosBrinquedos = $totalBrinquedos * $pesos['brinquedo'];
        $pontosHigiene = $totalHigiene * $pesos['higiene'];
        $pontosRifas = $totalRifasVendidas * $pesos['rifa_vendida'];
        $penalidade = $lotesComProblema * $pesos['penalidade_atraso'];

        $pontuacaoTotal = $pontosAlimentos + $pontosRoupas + $pontosBrinquedos + $pontosHigiene + $pontosRifas - $penalidade;
        // Pontuação não pode ser negativa
        $pontuacaoTotal = max(0, $pontuacaoTotal);

        return [
            'turma_id'              => $turmaId,
            'total_alimentos'       => $totalAlimentos,
            'total_roupas'          => $totalRoupas,
            'total_brinquedos'      => $totalBrinquedos,
            'total_higiene'         => $totalHigiene,
            'total_rifas_vendidas'  => $totalRifasVendidas,
            'lotes_com_problema'    => $lotesComProblema,
            'pontos_alimentos'      => $pontosAlimentos,
            'pontos_roupas'         => $pontosRoupas,
            'pontos_brinquedos'     => $pontosBrinquedos,
            'pontos_higiene'        => $pontosHigiene,
            'pontos_rifas'          => $pontosRifas,
            'penalidade'            => $penalidade,
            'pontuacao_total'       => $pontuacaoTotal,
        ];
    }

    /**
     * Retorna o ranking completo de todas as turmas ativas, com breakdown de pontuação
     */
    public static function getRankingCompleto(): array {
        $db = Database::getInstance();
        $pesos = self::getConfiguracaoPesos();

        $sql = "
            SELECT 
                t.id AS turma_id,
                t.nome AS turma_nome,
                t.ano_modulo,
                COALESCE(SUM(pa.quantidade), 0) AS total_alimentos,
                COALESCE(SUM(pr.quantidade), 0) AS total_roupas,
                COALESCE(SUM(pb.quantidade), 0) AS total_brinquedos,
                COALESCE(SUM(ph.quantidade), 0) AS total_higiene,
                COALESCE(rifa_agg.total_vendidas, 0) AS total_rifas_vendidas,
                COALESCE(atraso_agg.lotes_problema, 0) AS lotes_com_problema,
                (
                    (COALESCE(SUM(pa.quantidade), 0) * :peso_ali) +
                    (COALESCE(SUM(pr.quantidade), 0) * :peso_roup) +
                    (COALESCE(SUM(pb.quantidade), 0) * :peso_brinq) +
                    (COALESCE(SUM(ph.quantidade), 0) * :peso_hig) +
                    (COALESCE(rifa_agg.total_vendidas, 0) * :peso_rifa) -
                    (COALESCE(atraso_agg.lotes_problema, 0) * :penalidade)
                ) AS pontuacao_total
            FROM turmas t
            LEFT JOIN lotes_produtos lp ON lp.turma_id = t.id
            LEFT JOIN produtos_alimentos pa ON pa.lote_id = lp.id
            LEFT JOIN produtos_roupas pr ON pr.lote_id = lp.id
            LEFT JOIN produtos_brinquedos pb ON pb.lote_id = lp.id
            LEFT JOIN produtos_higiene ph ON ph.lote_id = lp.id
            LEFT JOIN (
                SELECT lr.turma_id, SUM(prif.quantidade_vendida) AS total_vendidas
                FROM lotes_rifas lr
                JOIN prestacao_rifas prif ON prif.lote_rifa_id = lr.id
                GROUP BY lr.turma_id
            ) rifa_agg ON rifa_agg.turma_id = t.id
            LEFT JOIN (
                SELECT turma_id, COUNT(*) AS lotes_problema
                FROM lotes_rifas
                WHERE status IN ('em_atraso', 'com_divergencia')
                GROUP BY turma_id
            ) atraso_agg ON atraso_agg.turma_id = t.id
            WHERE t.ativo = 1
            GROUP BY t.id, t.nome, t.ano_modulo, rifa_agg.total_vendidas, atraso_agg.lotes_problema
            ORDER BY pontuacao_total DESC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':peso_ali'    => $pesos['alimento'],
            ':peso_roup'   => $pesos['roupa'],
            ':peso_brinq'  => $pesos['brinquedo'],
            ':peso_hig'    => $pesos['higiene'],
            ':peso_rifa'   => $pesos['rifa_vendida'],
            ':penalidade'  => $pesos['penalidade_atraso'],
        ]);

        $results = $stmt->fetchAll();

        // Garantir que pontuação não seja negativa
        foreach ($results as &$row) {
            $row['pontuacao_total'] = max(0, (int)$row['pontuacao_total']);
        }

        return $results;
    }
}
