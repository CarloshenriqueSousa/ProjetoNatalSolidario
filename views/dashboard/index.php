<div style="margin-bottom: 24px;">
    <h2>Painel do Sistema</h2>
    <p style="color: var(--text-muted);">Bem-vindo(a), <?= htmlspecialchars($user['nome'], ENT_QUOTES, 'UTF-8') ?>! Perfil: <strong><?= htmlspecialchars(strtoupper($perfil), ENT_QUOTES, 'UTF-8') ?></strong></p>
</div>

<?php if (!$isRestrito): ?>
    <!-- DASHBOARD COMPLETO (ADMIN / SUBADMIN / COORDENADOR) -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-icon">🎟️</div>
            <div class="stat-info">
                <h3>Total em Rifas</h3>
                <div class="stat-value">R$ <?= number_format($resumoFinanceiro['total_rifas'] ?? 0, 2, ',', '.') ?></div>
            </div>
        </div>

        <div class="stat-card success">
            <div class="stat-icon">🤝</div>
            <div class="stat-info">
                <h3>Patrocinadores</h3>
                <div class="stat-value">R$ <?= number_format($resumoFinanceiro['total_patrocinadores'] ?? 0, 2, ',', '.') ?></div>
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-icon">🏫</div>
            <div class="stat-info">
                <h3>Recursos Escola (<?= $resumoFinanceiro['percentual_escola'] ?? 50 ?>%)</h3>
                <div class="stat-value">R$ <?= number_format($resumoFinanceiro['valor_escola'] ?? 0, 2, ',', '.') ?></div>
            </div>
        </div>

        <div class="stat-card info">
            <div class="stat-icon">🎓</div>
            <div class="stat-info">
                <h3>Recursos Turmas (<?= $resumoFinanceiro['percentual_turma'] ?? 50 ?>%)</h3>
                <div class="stat-value">R$ <?= number_format($resumoFinanceiro['valor_turmas'] ?? 0, 2, ',', '.') ?></div>
            </div>
        </div>
    </div>

    <!-- SEÇÃO DE RANKING DE TURMAS (EXCLUSIVO ADMIN / DIREÇÃO) -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">🏆 Ranking de Arrecadação por Turma (Tempo Real)</h3>
            <span class="badge badge-info">Atualizado Dinamicamente</span>
        </div>

        <div class="ranking-list">
            <?php if (empty($ranking)): ?>
                <p style="color: var(--text-muted);">Nenhuma pontuação computada ainda.</p>
            <?php else: ?>
                <?php foreach ($ranking as $index => $item): ?>
                    <div class="ranking-item">
                        <div class="ranking-pos"><?= $index + 1 ?>º</div>
                        <div class="ranking-details">
                            <div class="ranking-name"><?= htmlspecialchars($item['turma_nome'], ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="ranking-breakdown">
                                📦 Alimentos: <?= $item['total_alimentos'] ?> | 👕 Roupas: <?= $item['total_roupas'] ?> | 🧸 Brinquedos: <?= $item['total_brinquedos'] ?> | 🎟️ Rifas: <?= $item['total_rifas_vendidas'] ?>
                            </div>
                        </div>
                        <div class="ranking-score"><?= number_format($item['pontuacao_total'], 0, ',', '.') ?> pts</div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>
    <!-- DASHBOARD RESTRITO (RESPONSÁVEL DE COLETA / TURMA) -->
    <div class="alert alert-info">
        <strong>Modo de Acesso Restrito a Turma:</strong> Você está visualizando apenas os registros da sua turma vinculada. Pontuações e ranking gerais estão ocultos por regra de acesso.
    </div>
<?php endif; ?>

<!-- TABELA DE RESUMO DE LOTES RECENTES -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">📦 Lotes de Produtos Registrados</h3>
        <?php if ($perfil !== 'turma'): ?>
            <a href="/produtos/novo" class="btn btn-primary">+ Cadastrar Lote</a>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Turma</th>
                    <th>Categoria</th>
                    <th>Registrado Por</th>
                    <th>Data</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($produtos)): ?>
                    <tr><td colspan="5" style="text-align: center; color: var(--text-muted);">Nenhum lote registrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($produtos as $p): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($p['codigo_lote'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><?= htmlspecialchars($p['turma_nome'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge badge-info"><?= htmlspecialchars(strtoupper($p['categoria']), ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td><?= htmlspecialchars($p['usuario_nome'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($p['criado_em'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
