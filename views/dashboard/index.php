<div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <div>
        <h2 style="font-size: 24px; font-weight: 700;">Painel do Sistema — Natal Solidário 🎄</h2>
        <p style="color: var(--text-muted, #94a3b8); margin-top: 4px;">
            Bem-vindo(a), <strong><?= htmlspecialchars($user['nome'], ENT_QUOTES, 'UTF-8') ?></strong>! 
            Perfil: <span class="badge badge-info"><?= htmlspecialchars(strtoupper($perfil), ENT_QUOTES, 'UTF-8') ?></span>
        </p>
    </div>

    <?php if (!$isRestrito): ?>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="/relatorios/export/estoque?format=pdf" class="btn btn-secondary btn-sm" target="_blank">
                📄 PDF Estoque
            </a>
            <a href="/relatorios/export/prestacao?format=pdf" class="btn btn-primary btn-sm" target="_blank">
                🎟️ PDF Prestação Contas
            </a>
        </div>
    <?php endif; ?>
</div>

<?php if (!$isRestrito): ?>
    <!-- ════════════════════════════════════════════════════════════════ -->
    <!-- INDICADORES FINANCEIROS REAIS -->
    <!-- ════════════════════════════════════════════════════════════════ -->
    <div class="stats-grid">
        <div class="stat-card primary">
            <div class="stat-icon">🎟️</div>
            <div class="stat-info">
                <div class="stat-label">Total Arrecadado Rifas</div>
                <div class="stat-value">R$ <?= number_format($resumoFinanceiro['total_rifas'] ?? 0, 2, ',', '.') ?></div>
            </div>
        </div>

        <div class="stat-card secondary">
            <div class="stat-icon">🤝</div>
            <div class="stat-info">
                <div class="stat-label">Patrocinadores</div>
                <div class="stat-value">R$ <?= number_format($resumoFinanceiro['total_patrocinadores'] ?? 0, 2, ',', '.') ?></div>
            </div>
        </div>

        <div class="stat-card warning">
            <div class="stat-icon">🏫</div>
            <div class="stat-info">
                <div class="stat-label">Recursos Escola (<?= $resumoFinanceiro['percentual_escola'] ?? 50 ?>%)</div>
                <div class="stat-value">R$ <?= number_format($resumoFinanceiro['valor_escola'] ?? 0, 2, ',', '.') ?></div>
            </div>
        </div>

        <div class="stat-card info">
            <div class="stat-icon">🎓</div>
            <div class="stat-info">
                <div class="stat-label">Recursos Turmas (<?= $resumoFinanceiro['percentual_turma'] ?? 50 ?>%)</div>
                <div class="stat-value">R$ <?= number_format($resumoFinanceiro['valor_turmas'] ?? 0, 2, ',', '.') ?></div>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════════ -->
    <!-- GRÁFICOS VISUAIS E RANKING DE TURMAS -->
    <!-- ════════════════════════════════════════════════════════════════ -->
    <div class="dashboard-columns">
        <!-- COLUNA ESQUERDA: GRÁFICOS DE ESTOQUE & EVOLUÇÃO -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <div class="card">
                <div class="card-title">
                    <span>📦 Distribuição de Produtos por Categoria</span>
                </div>
                <div id="chart_categorias" data-json="<?= htmlspecialchars(json_encode($resumoCategorias ?? []), ENT_QUOTES, 'UTF-8') ?>" class="chart-container" style="height: 240px;">
                </div>
            </div>

            <div class="card">
                <div class="card-title">
                    <span>📈 Evolução das Doações (Últimos 30 dias)</span>
                </div>
                <div id="chart_evolucao_doacoes" data-json="<?= htmlspecialchars(json_encode($evolucaoDoacoes ?? []), ENT_QUOTES, 'UTF-8') ?>" class="chart-container" style="height: 220px;">
                </div>
            </div>
        </div>

        <!-- COLUNA DIREITA: STATUS DAS RIFAS & RANKING DE TURMAS -->
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <div class="card">
                <div class="card-title">
                    <span>🎟️ Status das Rifas Escolares</span>
                </div>
                <div id="chart_rifa_status" data-json="<?= htmlspecialchars(json_encode($resumoStatusRifas ?? []), ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>

            <!-- MOTOR DE PONTUAÇÃO (ADMIN ONLY) -->
            <div class="card">
                <div class="card-title">
                    <span>🏆 Ranking das Turmas (Tempo Real)</span>
                    <span class="badge badge-success">Atualizado</span>
                </div>
                <p style="font-size: 12px; color: var(--text-muted, #94a3b8); margin-bottom: 15px;">
                    Fórmula: (Rifas × <?= $pesosPontuacao['rifa_vendida'] ?? 5 ?>) + 
                    (Alimentos × <?= $pesosPontuacao['alimento'] ?? 10 ?>) + 
                    (Roupas × <?= $pesosPontuacao['roupa'] ?? 15 ?>) + 
                    (Brinquedos × <?= $pesosPontuacao['brinquedo'] ?? 20 ?>) + 
                    (Higiene × <?= $pesosPontuacao['higiene'] ?? 12 ?>) - 
                    (Atrasos × <?= $pesosPontuacao['penalidade_atraso'] ?? 50 ?> pts)
                </p>

                <div class="ranking-list">
                    <?php if (empty($ranking)): ?>
                        <p style="color: var(--text-muted, #94a3b8); text-align: center; padding: 20px;">Nenhuma pontuação computada ainda.</p>
                    <?php else: ?>
                        <?php foreach ($ranking as $index => $item): ?>
                            <div class="ranking-item">
                                <div class="ranking-position"><?= $index + 1 ?>º</div>
                                <div class="ranking-details" style="flex: 1;">
                                    <div class="ranking-name"><?= htmlspecialchars($item['turma_nome'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <div style="font-size: 11px; color: var(--text-muted, #94a3b8); margin-top: 2px;">
                                        🍚 <?= $item['total_alimentos'] ?> | 👕 <?= $item['total_roupas'] ?> | 🧸 <?= $item['total_brinquedos'] ?> | 🧼 <?= $item['total_higiene'] ?> | 🎟️ <?= $item['total_rifas_vendidas'] ?>
                                        <?php if (!empty($item['lotes_com_problema']) && $item['lotes_com_problema'] > 0): ?>
                                            <span style="color: var(--color-danger, #ef4444); font-weight: bold; margin-left: 4px;">
                                                ⚠️ <?= $item['lotes_com_problema'] ?> atraso(s)
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="ranking-points" style="font-size: 16px; font-weight: 700; color: #10b981;">
                                    <?= number_format($item['pontuacao_total'], 0, ',', '.') ?> pts
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- ════════════════════════════════════════════════════════════════ -->
    <!-- DASHBOARD RESTRITO (RESPONSÁVEL DE COLETA / TURMA) -->
    <!-- ════════════════════════════════════════════════════════════════ -->
    <div class="alert alert-info" style="margin-bottom: 24px;">
        <strong>Modo Restrito da Turma:</strong> Você está visualizando apenas os registros cadastrados para a sua turma vinculada. O ranking geral e dados financeiros globais são visíveis apenas para a Administração.
    </div>

    <div class="dashboard-columns">
        <div class="card">
            <div class="card-title">
                <span>📦 Produtos Coletados pela Sua Turma</span>
            </div>
            <div id="chart_categorias" data-json="<?= htmlspecialchars(json_encode($resumoCategorias ?? []), ENT_QUOTES, 'UTF-8') ?>" class="chart-container" style="height: 240px;">
            </div>
        </div>

        <div class="card">
            <div class="card-title">
                <span>📈 Evolução das Doações da Sua Turma</span>
            </div>
            <div id="chart_evolucao_doacoes" data-json="<?= htmlspecialchars(json_encode($evolucaoDoacoes ?? []), ENT_QUOTES, 'UTF-8') ?>" class="chart-container" style="height: 240px;">
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- ════════════════════════════════════════════════════════════════ -->
<!-- TABELA DE RESUMO DE LOTES DE PRODUTOS RECENTES -->
<!-- ════════════════════════════════════════════════════════════════ -->
<div class="card" style="margin-top: 24px;">
    <div class="card-title">
        <span>📦 Lotes de Produtos Registrados Recentemente</span>
        <?php if ($perfil !== 'turma'): ?>
            <a href="/produtos/novo" class="btn btn-primary btn-sm">+ Cadastrar Lote</a>
        <?php endif; ?>
    </div>

    <div class="table-wrapper">
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
                    <tr><td colspan="5" style="text-align: center; color: var(--text-muted, #94a3b8);">Nenhum lote registrado.</td></tr>
                <?php else: ?>
                    <?php foreach (array_slice($produtos, 0, 10) as $p): ?>
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
