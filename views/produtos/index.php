<div class="card">
    <div class="card-header">
        <h3 class="card-title">📦 Gestão de Produtos & Lotes de Coleta</h3>
        <?php if ($perfil !== 'turma'): ?>
            <a href="/produtos/novo" class="btn btn-primary">+ Novo Lote de Produto</a>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Código do Lote</th>
                    <th>Turma</th>
                    <th>Categoria</th>
                    <th>Registrado Por</th>
                    <th>Data do Registro</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($produtos)): ?>
                    <tr><td colspan="5" style="text-align: center; color: var(--text-muted);">Nenhum lote registrado até o momento.</td></tr>
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
