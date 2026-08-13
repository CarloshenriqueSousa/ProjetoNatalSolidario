<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Rifa.php';
require_once __DIR__ . '/../models/Turma.php';

class RifasController extends Controller {

    public function index(): void {
        Auth::requireLogin();
        $perfil = Auth::getPerfil();

        if (in_array($perfil, ['admin', 'subadmin', 'coordenador'])) {
            $rifas = Rifa::getAll();
        } else {
            $turmaId = Auth::getTurmaId();
            $rifas = $turmaId ? Rifa::getByTurma($turmaId) : [];
        }

        $this->render('rifas/index', ['rifas' => $rifas, 'perfil' => $perfil]);
    }

    public function create(): void {
        Auth::requireRole(['admin', 'subadmin', 'coordenador']);
        $turmas = Turma::getAll();
        $this->render('rifas/create', ['turmas' => $turmas]);
    }

    public function store(): void {
        Auth::requireRole(['admin', 'subadmin', 'coordenador']);

        $turmaId = (int)($_POST['turma_id'] ?? 0);
        $liderNome = trim(htmlspecialchars($_POST['lider_nome'] ?? '', ENT_QUOTES, 'UTF-8'));
        $qtdEntregue = (int)($_POST['quantidade_entregue'] ?? 0);
        $valorUnitario = (float)($_POST['valor_unitario'] ?? 0.00);
        $dataPrevista = $_POST['data_prevista_prestacao'] ?? null;

        if ($turmaId <= 0 || empty($liderNome) || $qtdEntregue <= 0 || $valorUnitario <= 0) {
            $_SESSION['error'] = 'Preencha todos os campos obrigatórios corretamente.';
            $this->redirect('/rifas/nova');
        }

        $usuarioId = Auth::user()['id'];

        Rifa::criarLote([
            'turma_id' => $turmaId,
            'lider_nome' => $liderNome,
            'quantidade_entregue' => $qtdEntregue,
            'valor_unitario' => $valorUnitario,
            'data_prevista_prestacao' => $dataPrevista,
            'usuario_entrega_id' => $usuarioId
        ]);

        $_SESSION['success'] = 'Lote de rifas registrado com sucesso!';
        $this->redirect('/rifas');
    }

    public function showPrestacao(string $id): void {
        Auth::requireRole(['admin', 'subadmin', 'coordenador']);
        $loteId = (int)$id;
        $rifa = Rifa::getById($loteId);

        if (!$rifa) {
            $_SESSION['error'] = 'Lote de rifas não encontrado.';
            $this->redirect('/rifas');
        }

        $this->render('rifas/prestacao', ['rifa' => $rifa]);
    }

    public function salvarPrestacao(): void {
        Auth::requireRole(['admin', 'subadmin', 'coordenador']);

        $loteId = (int)($_POST['lote_id'] ?? 0);
        $usuarioRecebimentoId = Auth::user()['id'];

        $dados = [
            'quantidade_vendida' => (int)($_POST['quantidade_vendida'] ?? 0),
            'quantidade_devolvida' => (int)($_POST['quantidade_devolvida'] ?? 0),
            'quantidade_perdida' => (int)($_POST['quantidade_perdida'] ?? 0),
            'valor_entregue' => (float)($_POST['valor_entregue'] ?? 0.00),
            'observacoes' => trim($_POST['observacoes'] ?? '')
        ];

        $res = Rifa::processarPrestacao($loteId, $dados, $usuarioRecebimentoId);

        if ($res['success']) {
            if ($res['status'] === 'com_divergencia') {
                $_SESSION['warning'] = "Prestação registrada com DIVERGÊNCIA! Diferença apurada: R$ " . number_format($res['diferenca'], 2, ',', '.');
            } else {
                $_SESSION['success'] = "Prestação de contas concluída e aprovada com sucesso! Lançamento financeiro realizado.";
            }
            $this->redirect('/rifas');
        } else {
            $_SESSION['error'] = $res['error'];
            $this->redirect("/rifas/prestacao/{$loteId}");
        }
    }
}
