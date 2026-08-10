<?php
require 'conexao.php';

// Exemplo de ranking simples: soma quantidade de doações por doador
$sql = "SELECT nome_doador, contato, SUM(quantidade) AS total_itens, COUNT(*) AS total_doacoes
        FROM doacoes
        GROUP BY nome_doador, contato
        ORDER BY total_itens DESC
        LIMIT 10";
$resultado = $conexao->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Ranking de Doadores - Natal Solidário JMF</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<header>
    <div class="titulo">
        <h1>Natal Solidário JMF</h1>
        <p>Lista de Doações</p>
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
document.addEventListener('click', function(event) {
    const menu = document.getElementById('menu');
    const button = document.querySelector('.menu-btn');
    if (!menu.contains(event.target) && !button.contains(event.target)) {
        menu.style.display = 'none';
    }
});
</script>

<div class="container">
    <h2>Ranking de melhores doadores</h2>

    <?php if ($resultado && $resultado->num_rows > 0): ?>
        <table>
            <tr>
                <th>Posição</th>
                <th>Doador</th>
                <th>Contato</th>
                <th>Total de itens</th>
                <th>Número de doações</th>
            </tr>
            <?php $posicao = 1; ?>
            <?php while ($linha = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $posicao++; ?></td>
                    <td><?php echo htmlspecialchars($linha['nome_doador']); ?></td>
                    <td><?php echo htmlspecialchars($linha['contato']); ?></td>
                    <td><?php echo (int)$linha['total_itens']; ?></td>
                    <td><?php echo (int)$linha['total_doacoes']; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Ainda não há doações suficientes para montar um ranking.</p>
    <?php endif; ?>
</div>
</body>
</html>
