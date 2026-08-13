<div class="card" style="margin-bottom: 30px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <h3 class="card-title" style="margin-bottom: 0;">Gerenciamento de Turmas</h3>
        <a href="<?= url('classes/create') ?>" class="btn btn-primary">Adicionar Nova Turma</a>
    </div>
</div>

<div class="card">
    <h3 class="card-title">Turmas Cadastradas no Sistema</h3>
    <div class="table-wrapper">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome da Turma</th>
                    <th>Login de Acesso</th>
                    <th>Data de Cadastro</th>
                    <th style="text-align: right;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($classes as $c): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td style="font-weight: 600; font-size: 15px;"><?= e($c['nome']) ?></td>
                        <td><span style="font-family: monospace; background-color: rgba(255,255,255,0.03); padding: 4px 8px; border-radius: 4px; border: 1px solid var(--border-color);"><?= e($c['login']) ?></span></td>
                        <td><?= format_datetime($c['created_at']) ?></td>
                        <td style="text-align: right;">
                            <div style="display: inline-flex; gap: 8px;">
                                <a href="<?= url('classes/edit&id=' . $c['id']) ?>" class="btn btn-accent btn-sm" title="Editar Turma">Editar</a>
                                <a href="<?= url('classes/delete&id=' . $c['id']) ?>" 
                                   class="btn btn-danger btn-sm confirm-delete" 
                                   data-message="ATENÇÃO: Excluir esta turma apagará permanentemente a conta de login e todas as doações vinculadas a ela. Tem certeza?" 
                                   title="Excluir Turma">Excluir</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($classes)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-secondary); padding: 30px;">
                            Nenhuma turma cadastrada no sistema.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
