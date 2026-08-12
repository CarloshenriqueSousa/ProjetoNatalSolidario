<div class="card">
    <div class="card-header">
        <h3 class="card-title">🎟️ Gerenciamento de Rifas Solidárias</h3>
        <?php if (in_array($perfil, ['admin', 'subadmin', 'coordenador'])): ?>
            <a href="/rifas/nova" class="btn btn-primary">+ Entregar Novo Lote</a>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Turma</th>
                    <th>Líder Responsável</th>
                    <th>Qtd Entregue</th>
                    <th>Valor Unitário</th>
                    <th>Valor Esperado</th>
                    <th>Valor Arrecadado</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rifas)): ?>
                    <tr><td colspan="9" style="text-align: center; color: var(--text-muted);">Nenhum lote de rifa cadastrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($rifas as $r): ?>
                        <?php 
                            $esperado = $r['quantidade_entregue'] * $r['valor_unitario'];
                            $statusClass = 'badge-info';
                            if ($r['status'] === 'prestacao_realizada' || $r['status'] === 'finalizado') $statusClass = 'badge-success';
                            if ($r['status'] === 'com_divergencia' || $r['status'] === 'em_atraso') $statusClass = 'badge-danger';
                            if ($r['status'] === 'entregue') $statusClass = 'badge-warning';
                        ?>
                        <tr>
                            <td>#<?= $r['id'] ?></td>
                            <td><strong><?= htmlspecialchars($r['turma_nome'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><?= htmlspecialchars($r['lider_nome'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= $r['quantidade_entregue'] ?> cartelas</td>
                            <td>R$ <?= number_format($r['valor_unitario'], 2, ',', '.') ?></td>
                            <td>R$ <?= number_format($esperado, 2, ',', '.') ?></td>
                            <td>R$ <?= number_format($r['valor_entregue'] ?? 0, 2, ',', '.') ?></td>
                            <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars(strtoupper(str_replace('_', ' ', $r['status'])), ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td>
                                <?php if (in_array($perfil, ['admin', 'subadmin', 'coordenador'])): ?>
                                    <a href="/rifas/prestacao/<?= $r['id'] ?>" class="btn btn-secondary" style="padding: 4px 10px; font-size: 0.8rem;">
                                        <?= ($r['status'] === 'entregue') ? 'Realizar Prestação' : 'Ver Prestação' ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.85rem;">Somente Leitura</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
