<div class="card" style="margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 25px;">
        <h3 class="card-title" style="margin-bottom: 0;">Filtrar e Pesquisar</h3>
        <a href="<?= url('products/create') ?>" class="btn btn-primary">Registrar Nova Doação</a>
    </div>
    
    <!-- Filter form -->
    <form action="index.php" method="GET" class="filter-bar">
        <input type="hidden" name="route" value="products">
        
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Tipo de Produto</label>
            <select name="tipo" class="form-control">
                <option value="">Todos</option>
                <option value="roupa" <?= ($currentFilters['tipo'] ?? '') === 'roupa' ? 'selected' : '' ?>>Roupa</option>
                <option value="brinquedo" <?= ($currentFilters['tipo'] ?? '') === 'brinquedo' ? 'selected' : '' ?>>Brinquedo</option>
                <option value="alimento" <?= ($currentFilters['tipo'] ?? '') === 'alimento' ? 'selected' : '' ?>>Alimento</option>
            </select>
        </div>

        <?php if (!$isClass): ?>
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Turma</label>
            <select name="turma_id" class="form-control">
                <option value="">Todas</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= ($currentFilters['turma_id'] ?? 0) == $c['id'] ? 'selected' : '' ?>><?= e($c['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Lote</label>
            <select name="lote_id" class="form-control">
                <option value="">Todos</option>
                <?php foreach ($batches as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= ($currentFilters['lote_id'] ?? 0) == $b['id'] ? 'selected' : '' ?>><?= e($b['codigo']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Validade</label>
            <select name="validade" class="form-control">
                <option value="">Qualquer data</option>
                <option value="proximos" <?= ($currentFilters['validade'] ?? '') === 'proximos' ? 'selected' : '' ?>>Próximos do vencimento (30d)</option>
                <option value="vencidos" <?= ($currentFilters['validade'] ?? '') === 'vencidos' ? 'selected' : '' ?>>Vencidos</option>
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Pesquisa</label>
            <input type="text" name="search" class="form-control" placeholder="Nome ou lote..." value="<?= e($currentFilters['search'] ?? '') ?>">
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn btn-accent" style="flex: 1;">Filtrar</button>
            <a href="<?= url('products') ?>" class="btn btn-secondary">Limpar</a>
        </div>
    </form>
</div>

<div class="card">
    <h3 class="card-title">Listagem de Estoque</h3>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Produto</th>
                    <th>Tipo</th>
                    <th>Qtd</th>
                    <th>Lote</th>
                    <?php if (!$isClass): ?><th>Turma</th><?php endif; ?>
                    <th>Atributos / Detalhes</th>
                    <th>Última Atualização</th>
                    <th style="text-align: right;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?= $p['id'] ?></td>
                        <td style="font-weight: 600;"><?= e($p['nome']) ?></td>
                        <td>
                            <?php if ($p['tipo'] === 'roupa'): ?>
                                <span class="badge badge-info">Roupa</span>
                            <?php elseif ($p['tipo'] === 'brinquedo'): ?>
                                <span class="badge badge-warning">Brinquedo</span>
                            <?php else: ?>
                                <span class="badge badge-success">Alimento</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong style="font-size: 15px;"><?= e($p['quantidade']) ?></strong>
                        </td>
                        <td><span style="font-family: monospace; font-weight: 500;"><?= e($p['lote_codigo']) ?></span></td>
                        <?php if (!$isClass): ?><td><?= e($p['turma_nome']) ?></td><?php endif; ?>
                        <td>
                            <?php if ($p['tipo'] === 'roupa'): ?>
                                <small style="display: block;">Qualidade: <strong><?= e($p['roupa_qualidade']) ?></strong></small>
                                <small style="display: block;">Faixa: <strong><?= e($p['roupa_faixa_etaria']) ?></strong></small>
                            <?php elseif ($p['tipo'] === 'brinquedo'): ?>
                                <small>Faixa: <strong><?= e($p['brinquedo_faixa_etaria']) ?></strong></small>
                            <?php elseif ($p['tipo'] === 'alimento'): ?>
                                <small style="display: block;">Categoria: <strong><?= e($p['alimento_categoria']) ?></strong></small>
                                <small style="display: block;">Qualidade: <strong><?= e($p['alimento_qualidade']) ?></strong></small>
                                <?php 
                                    $isExpired = strtotime($p['alimento_data_validade']) < time();
                                    $isNear = strtotime($p['alimento_data_validade']) <= strtotime('+30 days') && !$isExpired;
                                ?>
                                <small style="display: block;">Validade: 
                                    <strong class="<?= $isExpired ? 'badge badge-danger' : ($isNear ? 'badge badge-warning' : '') ?>" style="font-size: 11px; padding: 2px 6px;">
                                        <?= format_date($p['alimento_data_validade']) ?>
                                    </strong>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td><?= format_datetime($p['updated_at']) ?></td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 8px;">
                                <a href="<?= url('products/stock&id=' . $p['id']) ?>" class="btn btn-secondary btn-sm" title="Entrada / Saída de Estoque">Estoque</a>
                                <a href="<?= url('products/edit&id=' . $p['id']) ?>" class="btn btn-accent btn-sm" title="Editar">Editar</a>
                                <?php if (has_role('admin')): ?>
                                    <a href="<?= url('products/delete&id=' . $p['id']) ?>" 
                                       class="btn btn-danger btn-sm confirm-delete" 
                                       data-message="Deseja realmente excluir permanentemente o produto '<?= e($p['nome']) ?>' do sistema?" 
                                       title="Excluir">Excluir</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="<?= $isClass ? 8 : 9 ?>" style="text-align: center; color: var(--text-secondary); padding: 30px;">
                            Nenhum produto encontrado correspondente aos filtros.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
