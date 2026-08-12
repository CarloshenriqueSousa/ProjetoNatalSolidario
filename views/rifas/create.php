<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-header">
        <h3 class="card-title">🎟️ Registro de Entrega de Lote de Rifas</h3>
        <a href="/rifas" class="btn btn-secondary">Voltar</a>
    </div>

    <form action="/rifas/salvar" method="POST">
        <div class="form-group">
            <label for="turma_id" class="form-label">Turma Destinatária *</label>
            <select name="turma_id" id="turma_id" class="form-control" required>
                <option value="">-- Selecione a Turma --</option>
                <?php foreach ($turmas as $t): ?>
                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nome'], ENT_QUOTES, 'UTF-8') ?> (Líder: <?= htmlspecialchars($t['lider_nome'] ?? 'Não informado', ENT_QUOTES, 'UTF-8') ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="lider_nome" class="form-label">Nome do Líder / Responsável do Bloco *</label>
            <input type="text" id="lider_nome" name="lider_nome" class="form-control" placeholder="Ex: João Pedro (Líder 3º A)" required>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label for="quantidade_entregue" class="form-label">Quantidade de Cartelas *</label>
                <input type="number" id="quantidade_entregue" name="quantidade_entregue" class="form-control" min="1" value="100" required>
            </div>

            <div class="form-group">
                <label for="valor_unitario" class="form-label">Valor Unitário por Cartela (R$) *</label>
                <input type="number" id="valor_unitario" name="valor_unitario" class="form-control" step="0.50" min="0.50" value="5.00" required>
            </div>
        </div>

        <div class="form-group">
            <label for="data_prevista_prestacao" class="form-label">Data Prevista para Prestação de Contas</label>
            <input type="date" id="data_prevista_prestacao" name="data_prevista_prestacao" class="form-control">
        </div>

        <div class="card" style="background: var(--bg-input); margin-bottom: 20px; border-color: var(--border-color);">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>Valor Total Esperado a Arrecadar:</span>
                <strong id="valor_esperado_total" style="font-size: 1.4rem; color: var(--secondary);">R$ 0,00</strong>
            </div>
        </div>

        <button type="submit" class="btn btn-success" style="width: 100%; justify-content: center; padding: 12px;">Confirmar Entrega de Lote</button>
    </form>
</div>
