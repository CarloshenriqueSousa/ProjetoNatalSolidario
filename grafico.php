<?php
require 'conexao.php';

// Agrupa doações por data
$sql = "SELECT data_doacao, SUM(quantidade) AS total_itens, COUNT(*) AS total_doacoes
        FROM doacoes
        GROUP BY data_doacao
        ORDER BY data_doacao ASC";
$resultado = $conexao->query($sql);

// Arrays pra mandar pro JS
$labels = [];
$totais_itens = [];
$totais_doacoes = [];

if ($resultado && $resultado->num_rows > 0) {
    while ($linha = $resultado->fetch_assoc()) {
        // Label curtinha: dia/mês
        $labels[] = date('d/m', strtotime($linha['data_doacao']));
        $totais_itens[] = (int)$linha['total_itens'];
        $totais_doacoes[] = (int)$linha['total_doacoes'];
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gráfico de Doações - Natal Solidário JMF</title>
    <link rel="stylesheet" href="estilos.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<header>
    <div class="titulo">
        <h1>Natal Solidário JMF</h1>
        <p>Gráfico de doações</p>
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

<div class="container">
    <h2>Doações por dia</h2>

    <?php if (!empty($labels)): ?>
        <canvas id="graficoDoacoes"></canvas>

        <script>
            // Dados vindos do PHP
            const labels = <?php echo json_encode($labels); ?>;
            const totaisItens = <?php echo json_encode($totais_itens); ?>;
            const totaisDoacoes = <?php echo json_encode($totais_doacoes); ?>;

            const ctx = document.getElementById('graficoDoacoes').getContext('2d');

            const grafico = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Total de itens doados',
                            data: totaisItens,
                            borderWidth: 1
                        },
                        {
                            label: 'Número de doações',
                            data: totaisDoacoes,
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true
                        },
                        title: {
                            display: true,
                            text: 'Resumo de doações por dia'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        </script>

        <h3 style="margin-top: 30px;">Tabela de apoio</h3>
        <table>
            <tr>
                <th>Data</th>
                <th>Total de itens</th>
                <th>Número de doações</th>
            </tr>
            <?php
            // Reexecuta a query pra montar a tabela
            $resultadoTabela = $conexao->query($sql);
            while ($linha = $resultadoTabela->fetch_assoc()):
            ?>
                <tr>
                    <td><?php echo date('d/m/Y', strtotime($linha['data_doacao'])); ?></td>
                    <td><?php echo (int)$linha['total_itens']; ?></td>
                    <td><?php echo (int)$linha['total_doacoes']; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>Ainda não há dados suficientes para montar o gráfico.</p>
    <?php endif; ?>
</div>
</body>
</html>
