<?php
/**
 * Natal Solidário - Index Router Entrypoint
 */

// 1. Load Composer Dependencies and Configs
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    die("Autoloader do Composer não encontrado. Por favor, rode 'composer install'.");
}

require_once __DIR__ . '/config/config.php';

// 2. Register Custom Autoloader for Local Controllers and Models
spl_autoload_register(function ($class) {
    $dirs = ['config', 'controllers', 'models'];
    foreach ($dirs as $dir) {
        $file = __DIR__ . '/' . $dir . '/' . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// 3. Define Clean Routes Dispatch Map
$route = $_GET['route'] ?? 'dashboard';

$routes = [
    'login'             => ['AuthController', 'login'],
    'logout'            => ['AuthController', 'logout'],
    'dashboard'         => ['DashboardController', 'index'],
    
    'products'          => ['ProductController', 'index'],
    'products/create'   => ['ProductController', 'create'],
    'products/edit'     => ['ProductController', 'edit'],
    'products/stock'    => ['ProductController', 'stock'],
    'products/delete'   => ['ProductController', 'delete'],
    
    'batches'           => ['BatchController', 'index'],
    'batches/create'    => ['BatchController', 'create'],
    'batches/delete'    => ['BatchController', 'delete'],
    
    'classes'           => ['ClassController', 'index'],
    'classes/create'    => ['ClassController', 'create'],
    'classes/edit'      => ['ClassController', 'edit'],
    'classes/delete'    => ['ClassController', 'delete'],
    'classes/points'    => ['ClassController', 'points'],
    
    'reports'                   => ['ReportController', 'index'],
    'reports/export'            => ['ReportController', 'export'],
    'relatorios/export/estoque'  => ['RelatorioExportController', 'exportEstoque'],
    'relatorios/export/prestacao' => ['RelatorioExportController', 'exportPrestacao'],
    'api/dashboard'             => ['DashboardController', 'apiData'],
    
    'history'           => ['AuditController', 'index'],
];

// 4. Resolve Route and Execute Controller Action
if (array_key_exists($route, $routes)) {
    list($controllerClass, $method) = $routes[$route];
    
    if (class_exists($controllerClass)) {
        $controllerInstance = new $controllerClass();
        if (method_exists($controllerInstance, $method)) {
            // Run action
            $controllerInstance->$method();
        } else {
            http_response_code(500);
            die("Erro: Método '{$method}' não existe na classe '{$controllerClass}'.");
        }
    } else {
        http_response_code(500);
        die("Erro: Classe do Controller '{$controllerClass}' não encontrada.");
    }
} else {
    // Page Not Found (404)
    http_response_code(404);
    
    // Check if logged in to show layout, otherwise redirect to login
    if (is_logged_in()) {
        include __DIR__ . '/includes/header.php';
        echo '
        <div class="card error-card" style="text-align: center; padding: 40px; margin: 50px auto; max-width: 600px;">
            <h1 style="font-size: 64px; color: var(--color-danger); margin-bottom: 20px;">404</h1>
            <h2 style="margin-bottom: 20px;">Página Não Encontrada</h2>
            <p style="color: var(--text-secondary); margin-bottom: 30px;">O endereço que você tentou acessar não existe ou foi removido.</p>
            <a href="' . url('dashboard') . '" class="btn btn-primary">Voltar ao Painel</a>
        </div>';
        include __DIR__ . '/includes/footer.php';
    } else {
        redirect('login');
    }
}
