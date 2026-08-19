<?php
/**
 * Product Controller manages inventory stock list, polymorphic forms, and adjustments
 */
class ProductController extends BaseController {

    public function index() {
        $this->requireAuth();

        $productModel = new Product();
        $classModel = new ClassModel();
        $batchModel = new Batch();

        $filters = [];
        $isClass = has_role('turma');

        // Enforce classroom filter for class logins
        if ($isClass) {
            $filters['turma_id'] = $_SESSION['user_turma_id'] ?? 0;
        } else {
            // Admin can choose class filter
            if (!empty($_GET['turma_id'])) {
                $filters['turma_id'] = (int)$_GET['turma_id'];
            }
        }

        if (!empty($_GET['tipo'])) {
            $filters['tipo'] = $_GET['tipo'];
        }

        if (!empty($_GET['lote_id'])) {
            $filters['lote_id'] = (int)$_GET['lote_id'];
        }

        if (!empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }

        if (!empty($_GET['validade'])) {
            $filters['validade'] = $_GET['validade'];
        }

        // Get matching products
        $products = $productModel->getAll($filters);
        
        // Lookup helpers for filter controls
        $classes = $classModel->getAll();
        $batches = $batchModel->getAll();

        $this->render('products/index', [
            'title' => 'Gerenciar Estoque - Natal Solidário',
            'products' => $products,
            'classes' => $classes,
            'batches' => $batches,
            'isClass' => $isClass,
            'currentFilters' => $filters
        ]);
    }

    public function create() {
        $this->requireAuth();

        $productModel = new Product();
        $classModel = new ClassModel();
        $batchModel = new Batch();

        $error = null;
        $success = null;
        $isClass = has_role('turma');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            // Format data from post
            $data = [
                'tipo' => $_POST['tipo'] ?? '',
                'nome' => trim($_POST['nome'] ?? ''),
                'quantidade' => (int)($_POST['quantidade'] ?? 0),
                'lote_id' => (int)($_POST['lote_id'] ?? 0),
                'turma_id' => $isClass ? ($_SESSION['user_turma_id'] ?? 0) : (int)($_POST['turma_id'] ?? 0),
                
                // Clothing details
                'qualidade' => $_POST['qualidade'] ?? 'Boa', // Default or from form
                
                // Shared clothing/toy faixas
                'faixa_etaria' => $_POST['faixa_etaria'] ?? '',
                
                // Food details
                'categoria' => $_POST['categoria'] ?? 'Não perecível',
                'data_validade' => $_POST['data_validade'] ?? ''
            ];

            // Validation
            if (empty($data['tipo']) || empty($data['nome']) || $data['quantidade'] <= 0 || empty($data['lote_id']) || empty($data['turma_id'])) {
                $error = "Preencha todos os campos obrigatórios e garanta que a quantidade seja maior que zero.";
            } elseif ($data['tipo'] === 'alimento' && empty($data['data_validade'])) {
                $error = "A data de validade é obrigatória para alimentos.";
            } else {
                try {
                    $productModel->create($data, $_SESSION['user_id']);
                    $_SESSION['success'] = "Produto cadastrado e estoque inicial inserido com sucesso!";
                    redirect('products');
                } catch (Exception $e) {
                    $error = "Erro ao cadastrar produto: " . $e->getMessage();
                }
            }
        }

        $batches = $batchModel->getAll();
        $classes = $classModel->getAll();

