<?php
/**
 * Global Header layout and navigation drawer
 */
$currentRoute = $_GET['route'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-logo">N</div>
                <h1 class="brand-name">Natal Solidário</h1>
            </div>
            
            <ul class="sidebar-menu">
                <li class="menu-item <?= $currentRoute === 'dashboard' ? 'active' : '' ?>">
                    <a href="<?= url('dashboard') ?>">
                        <!-- SVG icon for Dashboard -->
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
                        <span>Painel Inicial</span>
                    </a>
                </li>
                
                <li class="menu-item <?= strpos($currentRoute, 'products') === 0 ? 'active' : '' ?>">
                    <a href="<?= url('products') ?>">
                        <!-- SVG icon for Products -->
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                        <span>Consultar Estoque</span>
                    </a>
                </li>
                
                <li class="menu-item <?= strpos($currentRoute, 'batches') === 0 ? 'active' : '' ?>">
                    <a href="<?= url('batches') ?>">
                        <!-- SVG icon for Batches -->
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="3" x2="9" y2="21"></line><line x1="15" y1="3" x2="15" y2="21"></line><line x1="3" y1="9" x2="21" y2="9"></line><line x1="3" y1="15" x2="21" y2="15"></line></svg>
                        <span>Lotes Cadastrados</span>
                    </a>
                </li>

                <?php if (has_role('admin')): ?>
                <li class="menu-item <?= strpos($currentRoute, 'classes') === 0 && $currentRoute !== 'classes/points' ? 'active' : '' ?>">
                    <a href="<?= url('classes') ?>">
                        <!-- SVG icon for Classrooms -->
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        <span>Gerenciar Turmas</span>
                    </a>
                </li>
                
                <li class="menu-item <?= $currentRoute === 'classes/points' ? 'active' : '' ?>">
                    <a href="<?= url('classes/points') ?>">
                        <!-- SVG icon for Points settings -->
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>
                        <span>Tabela de Pontos</span>
                    </a>
                </li>
                <?php endif; ?>

                <li class="menu-item <?= $currentRoute === 'reports' ? 'active' : '' ?>">
                    <a href="<?= url('reports') ?>">
                        <!-- SVG icon for Reports -->
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        <span>Gerar Relatórios</span>
                    </a>
                </li>

                <li class="menu-item <?= $currentRoute === 'history' ? 'active' : '' ?>">
                    <a href="<?= url('history') ?>">
                        <!-- SVG icon for History -->
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline><path d="M12 2a10 10 0 0 0-10 10h10z"></path></svg>
                        <span>Histórico de Ações</span>
                    </a>
                </li>
            </ul>

            <div class="sidebar-user">
                <div class="user-info">
                    <span class="user-name"><?= e($_SESSION['user_nome'] ?? 'Convidado') ?></span>
                    <span class="user-role"><?= e($_SESSION['user_tipo'] ?? 'Desconhecido') ?></span>
                </div>
                <a href="<?= url('logout') ?>" class="logout-btn">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    Sair
                </a>
            </div>
        </aside>

        <!-- Page Main Frame -->
        <main class="main-wrapper">
            <!-- Header bar -->
            <header class="top-bar">
                <div>
                    <h2 class="page-title"><?= e($pageTitle) ?></h2>
                    <p class="page-subtitle">Natal Solidário Campaign Management</p>
                </div>
                
                <div style="font-weight: 500; font-size: 14px; color: var(--text-secondary);">
                    <?= date('d/m/Y') ?>
                </div>
            </header>

            <!-- Alerts Messages Container -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <span><?= e($_SESSION['success']) ?></span>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <span><?= e($_SESSION['error']) ?></span>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
