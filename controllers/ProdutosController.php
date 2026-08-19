<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Produto.php';
require_once __DIR__ . '/../models/Turma.php';

class ProdutosController extends Controller {

    public function index(): void {
        Auth::requireLogin();
        $user = Auth::user();
        $perfil = $user['perfil'];

        if (in_array($perfil, ['coleta', 'turma'], true)) {
            $turmaId = $user['turma_id'];
            $produtos = $turmaId ? Produto::getByTurma($turmaId) : [];
        } else {
            $produtos = Produto::getAll();
        }

        $this->render('produtos/index', ['produtos' => $produtos, 'perfil' => $perfil]);
    }

    public function create(): void {
        Auth::requireRole(['admin', 'subadmin', 'coordenador', 'coleta']);
        $user = Auth::user();
        
        if ($user['perfil'] === 'coleta') {
            $turmas = [Turma::getById($user['turma_id'])];
        } else {
            $turmas = Turma::getAll();
        }

        $this->render('produtos/create', ['turmas' => $turmas, 'user' => $user]);
    }

    public function store(): void {
        Auth::requireRole(['admin', 'subadmin', 'coordenador', 'coleta']);
        $user = Auth::user();

        $turmaId = (int)($_POST['turma_id'] ?? 0);
        if ($user['perfil'] === 'coleta') {
            $turmaId = (int)$user['turma_id'];
        }
        $categoria = $_POST['categoria'] ?? '';

        $dados = [
            'turma_id' => $turmaId,
            'usuario_registro_id' => $user['id'],
            'categoria' => $categoria
        ];

        $detalhes = [
            'quantidade' => (int)($_POST['quantidade'] ?? 0),
            'qualidade' => $_POST['qualidade'] ?? 'boa',
            'faixa_etaria' => $_POST['faixa_etaria'] ?? '',
            'tipo_alimento' => $_POST['tipo_alimento'] ?? '',
            'data_validade' => $_POST['data_validade'] ?? null,
            'descricao' => $_POST['descricao'] ?? ''
        ];

        if ($turmaId <= 0 || empty($categoria) || $detalhes['quantidade'] <= 0) {
            $_SESSION['error'] = 'Preencha todos os campos obrigatórios.';
            $this->redirect('/produtos/novo');
        }

        if (Produto::criarLote($dados, $detalhes)) {
            $_SESSION['success'] = 'Lote de produto cadastrado com sucesso!';
            $this->redirect('/produtos');
        } else {
            $_SESSION['error'] = 'Erro ao salvar o produto.';
            $this->redirect('/produtos/novo');
        }
    }
}
