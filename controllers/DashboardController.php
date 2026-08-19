<?php
/**
 * DashboardController — Painel principal integrado com dados reais do banco
 * 
 * Consome: Produto, Rifa, Financeiro, Pontuacao
 * Exibe gráficos, indicadores, ranking e pontuação (admin only)
 */
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Financeiro.php';
require_once __DIR__ . '/../models/Produto.php';
require_once __DIR__ . '/../models/Rifa.php';
require_once __DIR__ . '/../models/Pontuacao.php';

class DashboardController extends Controller {

    public function index(): void {
        Auth::requireLogin();
        $user = Auth::user();
        $perfil = $user['perfil'];

        // Atualizar automaticamente lotes de rifas em atraso
        Rifa::verificarAtrasos();

        $data = [
            'user' => $user,
            'perfil' => $perfil,
            'isRestrito' => in_array($perfil, ['coleta', 'turma'], true)
        ];

        if (!$data['isRestrito']) {
            // ═══════════════════════════════════════════
            // ADMIN / SUBADMIN / COORDENADOR — Dashboard Completo
            // ═══════════════════════════════════════════

            // 1. Resumo Financeiro (cards de totais)
            $data['resumoFinanceiro'] = Financeiro::getResumoDivisaoRecursos();

            // 2. Produtos por categoria (gráfico donut)
            $data['resumoCategorias'] = Produto::getResumoPorCategoria();

            // 3. Evolução de doações últimos 30 dias (gráfico de barras temporal)
            $data['evolucaoDoacoes'] = Produto::getEvolucaoDoacoes(30);

            // 4. Status das rifas (gráfico de status)
            $data['resumoStatusRifas'] = Rifa::getResumoStatus();

            // 5. Resumo de vendas de rifas por turma
            $data['vendaRifasTurma'] = Rifa::getResumoVendasPorTurma();

            // 6. Ranking com pontuação completa (Motor de Pontuação)
            $data['ranking'] = Pontuacao::getRankingCompleto();
            $data['pesosPontuacao'] = Pontuacao::getConfiguracaoPesos();

            // 7. Lotes de produtos recentes
            $data['produtos'] = Produto::getAll();

            // 8. Lotes de rifas recentes
            $data['rifas'] = Rifa::getAll();

        } else {
            // ═══════════════════════════════════════════
            // COLETA / TURMA — Dashboard Restrito (só a turma deles)
            // ═══════════════════════════════════════════
            $turmaId = $user['turma_id'];

            $data['resumoCategorias'] = $turmaId ? Produto::getResumoPorCategoria($turmaId) : [];
            $data['evolucaoDoacoes'] = $turmaId ? Produto::getEvolucaoDoacoes(30, $turmaId) : [];
            $data['produtos'] = $turmaId ? Produto::getByTurma($turmaId) : [];
            $data['rifas'] = $turmaId ? Rifa::getByTurma($turmaId) : [];

            // BLOQUEADOS para perfil restrito
            $data['ranking'] = [];
            $data['resumoFinanceiro'] = [];
            $data['resumoStatusRifas'] = [];
            $data['vendaRifasTurma'] = [];
            $data['pesosPontuacao'] = [];
        }

        $this->render('dashboard/index', $data);
    }

    /**
     * API endpoint para atualização via AJAX (retorna JSON)
     */
    public function apiData(): void {
        Auth::requireLogin();
        $user = Auth::user();
        $perfil = $user['perfil'];
        $isRestrito = in_array($perfil, ['coleta', 'turma'], true);

        Rifa::verificarAtrasos();

        $response = [];

        if (!$isRestrito) {
            $response['resumoCategorias'] = Produto::getResumoPorCategoria();
            $response['evolucaoDoacoes'] = Produto::getEvolucaoDoacoes(30);
            $response['resumoStatusRifas'] = Rifa::getResumoStatus();
            $response['ranking'] = Pontuacao::getRankingCompleto();
        } else {
            $turmaId = $user['turma_id'];
            $response['resumoCategorias'] = $turmaId ? Produto::getResumoPorCategoria($turmaId) : [];
            $response['evolucaoDoacoes'] = $turmaId ? Produto::getEvolucaoDoacoes(30, $turmaId) : [];
        }

        $this->json($response);
    }
}
