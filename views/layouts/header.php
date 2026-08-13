<?php
$user = Auth::user();
$perfil = Auth::getPerfil();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="stylesheet" href="/css/dashboard.css">
</head>
<body>
<?php if ($user): ?>
<header class="navbar">
    <div class="container navbar-inner">
        <a href="/dashboard" class="brand">
            <div class="brand-icon">🎄</div>
            <span>Natal Solidário</span>
        </a>

        <ul class="nav-links">
            <li><a href="/dashboard" class="nav-link">Dashboard</a></li>
            <li><a href="/produtos" class="nav-link">Produtos</a></li>
            <li><a href="/rifas" class="nav-link">Rifas</a></li>
            
            <?php if (!in_array($perfil, ['coleta', 'turma'], true)): ?>
                <li><a href="/familias" class="nav-link">Famílias</a></li>
                <li><a href="/financeiro" class="nav-link">Financeiro</a></li>
                <li><a href="/relatorios" class="nav-link">Relatórios</a></li>
            <?php endif; ?>

            <?php if (in_array($perfil, ['admin', 'subadmin'], true)): ?>
                <li><a href="?route=admin" class="nav-link" style="color: var(--color-warning);">⚙ Admin</a></li>
            <?php endif; ?>
        </ul>

        <div class="user-badge">
            <span><?= htmlspecialchars($user['nome'], ENT_QUOTES, 'UTF-8') ?></span>
            <span class="role-tag"><?= htmlspecialchars($perfil, ENT_QUOTES, 'UTF-8') ?></span>
            <a href="/logout" style="margin-left: 10px; color: var(--danger); font-weight: bold;">Sair</a>
        </div>
    </div>
</header>
<?php endif; ?>

<main class="main-content">
    <div class="container">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['warning'])): ?>
            <div class="alert alert-warning"><?= htmlspecialchars($_SESSION['warning'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php unset($_SESSION['warning']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
