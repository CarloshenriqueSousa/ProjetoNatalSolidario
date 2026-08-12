<?php
require_once __DIR__ . '/../config/database.php';

class Financeiro {

    public static function getConfiguracoes(): array {
        $db = Database::getInstance();
        $rows = $db->query("SELECT chave, valor FROM configuracoes_sistema")->fetchAll();
        $configs = [];
        foreach ($rows as $row) {
            $configs[$row['chave']] = $row['valor'];
        }
        return $configs;
    }

    public static function getResumoDivisaoRecursos(): array {
        $db = Database::getInstance();
        $configs = self::getConfiguracoes();

        $pctEscola = (float)($configs['percentual_escola'] ?? 50);
        $pctTurma = (float)($configs['percentual_turma'] ?? 50);

        // Total vindo de prestação de rifas
        $stmtRifas = $db->query("SELECT COALESCE(SUM(valor_entregue), 0) FROM prestacao_rifas");
        $totalRifas = (float)$stmtRifas->fetchColumn();

        // Total vindo de patrocinadores
        $stmtPatroc = $db->query("SELECT COALESCE(SUM(valor), 0) FROM patrocinadores WHERE ativo = 1 AND tipo = 'dinheiro'");
        $totalPatrocinadores = (float)$stmtPatroc->fetchColumn();

        $totalGeral = $totalRifas + $totalPatrocinadores;

        $valorEscola = $totalRifas * ($pctEscola / 100.0);
        $valorTurmas = $totalRifas * ($pctTurma / 100.0);

        return [
            'total_rifas' => $totalRifas,
            'total_patrocinadores' => $totalPatrocinadores,
            'total_geral' => $totalGeral,
            'percentual_escola' => $pctEscola,
            'percentual_turma' => $pctTurma,
            'valor_escola' => $valorEscola,
            'valor_turmas' => $valorTurmas
        ];
    }

    public static function getRankingTurmas(): array {
        $db = Database::getInstance();
        $configs = self::getConfiguracoes();

        $pesoAlimento = (int)($configs['pontos_alimento_kilo'] ?? 10);
        $pesoRoupa = (int)($configs['pontos_roupas_lote'] ?? 15);
        $pesoBrinquedo = (int)($configs['pontos_brinquedo_lote'] ?? 20);
        $pesoRifa = (int)($configs['pontos_rifa_vendida'] ?? 5);

        // Query SQL consolidada por turma usando GROUP BY e agregadores SUM
        $sql = "
            SELECT 
                t.id AS turma_id,
                t.nome AS turma_nome,
                COALESCE(SUM(pa.quantidade), 0) AS total_alimentos,
                COALESCE(SUM(pr.quantidade), 0) AS total_roupas,
                COALESCE(SUM(pb.quantidade), 0) AS total_brinquedos,
                COALESCE(SUM(prif.quantidade_vendida), 0) AS total_rifas_vendidas,
                (
                    (COALESCE(SUM(pa.quantidade), 0) * :peso_ali) +
                    (COALESCE(SUM(pr.quantidade), 0) * :peso_roup) +
                    (COALESCE(SUM(pb.quantidade), 0) * :peso_brinq) +
                    (COALESCE(SUM(prif.quantidade_vendida), 0) * :peso_rifa)
                ) AS pontuacao_total
            FROM turmas t
            LEFT JOIN lotes_produtos lp ON lp.turma_id = t.id
            LEFT JOIN produtos_alimentos pa ON pa.lote_id = lp.id
            LEFT JOIN produtos_roupas pr ON pr.lote_id = lp.id
            LEFT JOIN produtos_brinquedos pb ON pb.lote_id = lp.id
            LEFT JOIN lotes_rifas lr ON lr.turma_id = t.id
            LEFT JOIN prestacao_rifas prif ON prif.lote_rifa_id = lr.id
            WHERE t.ativo = 1
            GROUP BY t.id, t.nome
            ORDER BY pontuacao_total DESC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':peso_ali' => $pesoAlimento,
            ':peso_roup' => $pesoRoupa,
            ':peso_brinq' => $pesoBrinquedo,
            ':peso_rifa' => $pesoRifa
        ]);

        return $stmt->fetchAll();
    }

    public static function getMovimentacoes(): array {
        $db = Database::getInstance();
        $sql = "SELECT fm.*, u.nome as usuario_nome
                FROM financeiro_movimentacoes fm
                JOIN usuarios u ON fm.usuario_id = u.id
                ORDER BY fm.id DESC";
        return $db->query($sql)->fetchAll();
    }
}
