<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Produto.php';
require_once __DIR__ . '/../models/Rifa.php';
require_once __DIR__ . '/../models/Financeiro.php';
require_once __DIR__ . '/../models/Familia.php';

class RelatoriosController extends Controller {

    public function index(): void {
        Auth::requireLogin();
        Auth::checkBlockColetaTurma();
        Auth::requireRole(['admin', 'subadmin']);

        $data = [
            'ranking' => Financeiro::getRankingTurmas(),
            'resumoFinanceiro' => Financeiro::getResumoDivisaoRecursos(),
            'produtos' => Produto::getAll(),
            'rifas' => Rifa::getAll(),
            'familias' => Familia::getAll()
        ];

        $this->render('relatorios/index', $data);
    }
}
