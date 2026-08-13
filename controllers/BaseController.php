<?php
/**
 * BaseController — Natal Solidário
 * Classe base única para TODOS os Controllers do sistema.
 *
 * Responsabilidades:
 *  - Renderizar views dentro do layout includes/header + includes/footer
 *  - Verificar autenticação e permissões usando Auth e helpers
 *  - Validar CSRF em requisições POST
 *  - Enviar respostas JSON para APIs internas
 */
abstract class BaseController
{
    // ─── Renderização de Views ────────────────────────────────────────────

    /**
     * Renderiza uma view dentro do layout padrão do sistema.
     *
     * Views "isoladas" (ex: login) não carregam o header/footer padrão.
     * Todas as outras são envolvidas pelo includes/header.php e includes/footer.php.
     *
     * @param string $view   Caminho relativo dentro de views/, sem .php
     *                       Ex: 'dashboard/index', 'products/create'
     * @param array  $data   Variáveis disponíveis na view (extract)
     */
    protected function render(string $view, array $data = []): void
    {
        // Extrai todas as variáveis para o escopo da view
        extract($data, EXTR_SKIP);

        // Define $pageTitle se ainda não definido nos dados
        if (!isset($pageTitle)) {
            $pageTitle = $data['title'] ?? APP_NAME;
        }

        // Views que possuem layout próprio e não precisam do header/footer padrão
        $isolatedViews = ['auth/login'];

        $viewPath = __DIR__ . '/../views/' . $view . '.php';

        if (!file_exists($viewPath)) {
            http_response_code(500);
            die("Erro interno: View '<strong>" . e($view) . "</strong>' não encontrada em <code>{$viewPath}</code>.");
        }

        if (in_array($view, $isolatedViews)) {
            // Layout isolado — apenas a view
            include $viewPath;
        } else {
            // Layout padrão
            include __DIR__ . '/../includes/header.php';
            include $viewPath;
            include __DIR__ . '/../includes/footer.php';
        }
    }

    // ─── Autenticação e Permissão ─────────────────────────────────────────

    /**
     * Redireciona para login se não houver sessão ativa.
     */
    protected function requireAuth(): void
    {
        if (!is_logged_in()) {
            redirect('login');
        }
    }

    /**
     * Exige perfil específico; redireciona se não tiver permissão.
     *
     * @param string|string[] $roles
     */
    protected function requireRole($roles): void
    {
        $this->requireAuth();
        if (!has_role($roles)) {
            $_SESSION['error'] = 'Acesso negado. Você não possui permissão para esta área.';
            redirect('dashboard');
        }
    }

    // ─── Segurança ────────────────────────────────────────────────────────

    /**
     * Valida o token CSRF em requisições POST.
     * Encerra com erro se o token for inválido.
     */
    protected function validateCSRF(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!verify_csrf_token($token)) {
                http_response_code(419);
                die("Erro de segurança: token CSRF inválido. <a href='" . url('dashboard') . "'>Voltar</a>");
            }
        }
    }

    // ─── Resposta JSON ────────────────────────────────────────────────────

    /**
     * Envia uma resposta JSON e encerra.
     */
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ─── Redirecionamento ─────────────────────────────────────────────────

    /**
     * Redireciona para uma rota do sistema.
     */
    protected function redirect(string $route): void
    {
        redirect($route);
    }
}
