<div class="card" style="margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <h3 class="card-title" style="margin-bottom: 0;">Lotes de Doação</h3>
        <?php if ($isAdmin): ?>
            <a href="<?= url('batches/create') ?>" class="btn btn-primary">Registrar Novo Lote</a>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <h3 class="card-title">Listagem de Lotes Cadastrados</h3>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>Código do Lote</th>
                    <th>Responsável pelo Cadastro</th>
                    <th>Tipos de Produtos</th>
                    <th>Quantidade de Itens</th>
                    <th>Data de Cadastro</th>
                    <?php if ($isAdmin): ?><th style="text-align: right;">Ações</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($batches as $b): ?>
                    <tr>
                        <td style="font-weight: 700; font-family: monospace; font-size: 15px; color: var(--color-accent);">
                            <?= e($b['codigo']) ?>
                        </td>
                        <td><?= e($b['responsavel_nome']) ?></td>
                        <td>
                            <strong style="color: var(--color-info);"><?= e($b['total_produtos']) ?></strong> produtos diferentes
                        </td>
                        <td>
                            <strong style="color: var(--color-secondary); font-size: 15px;"><?= e($b['total_itens']) ?></strong> unidades
                        </td>
                        <td><?= format_datetime($b['created_at']) ?></td>
                        <?php if ($isAdmin): ?>
                            <td style="text-align: right;">
                                <a href="<?= url('batches/delete&id=' . $b['id']) ?>" 
                                   class="btn btn-danger btn-sm confirm-delete" 
                                   data-message="ATENÇÃO: Excluir este lote apagará AUTOMATICAMENTE todos os produtos e doações vinculados a ele. Tem certeza que deseja prosseguir?" 
                                   title="Excluir Lote">Excluir</a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($batches)): ?>
                    <tr>
                        <td colspan="<?= $isAdmin ? 6 : 5 ?>" style="text-align: center; color: var(--text-secondary); padding: 30px;">
                            Nenhum lote cadastrado no sistema.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
