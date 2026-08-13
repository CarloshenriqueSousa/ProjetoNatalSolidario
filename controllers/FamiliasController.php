<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Familia.php';

class FamiliasController extends Controller {

    public function index(): void {
        Auth::requireLogin();
        Auth::checkBlockColetaTurma();
        Auth::requireRole(['admin', 'subadmin', 'coordenador']);

        $familias = Familia::getAll();
        $this->render('familias/index', ['familias' => $familias]);
    }

    public function store(): void {
        Auth::requireRole(['admin', 'subadmin', 'coordenador']);

        $dados = [
            'nome_responsavel' => trim($_POST['nome_responsavel'] ?? ''),
            'quantidade_membros' => (int)($_POST['quantidade_membros'] ?? 1),
            'quantidade_filhos' => (int)($_POST['quantidade_filhos'] ?? 0),
            'endereco' => trim($_POST['endereco'] ?? '')
        ];

        if (empty($dados['nome_responsavel']) || empty($dados['endereco'])) {
            $_SESSION['error'] = 'Preencha o nome do responsável e o endereço.';
            $this->redirect('/familias');
        }

        if (Familia::criar($dados)) {
            $_SESSION['success'] = 'Família cadastrada com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao cadastrar família.';
        }
        $this->redirect('/familias');
    }

    public function entregar(string $id): void {
        Auth::requireRole(['admin', 'subadmin', 'coordenador']);
        $user = Auth::user();

        if (Familia::registrarEntrega((int)$id, $user['id'])) {
            $_SESSION['success'] = 'Entrega de cesta confirmada para a família!';
        } else {
            $_SESSION['error'] = 'Erro ao registrar entrega.';
        }
        $this->redirect('/familias');
    }
}
