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
}
