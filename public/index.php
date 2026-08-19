<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Router.php';
require_once __DIR__ . '/../core/Auth.php';

// Autoload simples para Models
spl_autoload_register(function ($class) {
    $modelFile = __DIR__ . '/../models/' . $class . '.php';
    if (file_exists($modelFile)) {
        require_once $modelFile;
    }
});

$router = new Router();

// Rotas de Autenticação
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');

// Rotas do Dashboard
$router->get('/', 'DashboardController@index');
$router->get('/dashboard', 'DashboardController@index');
$router->get('/api/dashboard', 'DashboardController@apiData');

// Rotas de Rifas
$router->get('/rifas', 'RifasController@index');
$router->get('/rifas/nova', 'RifasController@create');
$router->post('/rifas/salvar', 'RifasController@store');
$router->get('/rifas/prestacao/{id}', 'RifasController@showPrestacao');
$router->post('/rifas/prestacao/salvar', 'RifasController@salvarPrestacao');

// Rotas de Produtos e Coleta
$router->get('/produtos', 'ProdutosController@index');
$router->get('/produtos/novo', 'ProdutosController@create');
$router->post('/produtos/salvar', 'ProdutosController@store');

// Rotas de Financeiro
$router->get('/financeiro', 'FinanceiroController@index');

// Rotas de Famílias
$router->get('/familias', 'FamiliasController@index');
$router->post('/familias/salvar', 'FamiliasController@store');
$router->get('/familias/entregar/{id}', 'FamiliasController@entregar');

// Rotas de Relatórios
$router->get('/relatorios', 'RelatoriosController@index');
$router->get('/relatorios/export/estoque', 'RelatorioExportController@exportEstoque');
$router->get('/relatorios/export/prestacao', 'RelatorioExportController@exportPrestacao');

// Dispatch
$router->dispatch();
