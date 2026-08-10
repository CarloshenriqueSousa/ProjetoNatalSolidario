<?php
require 'conexao.php';

if (!isset($_GET['id'])) {
    header("Location: listar_doacoes.php");
    exit;
}

$id = (int) $_GET['id'];

$sql = "SELECT * FROM doacoes WHERE id = $id";
$resultado = $conexao->query($sql);

if (!$resultado || $resultado->num_rows === 0) {
    echo "Doação não encontrada.";
    exit;
}

$doacao = $resultado->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Doação - Natal Solidário JMF</title>
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
        <a href="index.php">Registrar doações</a>
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
</header>

<div class="container">
    <a href="listar_doacoes.php">← Voltar para lista</a>

    <h2>Editar doação #<?php echo $doacao['id']; ?></h2>

    <form action="atualizar_doacao.php" method="POST">
        <input type="hidden" name="id" value="<?php echo $doacao['id']; ?>">

        <label for="nome_doador">Nome do doador *</label>
        <input type="text" id="nome_doador" name="nome_doador" 
               value="<?php echo htmlspecialchars($doacao['nome_doador']); ?>" required>

        <label for="contato">Contato</label>
        <input type="text" id="contato" name="contato" 
               value="<?php echo htmlspecialchars($doacao['contato']); ?>">

        <label for="tipo_doacao">Tipo de doação *</label>
        <input type="text" id="tipo_doacao" name="tipo_doacao" 
               value="<?php echo htmlspecialchars($doacao['tipo_doacao']); ?>" required>

        <label for="quantidade">Quantidade *</label>
        <input type="number" id="quantidade" name="quantidade" min="1" 
               value="<?php echo (int)$doacao['quantidade']; ?>" required>

        <label for="data_doacao">Data da doação *</label>
        <input type="date" id="data_doacao" name="data_doacao" 
               value="<?php echo $doacao['data_doacao']; ?>" required>

        <label for="observacoes">Observações</label>
        <textarea id="observacoes" name="observacoes" rows="3"><?php 
            echo htmlspecialchars($doacao['observacoes']); 
        ?></textarea>

        <button type="submit" class="btn-primario">Salvar alterações</button>
        <a href="listar_doacoes.php" class="btn-secundario">Cancelar</a>
    </form>
</div>
</body>
</html>
