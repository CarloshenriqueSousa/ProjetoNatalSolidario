<div class="stats-grid">
    <!-- Stat card 1: Total items -->
    <div class="stat-card primary">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
        </div>
        <span class="stat-label">Total de Itens</span>
        <span class="stat-value"><?= e($stats['estoque_atual']) ?></span>
    </div>

    <!-- Stat card 2: Clothes -->
    <div class="stat-card info">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.38 3.46L16 2.14a2 2 0 0 0-1-.14H9a2 2 0 0 0-1 .14L3.62 3.46A2 2 0 0 0 2.5 5.34v4.32a2 2 0 0 0 .52 1.34L7.5 16h9l4.48-4.99a2 2 0 0 0 .52-1.34V5.34a2 2 0 0 0-1.12-1.88z"></path><path d="M12 2v20"></path></svg>
        </div>
        <span class="stat-label">Total de Roupas</span>
        <span class="stat-value"><?= e($stats['total_roupas']) ?></span>
    </div>

    <!-- Stat card 3: Toys -->
    <div class="stat-card secondary">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20z"></path><path d="M12 6v12"></path><path d="M6 12h12"></path></svg>
        </div>
        <span class="stat-label">Total de Brinquedos</span>
        <span class="stat-value"><?= e($stats['total_brinquedos']) ?></span>
    </div>

    <!-- Stat card 4: Food -->
    <div class="stat-card success">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>
        </div>
        <span class="stat-label">Total de Alimentos</span>
        <span class="stat-value"><?= e($stats['total_alimentos']) ?></span>
    </div>
</div>

<div class="stats-grid">
    <!-- Extra Row for batches & alerts -->
    <div class="stat-card">
        <div class="stat-icon" style="color: var(--color-accent); background-color: rgba(99, 102, 241, 0.1);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect></svg>
        </div>
        <span class="stat-label">Total de Lotes</span>
        <span class="stat-value"><?= e($stats['total_lotes']) ?></span>
    </div>

    <div class="stat-card warning">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        </div>
        <span class="stat-label">Próximos do Vencimento</span>
        <span class="stat-value"><?= e($stats['proximos_vencimento']) ?></span>
    </div>

    <div class="stat-card danger">
        <div class="stat-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"></polygon><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        </div>
        <span class="stat-label">Produtos Vencidos</span>
        <span class="stat-value"><?= e($stats['vencidos']) ?></span>
    </div>
</div>

<div class="dashboard-columns">
    <!-- Column 1: Charts and Leaderboards -->
    <div style="display: flex; flex-direction: column; gap: 30px;">
        <div class="card">
            <h3 class="card-title">Distribuição de Arrecadações</h3>
            <div class="chart-container" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: auto;">
                <div id="type_donut_chart" style="width: 100%; max-width: 320px;" 
                     data-roupas="<?= (int)$stats['total_roupas'] ?>" 
                     data-brinquedos="<?= (int)$stats['total_brinquedos'] ?>" 
                     data-alimentos="<?= (int)$stats['total_alimentos'] ?>">
                </div>
            </div>
        </div>

        <?php if (!$isClass): ?>
        <div class="card">
            <h3 class="card-title">Gráfico de Pontuação das Turmas</h3>
            <div id="ranking_bar_chart" style="width: 100%;" data-ranking='<?= json_encode($rankings) ?>'></div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Column 2: Rankings / Quick links / History Feed -->
    <div style="display: flex; flex-direction: column; gap: 30px;">
        <?php if (!$isClass): ?>
        <div class="card">
            <h3 class="card-title">Placar de Líderes (Ranking)</h3>
            <div class="ranking-list">
                <?php foreach ($rankings as $idx => $r): ?>
                    <div class="ranking-item">
                        <div class="ranking-position"><?= $idx + 1 ?></div>
                        <span class="ranking-name"><?= e($r['nome']) ?></span>
                        <span class="ranking-points"><?= e($r['total_pontos']) ?> pts</span>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($rankings)): ?>
                    <p style="color: var(--text-secondary); text-align: center;">Nenhuma turma cadastrada.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <div class="card">
            <h3 class="card-title" style="margin-bottom: 15px;">Acesso Rápido</h3>
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <a href="<?= url('products/create') ?>" class="btn btn-primary" style="width: 100%;">Registrar Nova Doação</a>
                <a href="<?= url('products') ?>" class="btn btn-secondary" style="width: 100%;">Consultar Meu Estoque</a>
                <a href="<?= url('history') ?>" class="btn btn-secondary" style="width: 100%;">Ver Meu Histórico</a>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <h3 class="card-title">Ações Recentes</h3>
            <ul style="list-style: none; display: flex; flex-direction: column; gap: 15px;">
                <?php foreach ($recentMovements as $m): ?>
                    <li style="border-left: 3px solid <?= $m['tipo'] === 'Entrada' ? 'var(--color-secondary)' : 'var(--color-danger)' ?>; padding-left: 12px; font-size: 13px;">
                        <div>
                            <strong><?= e($m['tipo']) ?>:</strong> <?= e($m['produto_nome']) ?> 
                            <span style="color: var(--text-secondary)">x<?= e($m['quantidade']) ?></span>
                        </div>
                        <div style="color: var(--text-muted); font-size: 11px;">
                            Por <?= e($m['usuario_nome']) ?> em <?= format_datetime($m['created_at']) ?>
                        </div>
                    </li>
                <?php endforeach; ?>
                <?php if (empty($recentMovements)): ?>
                    <li style="color: var(--text-secondary); text-align: center; font-size: 13px;">Nenhuma movimentação recente.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<div class="card" style="margin-top: 30px;">
    <h3 class="card-title">Doações Cadastradas Recentemente</h3>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Qtd</th>
                    <th>Lote</th>
                    <?php if (!$isClass): ?><th>Turma</th><?php endif; ?>
                    <th>Data Cadastro</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentProducts as $p): ?>
                    <tr>
                        <td style="font-weight: 600;"><?= e($p['nome']) ?></td>
                        <td><span class="badge badge-info"><?= e($p['tipo']) ?></span></td>
                        <td><strong><?= e($p['quantidade']) ?></strong></td>
                        <td><span style="font-family: monospace;"><?= e($p['lote_codigo']) ?></span></td>
                        <?php if (!$isClass): ?><td><?= e($p['turma_nome']) ?></td><?php endif; ?>
                        <td><?= format_datetime($p['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($recentProducts)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--text-secondary);">Nenhum produto cadastrado recentemente.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
