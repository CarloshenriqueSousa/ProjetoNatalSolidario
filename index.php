<?php
/**
 * Natal Solidário — Index Router Entrypoint
 * Único entrypoint do sistema. Roteia via ?route=X.
 */

// 1. Autoload do Composer (dompdf, phpspreadsheet, etc.)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    die("Autoloader do Composer não encontrado. Execute 'composer install'.");
}

// 2. Configurações globais (DB, sessão, BASE_URL)
require_once __DIR__ . '/config/config.php';

// 3. Classe de conexão com banco (PDO Singleton)
require_once __DIR__ . '/config/Database.php';

// 4. Core: Auth (centralizado)
require_once __DIR__ . '/core/Auth.php';

// 5. Helpers globais (url, e, is_logged_in, has_role, redirect, etc.)
require_once __DIR__ . '/config/helpers.php';

// 6. Controller base único
require_once __DIR__ . '/controllers/BaseController.php';

// 7. Autoloader local para Controllers e Models
spl_autoload_register(function (string $class): void {
    $dirs = [
        __DIR__ . '/controllers/',
        __DIR__ . '/models/',
        __DIR__ . '/config/',
        __DIR__ . '/core/',
    ];
    foreach ($dirs as $dir) {
        $file = $dir . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// ─────────────────────────────────────────────────────────────────────────────
// MAPA DE ROTAS
// Formato: 'rota' => ['NomeController', 'metodo']
// ─────────────────────────────────────────────────────────────────────────────
$route = $_GET['route'] ?? 'dashboard';

$routes = [
    // ── Autenticação ────────────────────────────────────────────────────────
    'login'                 => ['AuthController',       'login'],
    'logout'                => ['AuthController',       'logout'],

    // ── Dashboard ───────────────────────────────────────────────────────────
    'dashboard'             => ['DashboardController',  'index'],
    'admin'                 => ['DashboardController',  'admin'],

    // ── Produtos (sistema principal: lotes_produtos) ─────────────────────────
    'products'              => ['ProductController',    'index'],
    'products/create'       => ['ProductController',    'create'],
    'products/edit'         => ['ProductController',    'edit'],
    'products/stock'        => ['ProductController',    'stock'],
    'products/delete'       => ['ProductController',    'delete'],

    // ── Produtos (sistema de coleta por turma) ───────────────────────────────
    'produtos'              => ['ProdutosController',   'index'],
    'produtos/create'       => ['ProdutosController',   'create'],
    'produtos/store'        => ['ProdutosController',   'store'],

    // ── Lotes ────────────────────────────────────────────────────────────────
    'batches'               => ['BatchController',      'index'],
    'batches/create'        => ['BatchController',      'create'],
    'batches/delete'        => ['BatchController',      'delete'],

    // ── Turmas ───────────────────────────────────────────────────────────────
    'classes'               => ['ClassController',      'index'],
    'classes/create'        => ['ClassController',      'create'],
    'classes/edit'          => ['ClassController',      'edit'],
    'classes/delete'        => ['ClassController',      'delete'],
    'classes/points'        => ['ClassController',      'points'],

    // ── Relatórios ───────────────────────────────────────────────────────────
    'reports'               => ['ReportController',     'index'],
    'reports/export'        => ['ReportController',     'export'],

    // ── Relatórios (sistema "B") ──────────────────────────────────────────────
    'relatorios'            => ['RelatoriosController', 'index'],

    // ── Histórico / Auditoria ────────────────────────────────────────────────
    'history'               => ['AuditController',      'index'],

    // ── Rifas ────────────────────────────────────────────────────────────────
    'rifas'                 => ['RifasController',      'index'],
    'rifas/create'          => ['RifasController',      'create'],
    'rifas/store'           => ['RifasController',      'store'],
    'rifas/prestacao'       => ['RifasController',      'showPrestacao'],
    'rifas/prestacao/save'  => ['RifasController',      'salvarPrestacao'],

    // ── Famílias ─────────────────────────────────────────────────────────────
    'familias'              => ['FamiliasController',   'index'],
    'familias/store'        => ['FamiliasController',   'store'],
    'familias/entregar'     => ['FamiliasController',   'entregar'],

    // ── Financeiro ───────────────────────────────────────────────────────────
    'financeiro'            => ['FinanceiroController', 'index'],
];

// ─────────────────────────────────────────────────────────────────────────────
// DISPATCH
// ─────────────────────────────────────────────────────────────────────────────
if (array_key_exists($route, $routes)) {
    [$controllerClass, $method] = $routes[$route];

    // Carrega o arquivo do controller se o autoloader ainda não o fez
    $controllerFile = __DIR__ . '/controllers/' . $controllerClass . '.php';
    if (file_exists($controllerFile) && !class_exists($controllerClass)) {
        require_once $controllerFile;
    }

    if (!class_exists($controllerClass)) {
        http_response_code(500);
        die("Erro: Controller '{$controllerClass}' não encontrado.");
    }

    $controllerInstance = new $controllerClass();

    if (!method_exists($controllerInstance, $method)) {
        http_response_code(500);
        die("Erro: Método '{$method}' não existe em '{$controllerClass}'.");
    }

    $controllerInstance->$method();

} else {
    // 404 — Página não encontrada
    http_response_code(404);

    if (is_logged_in()) {
        $pageTitle = '404 — Página Não Encontrada';
        include __DIR__ . '/includes/header.php';
        echo '
        <div class="card error-card" style="text-align:center; padding:40px; margin:50px auto; max-width:600px;">
            <h1 style="font-size:64px; color:var(--color-danger); margin-bottom:20px;">404</h1>
            <h2 style="margin-bottom:20px;">Página Não Encontrada</h2>
            <p style="color:var(--text-secondary); margin-bottom:30px;">
                A rota <strong>' . e($route) . '</strong> não existe ou foi removida.
            </p>
            <a href="' . url('dashboard') . '" class="btn btn-primary">Voltar ao Painel</a>
        </div>';
        include __DIR__ . '/includes/footer.php';
    } else {
        redirect('login');
    }
}
