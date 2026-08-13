<div class="card" style="max-width: 600px; margin: 0 auto;">
    <h3 class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 25px;">Editar Cadastro da Turma</h3>

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <span><?= e($error) ?></span>
        </div>
    <?php endif; ?>

    <form action="<?= url('classes/edit&id=' . $class['id']) ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="form-group">
            <label class="form-label">Nome da Turma *</label>
            <input type="text" name="nome" class="form-control" value="<?= e($class['nome']) ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Usuário de Login *</label>
            <input type="text" name="login" class="form-control" value="<?= e($class['login']) ?>" required style="text-transform: lowercase;">
        </div>

        <div class="form-group">
            <label class="form-label">Senha de Acesso (Deixe em branco para manter a atual)</label>
            <input type="password" name="senha" class="form-control" placeholder="Digite uma nova senha caso queira alterar">
        </div>

        <div style="display: flex; gap: 15px; border-top: 1px solid var(--border-color); padding-top: 20px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            <a href="<?= url('classes') ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
