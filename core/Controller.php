<?php

abstract class Controller {
    protected function render(string $view, array $data = []): void {
        extract($data);
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        
        if (file_exists($viewFile)) {
            require_once __DIR__ . '/../views/layouts/header.php';
            require_once $viewFile;
            require_once __DIR__ . '/../views/layouts/footer.php';
        } else {
            http_response_code(404);
            echo "View '{$view}' não encontrada.";
        }
    }

    protected function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }

    protected function redirect(string $url): void {
        header("Location: " . $url);
        exit;
    }
}
