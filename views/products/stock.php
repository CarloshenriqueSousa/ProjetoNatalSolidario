<div class="card" style="max-width: 600px; margin: 0 auto;">
    <h3 class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 25px;">Ajustar Estoque de Doação</h3>

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <span><?= e($error) ?></span>
        </div>
    <?php endif; ?>

    <div style="margin-bottom: 25px; padding: 15px 20px; background-color: rgba(255,255,255,0.015); border: 1px solid var(--border-color); border-radius: var(--radius-md);">
        <p style="font-size: 13px; color: var(--text-secondary); text-transform: uppercase; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 5px;">Produto Selecionado</p>
        <h4 style="font-size: 18px; font-weight: 700; margin-bottom: 5px;"><?= e($product['nome']) ?></h4>
        
        <div style="display: flex; gap: 20px; margin-top: 10px; font-size: 14px;">
            <span>Tipo: <strong class="badge badge-info"><?= e($product['tipo']) ?></strong></span>
            <span>Estoque Atual: <strong style="color: var(--color-secondary); font-size: 16px;"><?= e($product['quantidade']) ?></strong> unidades</span>
        </div>
    </div>

    <form action="<?= url('products/stock&id=' . $product['id']) ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div class="form-group">
            <label class="form-label">Tipo de Movimentação *</label>
            <select name="tipo_movimentacao" class="form-control" required>
                <option value="Entrada">Entrada (Adicionar itens ao estoque)</option>
                <option value="Saída">Saída (Remover itens do estoque)</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Quantidade do Ajuste *</label>
            <input type="number" name="quantidade" class="form-control" min="1" placeholder="Ex: 5" required>
        </div>

        <div class="form-group">
            <label class="form-label">Motivo do Ajuste</label>
            <input type="text" name="motivo" class="form-control" placeholder="Ex: Recebimento de nova doação, Entrega para distribuição, Ajuste de inventário">
        </div>

        <div style="display: flex; gap: 15px; border-top: 1px solid var(--border-color); padding-top: 20px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary">Processar Ajuste</button>
            <a href="<?= url('products') ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