        $this->render('products/create', [
            'title' => 'Cadastrar Doação - Natal Solidário',
            'batches' => $batches,
            'classes' => $classes,
            'isClass' => $isClass,
            'error' => $error
        ]);
    }

    public function edit() {
        $this->requireAuth();

        $productModel = new Product();
        $classModel = new ClassModel();
        $batchModel = new Batch();

        $id = (int)($_GET['id'] ?? 0);
        $product = $productModel->getById($id);

        if (!$product) {
            $_SESSION['error'] = "Produto não encontrado.";
            redirect('products');
        }

        $isClass = has_role('turma');

        // Enforce ownership check for class logins
        if ($isClass && $product['turma_id'] != $_SESSION['user_turma_id']) {
            $_SESSION['error'] = "Você não tem permissão para editar produtos de outra turma.";
            redirect('products');
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            $data = [
                'nome' => trim($_POST['nome'] ?? ''),
                'lote_id' => (int)($_POST['lote_id'] ?? 0),
                'turma_id' => $isClass ? ($_SESSION['user_turma_id'] ?? 0) : (int)($_POST['turma_id'] ?? 0),
                
                // Specific properties
                'qualidade' => $_POST['qualidade'] ?? 'Boa',
                'faixa_etaria' => $_POST['faixa_etaria'] ?? '',
                'categoria' => $_POST['categoria'] ?? 'Não perecível',
                'data_validade' => $_POST['data_validade'] ?? ''
            ];

            if (empty($data['nome']) || empty($data['lote_id']) || empty($data['turma_id'])) {
                $error = "Preencha todos os campos obrigatórios.";
            } elseif ($product['tipo'] === 'alimento' && empty($data['data_validade'])) {
                $error = "A data de validade é obrigatória para alimentos.";
            } else {
                try {
                    $productModel->update($id, $data, $_SESSION['user_id']);
                    $_SESSION['success'] = "Produto atualizado com sucesso!";
                    redirect('products');
                } catch (Exception $e) {
                    $error = "Erro ao atualizar produto: " . $e->getMessage();
                }
            }
        }

        $batches = $batchModel->getAll();
        $classes = $classModel->getAll();

        $this->render('products/edit', [
            'title' => 'Editar Produto - Natal Solidário',
            'product' => $product,
            'batches' => $batches,
            'classes' => $classes,
            'isClass' => $isClass,
            'error' => $error
        ]);
    }

    public function stock() {
        $this->requireAuth();

        $productModel = new Product();
        $id = (int)($_GET['id'] ?? 0);
        $product = $productModel->getById($id);

        if (!$product) {
            $_SESSION['error'] = "Produto não encontrado.";
            redirect('products');
        }

        $isClass = has_role('turma');

        // Enforce ownership check for class logins (saida/entrada permissions)
        if ($isClass && $product['turma_id'] != $_SESSION['user_turma_id']) {
            $_SESSION['error'] = "Você não tem permissão para alterar o estoque de outra turma.";
            redirect('products');
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            $tipoMov = $_POST['tipo_movimentacao'] ?? ''; // 'Entrada' or 'Saída'
            $qtd = (int)($_POST['quantidade'] ?? 0);
            $motivo = trim($_POST['motivo'] ?? '');

            if (empty($tipoMov) || $qtd <= 0) {
                $error = "Selecione o tipo de movimentação e defina uma quantidade maior que zero.";
            } else {
                try {
                    $productModel->adjustStock($id, $tipoMov, $qtd, $_SESSION['user_id'], $motivo);
                    $_SESSION['success'] = "Estoque do produto '{$product['nome']}' ajustado com sucesso!";
                    redirect('products');
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }

        $this->render('products/stock', [
            'title' => 'Movimentar Estoque - Natal Solidário',
            'product' => $product,
            'error' => $error
        ]);
    }

    public function delete() {
        $this->requireAuth();
        
        // Admin only functionality (Turmas cannot delete or alter points)
        // Wait, the requirement says: "As turmas NÃO poderão: ... Acessar funcionalidades administrativas... O administrador poderá: ... Excluir registros"
        // Let's enforce admin block for delete!
        $this->requireRole('admin');

        $id = (int)($_GET['id'] ?? 0);
        $productModel = new Product();
        
        try {
            $productModel->delete($id, $_SESSION['user_id']);
            $_SESSION['success'] = "Produto excluído com sucesso!";
        } catch (Exception $e) {
            $_SESSION['error'] = "Erro ao excluir produto: " . $e->getMessage();
        }
        
        redirect('products');
    }
}
