<?php
/**
 * Audit Controller displays logs history for administrators and classrooms
 */
class AuditController extends BaseController {

    public function index() {
        $this->requireAuth();

        $auditModel = new AuditLog();
        $userModel = new User();
        
        $filters = [];
        $isClass = has_role('turma');

        if ($isClass) {
            // Classrooms can only view history related to actions performed by their own user account
            $filters['usuario_id'] = $_SESSION['user_id'];
        } else {
            // Admin can filter by any user
            if (!empty($_GET['usuario_id'])) {
                $filters['usuario_id'] = (int)$_GET['usuario_id'];
            }
        }

        if (!empty($_GET['acao'])) {
            $filters['acao'] = $_GET['acao'];
        }

        if (!empty($_GET['search'])) {
            $filters['search'] = $_GET['search'];
        }

        $logs = $auditModel->getAll($filters);
        $users = $userModel->getAll(); // For admin filter dropdown

        $this->render('history/index', [
            'title' => 'Histórico de Atividades - Natal Solidário',
            'logs' => $logs,
            'users' => $users,
            'isClass' => $isClass,
            'currentFilters' => $filters
        ]);
    }
}
