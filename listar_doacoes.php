<?php
require 'conexao.php';

$sql = "SELECT * FROM doacoes ORDER BY data_doacao ASC, id DESC";
$resultado = $conexao->query($sql);

$msg = $_GET['msg'] ?? '';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Lista de Doações - Natal Solidário JMF</title>
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
    <a href="home.php">← Voltar para cadastro</a>

    <?php if ($msg): ?>
        <p><strong><?php echo htmlspecialchars($msg); ?></strong></p>
    <?php endif; ?>

    <?php if ($resultado && $resultado->num_rows > 0): ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Doador</th>
                <th>Contato</th>
                <th>Tipo</th>
                <th>Qtd</th>
                <th>Data</th>
                <th>Obs</th>
                <th>Ações</th>
            </tr>
            <?php while ($linha = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $linha['id']; ?></td>
                    <td><?php echo htmlspecialchars($linha['nome_doador']); ?></td>
                    <td><?php echo htmlspecialchars($linha['contato']); ?></td>
                    <td><?php echo htmlspecialchars($linha['tipo_doacao']); ?></td>
                    <td><?php echo $linha['quantidade']; ?></td>
                    <td><?php echo date('d/m/Y', strtotime($linha['data_doacao'])); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($linha['observacoes'])); ?></td>
                    <td>
    <a href="editar_doacao.php?id=<?php echo $linha['id']; ?>">Editar</a>
    |
    <a href="deletar_doacao.php?id=<?php echo $linha['id']; ?>" 
       onclick="return confirm('Tem certeza que deseja apagar esta doação?');">
       Apagar
    </a>
</td>

                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Nenhuma doação cadastrada ainda.</p>
    <?php endif; ?>
</div>
</body>
</html>
