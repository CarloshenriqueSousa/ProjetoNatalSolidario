<div class="card" style="max-width: 750px; margin: 0 auto;">
    <div class="card-header">
        <h3 class="card-title">💰 Prestação de Contas de Rifa - Lote #<?= $rifa['id'] ?></h3>
        <a href="/rifas" class="btn btn-secondary">Voltar</a>
    </div>

    <!-- METADADOS DO LOTE -->
    <input type="hidden" id="meta_quantidade_entregue" value="<?= $rifa['quantidade_entregue'] ?>">
    <input type="hidden" id="meta_valor_unitario" value="<?= $rifa['valor_unitario'] ?>">

    <div class="division-widget" style="margin-bottom: 24px;">
        <div class="division-card">
            <h4>Turma / Responsável</h4>
            <div class="amount" style="font-size: 1.2rem;"><?= htmlspecialchars($rifa['turma_nome'], ENT_QUOTES, 'UTF-8') ?></div>
            <p style="font-size: 0.85rem; color: var(--text-muted);"><?= htmlspecialchars($rifa['lider_nome'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <div class="division-card">
            <h4>Cartelas Entregues</h4>
            <div class="amount" style="font-size: 1.2rem; color: var(--info);"><?= $rifa['quantidade_entregue'] ?> un</div>
            <p style="font-size: 0.85rem; color: var(--text-muted);">Valor Unitário: R$ <?= number_format($rifa['valor_unitario'], 2, ',', '.') ?></p>
        </div>
    </div>

    <form action="/rifas/prestacao/salvar" method="POST">
        <input type="hidden" name="lote_id" value="<?= $rifa['id'] ?>">

        <h4 style="margin-bottom: 14px; border-bottom: 1px solid var(--border-color); padding-bottom: 6px;">1. Conferência Física de Cartelas</h4>
        
        <div class="form-grid">
            <div class="form-group">
                <label for="quantidade_vendida" class="form-label">Cartelas Vendidas *</label>
                <input type="number" id="quantidade_vendida" name="quantidade_vendida" class="form-control" min="0" max="<?= $rifa['quantidade_entregue'] ?>" value="<?= $rifa['quantidade_vendida'] ?? $rifa['quantidade_entregue'] ?>" required>
            </div>

            <div class="form-group">
                <label for="quantidade_devolvida" class="form-label">Cartelas Devolvidas (Intactas)</label>
                <input type="number" id="quantidade_devolvida" name="quantidade_devolvida" class="form-control" min="0" max="<?= $rifa['quantidade_entregue'] ?>" value="<?= $rifa['quantidade_devolvida'] ?? 0 ?>" required>
            </div>

            <div class="form-group">
                <label for="quantidade_perdida" class="form-label">Cartelas Extravidadas / Perdidas</label>
                <input type="number" id="quantidade_perdida" name="quantidade_perdida" class="form-control" min="0" max="<?= $rifa['quantidade_entregue'] ?>" value="<?= $rifa['quantidade_perdida'] ?? 0 ?>" required>
            </div>
        </div>

        <h4 style="margin: 14px 0; border-bottom: 1px solid var(--border-color); padding-bottom: 6px;">2. Valor em Dinheiro Arrecadado</h4>

        <div class="form-group">
            <label for="valor_entregue" class="form-label">Valor Total Efetivamente Entregue em Mãos (R$) *</label>
            <input type="number" id="valor_entregue" name="valor_entregue" class="form-control" step="0.01" min="0" value="<?= $rifa['valor_entregue'] ?? ($rifa['quantidade_entregue'] * $rifa['valor_unitario']) ?>" required>
        </div>

        <div class="form-group">
            <label for="observacoes" class="form-label">Observações / Justificativas de Divergência</label>
            <textarea id="observacoes" name="observacoes" class="form-control" rows="3" placeholder="Caso haja divergência ou cartelas perdidas, mencione os detalhes aqui..."><?= htmlspecialchars($rifa['observacoes'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <!-- PAINEL DE VALIDAÇÃO EM TEMPO REAL -->
        <div class="card" style="background: var(--bg-input); border-color: var(--border-color); margin-bottom: 20px;">
            <h4 style="font-size: 0.95rem; margin-bottom: 10px; color: var(--text-muted);">Validação Automática em Tempo Real:</h4>
            
            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <span>Soma de Cartelas (Vendidas + Dev + Perdidas):</span>
                <strong id="calc_soma_quantidades">0 / 0</strong>
            </div>

            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                <span>Valor Calculado pelas Cartelas Vendidas:</span>
                <strong id="calc_valor_calculado" style="color: var(--info);">R$ 0,00</strong>
            </div>

            <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                <span>Diferença Apurada (Entregue - Calculado):</span>
                <strong id="calc_diferenca">R$ 0,00</strong>
            </div>

            <div style="text-align: center; margin-top: 10px;">
                <span id="calc_status_badge" class="badge badge-info">Aguardando Validação</span>
            </div>
        </div>

        <button type="submit" class="btn btn-success" style="width: 100%; justify-content: center; padding: 12px; font-size: 1rem;">
            💾 Finalizar & Registrar Prestação de Contas
        </button>
    </form>
</div>
