<div class="card">
    <div class="card-header">
        <h3 class="card-title">👨‍👩‍👧‍👦 Gerenciamento de Famílias Beneficiárias (Cestas)</h3>
    </div>

    <!-- FORMULÁRIO RÁPIDO DE CADASTRO -->
    <form action="/familias/salvar" method="POST" style="margin-bottom: 24px; background: var(--bg-input); padding: 16px; border-radius: 8px; border: 1px solid var(--border-color);">
        <h4 style="margin-bottom: 12px;">+ Cadastrar Nova Família</h4>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Nome do Responsável *</label>
                <input type="text" name="nome_responsavel" class="form-control" placeholder="Ex: Maria Silva" required>
            </div>
            <div class="form-group">
                <label class="form-label">Qtd Membros</label>
                <input type="number" name="quantidade_membros" class="form-control" min="1" value="4" required>
            </div>
            <div class="form-group">
                <label class="form-label">Qtd Filhos</label>
                <input type="number" name="quantidade_filhos" class="form-control" min="0" value="2" required>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">Endereço Completo *</label>
            <input type="text" name="endereco" class="form-control" placeholder="Rua, número, bairro" required>
        </div>
        <button type="submit" class="btn btn-success">+ Cadastrar Família</button>
    </form>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Responsável</th>
                    <th>Membros / Filhos</th>
                    <th>Endereço</th>
                    <th>Status da Cesta</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($familias)): ?>
                    <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">Nenhuma família cadastrada.</td></tr>
                <?php else: ?>
                    <?php foreach ($familias as $f): ?>
                        <tr>
                            <td>#<?= $f['id'] ?></td>
                            <td><strong><?= htmlspecialchars($f['nome_responsavel'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><?= $f['quantidade_membros'] ?> membros (<?= $f['quantidade_filhos'] ?> filhos)</td>
                            <td><?= htmlspecialchars($f['endereco'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <?php if ($f['status_entrega'] === 'entregue'): ?>
                                    <span class="badge badge-success">ENTREGUE em <?= date('d/m/Y H:i', strtotime($f['data_entrega'])) ?></span>
                                <?php else: ?>
                                    <span class="badge badge-warning">PENDENTE</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($f['status_entrega'] === 'pendente'): ?>
                                    <a href="/familias/entregar/<?= $f['id'] ?>" class="btn btn-primary" style="padding: 4px 10px; font-size: 0.8rem;">
                                        🎁 Registrar Entrega
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--text-muted); font-size: 0.85rem;">Concluído</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
