<div class="card" style="max-width: 800px; margin: 0 auto;">
    <h3 class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 25px;">Exportar Relatórios</h3>
    
    <p style="color: var(--text-secondary); margin-bottom: 30px; font-size: 14px;">
        Selecione o tipo de relatório desejado e escolha o formato de saída (PDF, Planilha Excel ou CSV) para fazer o download.
    </p>

    <!-- Stock / Products reports selection -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
        <!-- Card 1: Geral -->
        <div style="padding: 20px; border: 1px solid var(--border-color); background-color: rgba(255,255,255,0.01); border-radius: var(--radius-md);">
            <h4 style="font-weight: 600; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <span style="color: var(--color-primary);">●</span> Estoque Geral da Escola
            </h4>
            <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 20px;">
                Planilha completa do inventário geral contendo todas as roupas, brinquedos e alimentos cadastrados.
            </p>
            <div style="display: flex; gap: 8px;">
                <a href="<?= url('reports/export&type=estoque&format=pdf') ?>" class="btn btn-secondary btn-sm" style="flex: 1;">PDF</a>
                <a href="<?= url('reports/export&type=estoque&format=excel') ?>" class="btn btn-secondary btn-sm" style="flex: 1;">Excel</a>
                <a href="<?= url('reports/export&type=estoque&format=csv') ?>" class="btn btn-secondary btn-sm" style="flex: 1;">CSV</a>
            </div>
        </div>

        <!-- Card 2: Vencidos / Próximos -->
        <div style="padding: 20px; border: 1px solid var(--border-color); background-color: rgba(255,255,255,0.01); border-radius: var(--radius-md);">
            <h4 style="font-weight: 600; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <span style="color: var(--color-danger);">●</span> Validade de Alimentos
            </h4>
            <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 20px;">
                Produtos com data de vencimento expirada ou próximos do vencimento (limite de 30 dias).
            </p>
            <div style="display: flex; flex-direction: column; gap: 8px;">
                <div style="display: flex; gap: 8px; align-items: center;">
                    <span style="font-size: 12px; width: 80px; color: var(--text-secondary);">Vencidos:</span>
                    <a href="<?= url('reports/export&type=vencidos&format=pdf') ?>" class="btn btn-secondary btn-sm" style="flex: 1;">PDF</a>
                    <a href="<?= url('reports/export&type=vencidos&format=excel') ?>" class="btn btn-secondary btn-sm" style="flex: 1;">Excel</a>
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <span style="font-size: 12px; width: 80px; color: var(--text-secondary);">Próximos:</span>
                    <a href="<?= url('reports/export&type=proximos&format=pdf') ?>" class="btn btn-secondary btn-sm" style="flex: 1;">PDF</a>
                    <a href="<?= url('reports/export&type=proximos&format=excel') ?>" class="btn btn-secondary btn-sm" style="flex: 1;">Excel</a>
                </div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
        <!-- Card 3: Movimentações -->
        <div style="padding: 20px; border: 1px solid var(--border-color); background-color: rgba(255,255,255,0.01); border-radius: var(--radius-md);">
            <h4 style="font-weight: 600; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <span style="color: var(--color-warning);">●</span> Histórico de Movimentações
            </h4>
            <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 20px;">
                Registros de todas as entradas e saídas de estoque realizadas por turma ou administrador.
            </p>
            <div style="display: flex; gap: 8px;">
                <a href="<?= url('reports/export&type=movimentacoes&format=pdf') ?>" class="btn btn-secondary btn-sm" style="flex: 1;">PDF</a>
                <a href="<?= url('reports/export&type=movimentacoes&format=excel') ?>" class="btn btn-secondary btn-sm" style="flex: 1;">Excel</a>
                <a href="<?= url('reports/export&type=movimentacoes&format=csv') ?>" class="btn btn-secondary btn-sm" style="flex: 1;">CSV</a>
            </div>
        </div>

        <!-- Card 4: Por Turma -->
        <div style="padding: 20px; border: 1px solid var(--border-color); background-color: rgba(255,255,255,0.01); border-radius: var(--radius-md);">
            <h4 style="font-weight: 600; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <span style="color: var(--color-secondary);">●</span> Estoque por Turma
            </h4>
            <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 15px;">
                Filtre e exporte o estoque de uma turma específica.
            </p>
            
            <form action="index.php" method="GET" style="display: flex; flex-direction: column; gap: 10px;">
                <input type="hidden" name="route" value="reports/export">
                <input type="hidden" name="type" value="turma">
                
                <?php if (!$isClass): ?>
                <select name="turma_id" class="form-control" style="height: 34px; padding: 4px 10px; font-size: 13px;" required>
                    <option value="">Selecione a Turma</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= e($c['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php else: ?>
                    <input type="hidden" name="turma_id" value="<?= $_SESSION['user_turma_id'] ?>">
                    <p style="font-size: 12px; color: var(--color-secondary); font-weight: 600;">Turma: <?= e($_SESSION['user_nome']) ?></p>
                <?php endif; ?>
                
                <div style="display: flex; gap: 8px; margin-top: 5px;">
                    <button type="submit" name="format" value="pdf" class="btn btn-accent btn-sm" style="flex: 1;">Exportar PDF</button>
                    <button type="submit" name="format" value="excel" class="btn btn-accent btn-sm" style="flex: 1;">Exportar Excel</button>
                </div>
            </form>
        </div>
    </div>
</div>
