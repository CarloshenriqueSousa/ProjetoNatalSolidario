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
        require_once __DIR__ . '/Pontuacao.php';
        return Pontuacao::getRankingCompleto();
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
