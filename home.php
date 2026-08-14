<?php
require 'conexao.php';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Natal Solidário JMF - Agenda de Doações</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<header>
    <div class="titulo">
        <h1>Natal Solidário JMF</h1>
        <p>Agenda de Doações</p>
    </div>

    <button class="menu-btn" onclick="toggleSidebar()">☰</button>
</header>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h2>Menu</h2>
        <button class="sidebar-close" onclick="closeSidebar()">✕</button>
    </div>
    <nav class="sidebar-nav">
        <a href="home.php">Registrar doações</a>
        <a href="listar_doacoes.php">Lista de doações</a>
        <a href="ranking.php">Ranking de melhores doadores</a>
        <a href="grafico.php">Gráfico de doações</a>
    </nav>
</aside>

<script>
function toggleSidebar() {
    document.body.classList.toggle('sidebar-open');
}

function closeSidebar() {
    document.body.classList.remove('sidebar-open');
}

// Fecha com Esc
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeSidebar();
    }
});
</script>


<script>
function toggleMenu() {
    const menu = document.getElementById('menu');
    if (menu.style.display === 'block') {
        menu.style.display = 'none';
    } else {
        menu.style.display = 'block';
    }
}

// Fecha o menu se clicar fora
document.addEventListener('click', function(event) {
    const menu = document.getElementById('menu');
    const button = document.querySelector('.menu-btn');
    if (!menu.contains(event.target) && !button.contains(event.target)) {
        menu.style.display = 'none';
    }
});
</script>


<div class="container">
    <h2>Registrar nova doação</h2>

    <form action="cadastrar_doacao.php" method="POST">
        <label for="nome_doador">Nome do doador *</label>
        <input type="text" id="nome_doador" name="nome_doador" required>

        <label for="contato">Contato (WhatsApp, telefone ou e-mail)</label>
        <input type="text" id="contato" name="contato">

        <label for="tipo_doacao">Tipo de doação *</label>
        <input type="text" id="tipo_doacao" name="tipo_doacao" placeholder="Ex: cesta básica, brinquedo, roupa" required>

        <label for="quantidade">Quantidade *</label>
        <input type="number" id="quantidade" name="quantidade" min="1" required>

        <label for="data_doacao">Data da doação *</label>
        <input type="date" id="data_doacao" name="data_doacao" required>

        <label for="observacoes">Observações</label>
        <textarea id="observacoes" name="observacoes" rows="3" placeholder="Ex: vai entregar na igreja dia 20/12"></textarea>

        <button type="submit" class="btn-primario">Salvar doação</button>
        <a href="listar_doacoes.php">Ver lista de doações</a>
    </form>
</div>
</body>
</html>
