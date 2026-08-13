<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Financeiro.php';
require_once __DIR__ . '/../models/Produto.php';
require_once __DIR__ . '/../models/Rifa.php';

class DashboardController extends Controller {

    public function index(): void {
        Auth::requireLogin();
        $user = Auth::user();
        $perfil = $user['perfil'];

        $data = [
            'user' => $user,
            'perfil' => $perfil,
            'isRestrito' => in_array($perfil, ['coleta', 'turma'], true)
        ];

        if (!$data['isRestrito']) {
            // Admin, Subadmin, Coordenador -> Vê Ranking e estatísticas globais
            $data['ranking'] = Financeiro::getRankingTurmas();
            $data['resumoFinanceiro'] = Financeiro::getResumoDivisaoRecursos();
            $data['produtos'] = Produto::getAll();
            $data['rifas'] = Rifa::getAll();
        } else {
            // Coleta ou Turma -> Apenas dados da SUA turma
            $turmaId = $user['turma_id'];
            $data['produtos'] = $turmaId ? Produto::getByTurma($turmaId) : [];
            $data['rifas'] = $turmaId ? Rifa::getByTurma($turmaId) : [];
            $data['ranking'] = []; // BLOQUEADO
            $data['resumoFinanceiro'] = []; // BLOQUEADO
        }

        $this->render('dashboard/index', $data);
    }

    // ============================================================
    // PAINEL ADMINISTRATIVO — admin()
    // Migrado de admin_home.php (raiz antiga do projeto)
    // ============================================================
    public function admin(): void {
        Auth::requireLogin();
        $user   = Auth::user();
        $perfil = $user['perfil'];

        // ------------------------------------------------------------
        // INDICADORES
        // Substituir null pelos resultados das consultas SQL quando
        // os módulos dos outros integrantes estiverem integrados.
        // ------------------------------------------------------------
        $indicadores = [
            'total_produtos' => null,  // Módulo de produtos
            'alimentos'      => null,  // Módulo de produtos (categoria)
            'brinquedos'     => null,  // Módulo de produtos (categoria)
            'roupas'         => null,  // Módulo de produtos (categoria)
            'higiene'        => null,  // Módulo de produtos (categoria)
            'total_turmas'   => null,  // Módulo de turmas
            'total_lotes'    => null,  // Módulo de estoque
            'estoque_atual'  => null,  // Módulo de estoque
        ];

        // ------------------------------------------------------------
        // CARDS — título, chave e cor de acento
        // ------------------------------------------------------------
        $cards = [
            ['titulo' => 'Total de Produtos', 'chave' => 'total_produtos', 'acento' => '#c0392b'],
            ['titulo' => 'Alimentos',         'chave' => 'alimentos',      'acento' => '#27ae60'],
            ['titulo' => 'Brinquedos',        'chave' => 'brinquedos',     'acento' => '#2980b9'],
            ['titulo' => 'Roupas',            'chave' => 'roupas',         'acento' => '#8e44ad'],
            ['titulo' => 'Higiene',           'chave' => 'higiene',        'acento' => '#e67e22'],
            ['titulo' => 'Turmas',            'chave' => 'total_turmas',   'acento' => '#16a085'],
            ['titulo' => 'Lotes',             'chave' => 'total_lotes',    'acento' => '#7f8c8d'],
            ['titulo' => 'Estoque Atual',     'chave' => 'estoque_atual',  'acento' => '#c0392b'],
        ];

        // ------------------------------------------------------------
        // GRÁFICO — Arrecadação por Categoria
        // Formato esperado: ['Categoria' => quantidade, ...]
        // ------------------------------------------------------------
        $grafico_categorias = [];

        $cores_categorias = [
            'Alimentos'  => '#27ae60',
            'Brinquedos' => '#2980b9',
            'Roupas'     => '#8e44ad',
            'Higiene'    => '#e67e22',
            'Outros'     => '#7f8c8d',
        ];

        // ------------------------------------------------------------
        // GRÁFICO — Arrecadação por Turma
        // Formato esperado: ['Turma' => pontos, ...]
        // ------------------------------------------------------------
        $grafico_turmas = [];

        // ------------------------------------------------------------
        // RANKING DAS TURMAS
        // Formato esperado: [['posicao'=>1,'turma'=>'3ºA','pontos'=>0,'doacoes'=>0], ...]
        // ------------------------------------------------------------
        $ranking_turmas = [];

        // Classes CSS para o pódio (1º, 2º, 3º lugar)
        $classes_podio = [1 => 'rank-ouro', 2 => 'rank-prata', 3 => 'rank-bronze'];

        // ------------------------------------------------------------
        // ALERTAS E PENDÊNCIAS
        // Formato esperado: [['tipo'=>'vencimento','mensagem'=>'...'], ...]
        // ------------------------------------------------------------
        $alertas = [];

        $simbolos_alerta = [
            'vencimento' => '!',
            'pendencia'  => '!',
            'aviso'      => 'i',
        ];

        // ------------------------------------------------------------
        // RELATÓRIOS
        // 'ativo' => false = em desenvolvimento (desabilitado visualmente)
        // ------------------------------------------------------------
        $relatorios = [
            ['titulo' => 'Estoque',   'descricao' => 'Produtos em estoque',              'href' => '?route=reports',        'ativo' => false],
            ['titulo' => 'Doações',   'descricao' => 'Resumo das doações',               'href' => '?route=reports',        'ativo' => false],
            ['titulo' => 'Produtos',  'descricao' => 'Produtos arrecadados',             'href' => '?route=reports',        'ativo' => false],
            ['titulo' => 'Por Turma', 'descricao' => 'Desempenho de cada turma',        'href' => '?route=reports',        'ativo' => false],
            ['titulo' => 'Validade',  'descricao' => 'Produtos próximos ao vencimento', 'href' => '?route=reports',        'ativo' => false],
            ['titulo' => 'Pontuação', 'descricao' => 'Classificação das turmas',        'href' => '?route=classes/points', 'ativo' => false],
            ['titulo' => 'Rifas',     'descricao' => 'Controle de rifas',               'href' => '?route=rifas',          'ativo' => false],
        ];

        // Função auxiliar: formata valor do indicador ou retorna "—"
        if (!function_exists('formato_indicador')) {
            function formato_indicador($valor) {
                if ($valor === null) {
                    return '<span class="admin-card-vazio">&#8212;</span>';
                }
                return '<span class="admin-card-numero">' . number_format((int)$valor, 0, ',', '.') . '</span>';
            }
        }

        $data = [
            'user'               => $user,
            'perfil'             => $perfil,
            'indicadores'        => $indicadores,
            'cards'              => $cards,
            'grafico_categorias' => $grafico_categorias,
            'cores_categorias'   => $cores_categorias,
            'grafico_turmas'     => $grafico_turmas,
            'ranking_turmas'     => $ranking_turmas,
            'classes_podio'      => $classes_podio,
            'alertas'            => $alertas,
            'simbolos_alerta'    => $simbolos_alerta,
            'relatorios'         => $relatorios,
        ];

        $this->render('dashboard/admin', $data);
    }
}
