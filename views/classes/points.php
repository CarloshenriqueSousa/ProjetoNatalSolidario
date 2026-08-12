<div class="card" style="max-width: 600px; margin: 0 auto;">
    <h3 class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 25px;">Configuração de Multiplicadores de Pontos</h3>

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <span><?= e($error) ?></span>
        </div>
    <?php endif; ?>

    <p style="color: var(--text-secondary); margin-bottom: 25px; font-size: 14px;">
        Defina quantos pontos vale cada unidade de produto doado por categoria. A pontuação geral das turmas e o placar de líderes (ranking) serão recalculados automaticamente pelo sistema.
    </p>

    <form action="<?= url('classes/points') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="form-group">
            <label class="form-label" style="color: var(--color-success); font-weight: 600;">Pontos por Alimento (cada unidade)</label>
            <input type="number" name="alimento" class="form-control" min="0" value="<?= (int)($points['alimento'] ?? 5) ?>" required>
            <small style="color: var(--text-muted); display: block; margin-top: 4px;">Ex: 1 alimento = 5 pontos.</small>
        </div>

        <div class="form-group">
            <label class="form-label" style="color: var(--color-info); font-weight: 600;">Pontos por Roupa (cada unidade)</label>
            <input type="number" name="roupa" class="form-control" min="0" value="<?= (int)($points['roupa'] ?? 10) ?>" required>
            <small style="color: var(--text-muted); display: block; margin-top: 4px;">Ex: 1 roupa = 10 pontos.</small>
        </div>

        <div class="form-group">
            <label class="form-label" style="color: var(--color-warning); font-weight: 600;">Pontos por Brinquedo (cada unidade)</label>
            <input type="number" name="brinquedo" class="form-control" min="0" value="<?= (int)($points['brinquedo'] ?? 15) ?>" required>
            <small style="color: var(--text-muted); display: block; margin-top: 4px;">Ex: 1 brinquedo = 15 pontos.</small>
        </div>

        <div style="display: flex; gap: 15px; border-top: 1px solid var(--border-color); padding-top: 20px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary">Salvar Multiplicadores</button>
            <a href="<?= url('dashboard') ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
