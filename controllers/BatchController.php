<?php
/**
 * Batch Controller handles donations grouping codes
 */
class BatchController extends BaseController {

    public function index() {
        $this->requireAuth();
        
        $batchModel = new Batch();
        $batches = $batchModel->getAll();

        $this->render('batches/index', [
            'title' => 'Gerenciar Lotes - Natal Solidário',
            'batches' => $batches,
            'isAdmin' => has_role('admin')
        ]);
    }

    public function create() {
        // Only administrators can register batches
        $this->requireRole('admin');

        $error = null;
        $batchModel = new Batch();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $codigo = strtoupper(trim($_POST['codigo'] ?? ''));

            if (empty($codigo)) {
                $error = "O código do lote é obrigatório.";
            } else {
                try {
                    $batchModel->create($codigo, $_SESSION['user_id']);
                    $_SESSION['success'] = "Lote '{$codigo}' registrado com sucesso!";
                    redirect('batches');
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }

        $this->render('batches/create', [
            'title' => 'Registrar Lote - Natal Solidário',
            'error' => $error
        ]);
    }

    public function delete() {
        // Only admins can delete batches
        $this->requireRole('admin');

        $id = (int)($_GET['id'] ?? 0);
        $batchModel = new Batch();

        try {
            $batchModel->delete($id);
            $_SESSION['success'] = "Lote excluído com sucesso!";
        } catch (Exception $e) {
            $_SESSION['error'] = "Erro ao excluir lote: " . $e->getMessage();
        }

        redirect('batches');
    }
}
