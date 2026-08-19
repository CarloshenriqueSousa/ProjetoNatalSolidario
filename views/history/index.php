<div class="card" style="margin-bottom: 30px;">
    <h3 class="card-title" style="margin-bottom: 20px;">Filtros do Histórico</h3>
    
    <form action="index.php" method="GET" class="filter-bar">
        <input type="hidden" name="route" value="history">

        <?php if (!$isClass): ?>
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Operador (Usuário)</label>
            <select name="usuario_id" class="form-control">
                <option value="">Todos</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= ($currentFilters['usuario_id'] ?? 0) == $u['id'] ? 'selected' : '' ?>><?= e($u['nome']) ?> (<?= e($u['tipo']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Ação Realizada</label>
            <select name="acao" class="form-control">
                <option value="">Todas</option>
                <option value="Cadastro" <?= ($currentFilters['acao'] ?? '') === 'Cadastro' ? 'selected' : '' ?>>Cadastro Inicial</option>
                <option value="Entrada" <?= ($currentFilters['acao'] ?? '') === 'Entrada' ? 'selected' : '' ?>>Entrada de Estoque</option>
                <option value="Saída" <?= ($currentFilters['acao'] ?? '') === 'Saída' ? 'selected' : '' ?>>Saída de Estoque</option>
                <option value="Alteração" <?= ($currentFilters['acao'] ?? '') === 'Alteração' ? 'selected' : '' ?>>Alteração/Edição</option>
                <option value="Exclusão" <?= ($currentFilters['acao'] ?? '') === 'Exclusão' ? 'selected' : '' ?>>Exclusão</option>
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Pesquisa rápida</label>
            <input type="text" name="search" class="form-control" placeholder="Detalhes, lote ou produto..." value="<?= e($currentFilters['search'] ?? '') ?>">
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn btn-accent" style="flex: 1;">Filtrar</button>
            <a href="<?= url('history') ?>" class="btn btn-secondary">Limpar</a>
        </div>
    </form>
</div>

<div class="card">
    <h3 class="card-title">Histórico Geral de Auditoria</h3>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Data/Hora</th>
                    <th>Operador</th>
                    <th>Cargo/Tipo</th>
                    <th>Ação</th>
                    <th>Produto Referência</th>
                    <th>Lote Referência</th>
                    <th>Quantidade</th>
                    <th>Descrição da Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $l): ?>
                    <tr>
                        <td style="color: var(--text-muted); font-size: 13px; font-weight: 500;">
                            <?= format_datetime($l['created_at']) ?>
                        </td>
                        <td style="font-weight: 600;"><?= e($l['usuario_nome']) ?></td>
                        <td>
                            <span class="badge <?= $l['usuario_tipo'] === 'admin' ? 'badge-danger' : 'badge-info' ?>" style="font-size: 10px;">
                                <?= e($l['usuario_tipo']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($l['acao'] === 'Cadastro'): ?>
                                <span class="badge badge-success" style="background-color: rgba(16, 185, 129, 0.15);">Cadastro</span>
                            <?php elseif ($l['acao'] === 'Entrada'): ?>
                                <span class="badge badge-success">Entrada</span>
                            <?php elseif ($l['acao'] === 'Saída'): ?>
                                <span class="badge badge-danger">Saída</span>
                            <?php elseif ($l['acao'] === 'Alteração'): ?>
                                <span class="badge badge-warning">Alteração</span>
                            <?php elseif ($l['acao'] === 'Exclusão'): ?>
                                <span class="badge badge-danger" style="background-color: rgba(239, 68, 68, 0.2); color: #f87171;">Exclusão</span>
                            <?php endif; ?>
                        </td>
                        <td><?= e($l['produto_nome'] ?? '-') ?></td>
                        <td><span style="font-family: monospace; font-weight: 600;"><?= e($l['lote_codigo'] ?? '-') ?></span></td>
                        <td><strong><?= $l['quantidade'] !== null ? e($l['quantidade']) : '-' ?></strong></td>
                        <td style="color: var(--text-secondary); font-size: 13px;"><?= e($l['detalhes']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-secondary); padding: 30px;">
                            Nenhum registro de log encontrado.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
