<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Financeiro.php';

class FinanceiroController extends Controller {

    public function index(): void {
        Auth::requireLogin();
        // BLOQUEIO ABSOLUTO de Coleta e Turma
        Auth::checkBlockColetaTurma();
        Auth::requireRole(['admin', 'subadmin']);

        $resumo = Financeiro::getResumoDivisaoRecursos();
        $movimentacoes = Financeiro::getMovimentacoes();

        $this->render('financeiro/index', [
            'resumo' => $resumo,
            'movimentacoes' => $movimentacoes
        ]);
    }
}
