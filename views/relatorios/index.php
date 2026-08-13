<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
    <div>
        <h2>📊 Central de Relatórios Gerais</h2>
        <p style="color: var(--text-muted);">Consolidação de Arrecadação, Rifas, Finanças e Entregas de Cestas.</p>
    </div>
    <button onclick="window.print()" class="btn btn-secondary">🖨️ Imprimir / Salvar PDF</button>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">1. Resumo Financeiro e Divisão de Recursos</h3>
    </div>
    <div class="form-grid">
        <div class="stat-card primary">
            <div class="stat-info">
                <h3>Total em Rifas</h3>
                <div class="stat-value">R$ <?= number_format($resumoFinanceiro['total_rifas'] ?? 0, 2, ',', '.') ?></div>
            </div>
        </div>
        <div class="stat-card warning">
            <div class="stat-info">
                <h3>Escola (<?= $resumoFinanceiro['percentual_escola'] ?>%)</h3>
                <div class="stat-value">R$ <?= number_format($resumoFinanceiro['valor_escola'] ?? 0, 2, ',', '.') ?></div>
            </div>
        </div>
        <div class="stat-card success">
            <div class="stat-info">
                <h3>Turmas (<?= $resumoFinanceiro['percentual_turma'] ?>%)</h3>
                <div class="stat-value">R$ <?= number_format($resumoFinanceiro['valor_turmas'] ?? 0, 2, ',', '.') ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">2. Relatório de Desempenho e Ranking das Turmas</h3>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Posição</th>
                    <th>Turma</th>
                    <th>Alimentos</th>
                    <th>Roupas</th>
                    <th>Brinquedos</th>
                    <th>Rifas Vendidas</th>
                    <th>Pontuação Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ranking as $idx => $r): ?>
                    <tr>
                        <td><strong><?= $idx + 1 ?>º</strong></td>
                        <td><?= htmlspecialchars($r['turma_nome'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= $r['total_alimentos'] ?></td>
                        <td><?= $r['total_roupas'] ?></td>
                        <td><?= $r['total_brinquedos'] ?></td>
                        <td><?= $r['total_rifas_vendidas'] ?></td>
                        <td><strong style="color: var(--secondary);"><?= number_format($r['pontuacao_total'], 0, ',', '.') ?> pts</strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
