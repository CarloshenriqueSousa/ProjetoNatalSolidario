<div style="margin-bottom: 24px;">
    <h2>💰 Módulo Financeiro & Divisão de Recursos</h2>
    <p style="color: var(--text-muted);">Consolidação de Arrecadação de Rifas, Patrocinadores e Divisão Percentual de Verba.</p>
</div>

<div class="division-widget" style="margin-bottom: 24px;">
    <div class="division-card" style="border-top: 4px solid var(--accent);">
        <h4>🏫 Cota Destinada à Escola (<?= $resumo['percentual_escola'] ?>%)</h4>
        <div class="amount" style="color: var(--accent);">R$ <?= number_format($resumo['valor_escola'], 2, ',', '.') ?></div>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 6px;">Calculado sobre R$ <?= number_format($resumo['total_rifas'], 2, ',', '.') ?> de Rifas</p>
    </div>

    <div class="division-card" style="border-top: 4px solid var(--secondary);">
        <h4>🎓 Cota Destinada às Turmas (<?= $resumo['percentual_turma'] ?>%)</h4>
        <div class="amount" style="color: var(--secondary);">R$ <?= number_format($resumo['valor_turmas'], 2, ',', '.') ?></div>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 6px;">Verba proporcional a ser dividida entre as turmas</p>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">📜 Extrato de Movimentações Financeiras Automatizadas</h3>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Origem / Destino</th>
                    <th>Descrição</th>
                    <th>Valor</th>
                    <th>Registrado Por</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($movimentacoes)): ?>
                    <tr><td colspan="7" style="text-align: center; color: var(--text-muted);">Nenhuma movimentação registrada.</td></tr>
                <?php else: ?>
                    <?php foreach ($movimentacoes as $m): ?>
                        <tr>
                            <td>#<?= $m['id'] ?></td>
                            <td>
                                <span class="badge <?= ($m['tipo'] === 'entrada') ? 'badge-success' : 'badge-danger' ?>">
                                    <?= htmlspecialchars(strtoupper($m['tipo']), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td><strong><?= htmlspecialchars($m['origem_destino'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><?= htmlspecialchars($m['descricao'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            <td style="color: <?= ($m['tipo'] === 'entrada') ? '#2ecc71' : '#e74c3c' ?>; font-weight: bold;">
                                <?= ($m['tipo'] === 'entrada') ? '+' : '-' ?> R$ <?= number_format($m['valor'], 2, ',', '.') ?>
                            </td>
                            <td><?= htmlspecialchars($m['usuario_nome'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($m['data_movimentacao'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
