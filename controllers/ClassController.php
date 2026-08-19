<?php
/**
 * Class Controller manages school classes accounts and scores configuration
 */
class ClassController extends BaseController {

    public function index() {
        $this->requireRole('admin');

        $classModel = new ClassModel();
        $classes = $classModel->getAll();

        $this->render('classes/index', [
            'title' => 'Gerenciar Turmas - Natal Solidário',
            'classes' => $classes
        ]);
    }

    public function create() {
        $this->requireRole('admin');

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            $nome = trim($_POST['nome'] ?? '');
            $login = trim($_POST['login'] ?? '');
            $senha = $_POST['senha'] ?? '';

            if (empty($nome) || empty($login) || empty($senha)) {
                $error = "Preencha todos os campos.";
            } else {
                try {
                    $classModel = new ClassModel();
                    $classModel->create($nome, $login, $senha);
                    $_SESSION['success'] = "Turma '{$nome}' cadastrada com sucesso!";
                    redirect('classes');
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }

        $this->render('classes/create', [
            'title' => 'Adicionar Turma - Natal Solidário',
            'error' => $error
        ]);
    }

    public function edit() {
        $this->requireRole('admin');

        $id = (int)($_GET['id'] ?? 0);
        $classModel = new ClassModel();
        $class = $classModel->getById($id);

        if (!$class) {
            $_SESSION['error'] = "Turma não encontrada.";
            redirect('classes');
        }

        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            $nome = trim($_POST['nome'] ?? '');
            $login = trim($_POST['login'] ?? '');
            $senha = $_POST['senha'] ?? null; // Optional password reset

            if (empty($nome) || empty($login)) {
                $error = "Nome e Login são obrigatórios.";
            } else {
                try {
                    $classModel->update($id, $nome, $login, $senha);
                    $_SESSION['success'] = "Turma '{$nome}' atualizada com sucesso!";
                    redirect('classes');
                } catch (Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }

        $this->render('classes/edit', [
            'title' => 'Editar Turma - Natal Solidário',
            'class' => $class,
            'error' => $error
        ]);
    }

    public function delete() {
        $this->requireRole('admin');

        $id = (int)($_GET['id'] ?? 0);
        $classModel = new ClassModel();

        if ($classModel->delete($id)) {
            $_SESSION['success'] = "Turma e sua conta associada excluídas com sucesso!";
        } else {
            $_SESSION['error'] = "Não foi possível excluir a turma.";
        }

        redirect('classes');
    }

    /**
     * Manage Points Multipliers Configuration
     */
    public function points() {
        $this->requireRole('admin');

        $classModel = new ClassModel();
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();

            $config = [
                'alimento' => (int)($_POST['alimento'] ?? 5),
                'roupa' => (int)($_POST['roupa'] ?? 10),
                'brinquedo' => (int)($_POST['brinquedo'] ?? 15),
            ];

            if ($config['alimento'] < 0 || $config['roupa'] < 0 || $config['brinquedo'] < 0) {
                $error = "Os pontos não podem ser negativos.";
            } else {
                if ($classModel->updateScoringConfig($config)) {
                    $_SESSION['success'] = "Configuração de pontuação atualizada com sucesso!";
                    redirect('classes/points');
                } else {
                    $error = "Erro ao salvar a configuração de pontuação.";
                }
            }
        }

        $pointsConfig = $classModel->getScoringConfig();

        $this->render('classes/points', [
            'title' => 'Configurar Multiplicadores de Pontuação - Natal Solidário',
            'points' => $pointsConfig,
            'error' => $error
        ]);
    }
}
