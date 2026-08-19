<?php
/**
 * Base Controller providing view rendering, session checks, and CSRF validations
 */
abstract class BaseController {
    
    /**
     * Renders a view template inside the global header/footer structure
     */
    protected function render($view, $data = []) {
        // Extract data keys to variables for the views
        extract($data);
        
        // Define page parameters if not set
        $pageTitle = $data['title'] ?? 'Natal Solidário';

        // Check if template is isolated (like login page, which has its own design)
        $isolatedViews = ['login'];
        
        if (in_array($view, $isolatedViews)) {
            $viewPath = __DIR__ . '/../views/' . $view . '.php';
            if (file_exists($viewPath)) {
                include $viewPath;
            } else {
                die("Erro: View '{$view}' não encontrada em: {$viewPath}");
            }
        } else {
            // Load standard layout with header, sidebar, footer
            include __DIR__ . '/../includes/header.php';
            
            $viewPath = __DIR__ . '/../views/' . $view . '.php';
            if (file_exists($viewPath)) {
                include $viewPath;
            } else {
                die("Erro: View '{$view}' não encontrada em: {$viewPath}");
            }
            
            include __DIR__ . '/../includes/footer.php';
        }
    }

    /**
     * Require authentication session
     */
    protected function requireAuth() {
        if (!is_logged_in()) {
            redirect('login');
        }
    }

    /**
     * Require specific roles ('admin' or 'turma')
     */
    protected function requireRole($roles) {
        $this->requireAuth();
        if (!has_role($roles)) {
            $_SESSION['error'] = "Acesso negado. Você não possui permissão para esta funcionalidade.";
            redirect('dashboard');
        }
    }

    /**
     * Helper to validate CSRF token on POST requests
     */
    protected function validateCSRF() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!verify_csrf_token($token)) {
                die("Erro de segurança: Validação CSRF falhou.");
            }
        }
    }

    /**
     * Helper to send JSON responses
     */
    protected function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
