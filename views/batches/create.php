<div class="card" style="max-width: 600px; margin: 0 auto;">
    <h3 class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 25px;">Registrar Novo Lote de Doações</h3>

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <span><?= e($error) ?></span>
        </div>
    <?php endif; ?>

    <form action="<?= url('batches/create') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="form-group">
            <label class="form-label">Código do Lote *</label>
            <input type="text" name="codigo" class="form-control" placeholder="Ex: LOTE-2026-A" required autofocus style="text-transform: uppercase;">
            <small style="color: var(--text-muted); margin-top: 4px; display: block;">Digite um identificador único de lote para agrupar as doações.</small>
        </div>

        <div style="display: flex; gap: 15px; border-top: 1px solid var(--border-color); padding-top: 20px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary">Registrar Lote</button>
            <a href="<?= url('batches') ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
