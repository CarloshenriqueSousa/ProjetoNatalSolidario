<div class="card" style="max-width: 800px; margin: 0 auto;">
    <h3 class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 25px;">Editar Registro de Doação</h3>

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <span><?= e($error) ?></span>
        </div>
    <?php endif; ?>

    <form action="<?= url('products/edit&id=' . $product['id']) ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <!-- Read-only Category indicator -->
        <div class="form-group">
            <label class="form-label">Tipo de Categoria</label>
            <input type="text" class="form-control" value="<?= ucfirst(e($product['tipo'])) ?>" style="background-color: rgba(255,255,255,0.03); color: var(--text-secondary); cursor: not-allowed;" readonly>
        </div>

        <div class="form-group">
            <label class="form-label">Nome do Produto *</label>
            <input type="text" name="nome" class="form-control" value="<?= e($product['nome']) ?>" required>
        </div>

        <div class="form-group">
            <label class="form-label">Quantidade em Estoque</label>
            <input type="text" class="form-control" value="<?= e($product['quantidade']) ?>" style="background-color: rgba(255,255,255,0.03); color: var(--text-secondary); cursor: not-allowed;" readonly>
            <small style="color: var(--text-muted); margin-top: 4px; display: block;">A quantidade em estoque deve ser modificada através do formulário de Movimentação (Entrada/Saída).</small>
        </div>

        <div class="form-group">
            <label class="form-label">Lote Associado *</label>
            <select name="lote_id" class="form-control" required>
                <?php foreach ($batches as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= $product['lote_id'] == $b['id'] ? 'selected' : '' ?>><?= e($b['codigo']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if (!$isClass): ?>
        <div class="form-group">
            <label class="form-label">Turma Responsável *</label>
            <select name="turma_id" class="form-control" required>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $product['turma_id'] == $c['id'] ? 'selected' : '' ?>><?= e($c['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <!-- Specific properties: Roupas -->
        <?php if ($product['tipo'] === 'roupa'): ?>
            <div style="border-top: 1px solid var(--border-color); padding-top: 20px; margin-top: 25px; margin-bottom: 25px;">
                <h4 style="margin-bottom: 15px; color: var(--color-accent);">Atributos de Roupas</h4>
                
                <div class="form-group">
                    <label class="form-label">Qualidade *</label>
                    <select name="qualidade" class="form-control">
                        <option value="Boa" <?= $product['roupa_qualidade'] === 'Boa' ? 'selected' : '' ?>>Boa (Pronta para uso)</option>
                        <option value="Ruim" <?= $product['roupa_qualidade'] === 'Ruim' ? 'selected' : '' ?>>Ruim (Danificada/Sem condições)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Faixa Etária *</label>
                    <select name="faixa_etaria" class="form-control">
                        <option value="Bebê" <?= $product['roupa_faixa_etaria'] === 'Bebê' ? 'selected' : '' ?>>Bebê</option>
                        <option value="Criança" <?= $product['roupa_faixa_etaria'] === 'Criança' ? 'selected' : '' ?>>Criança</option>
                        <option value="Adolescente" <?= $product['roupa_faixa_etaria'] === 'Adolescente' ? 'selected' : '' ?>>Adolescente</option>
                        <option value="Adulto" <?= $product['roupa_faixa_etaria'] === 'Adulto' ? 'selected' : '' ?>>Adulto</option>
                    </select>
                </div>
            </div>
        <?php endif; ?>

        <!-- Specific properties: Brinquedos -->
        <?php if ($product['tipo'] === 'brinquedo'): ?>
            <div style="border-top: 1px solid var(--border-color); padding-top: 20px; margin-top: 25px; margin-bottom: 25px;">
                <h4 style="margin-bottom: 15px; color: var(--color-accent);">Atributos de Brinquedos</h4>
                
                <div class="form-group">
                    <label class="form-label">Faixa Etária Recomendada *</label>
                    <select name="faixa_etaria" class="form-control">
                        <option value="0-4 anos" <?= $product['brinquedo_faixa_etaria'] === '0-4 anos' ? 'selected' : '' ?>>0 a 4 anos (Primeira infância)</option>
                        <option value="5+ anos" <?= $product['brinquedo_faixa_etaria'] === '5+ anos' ? 'selected' : '' ?>>5+ anos (Crianças maiores)</option>
                    </select>
                </div>
            </div>
        <?php endif; ?>

        <!-- Specific properties: Alimentos -->
        <?php if ($product['tipo'] === 'alimento'): ?>
            <div style="border-top: 1px solid var(--border-color); padding-top: 20px; margin-top: 25px; margin-bottom: 25px;">
                <h4 style="margin-bottom: 15px; color: var(--color-accent);">Atributos de Alimentos</h4>
                
                <div class="form-group">
                    <label class="form-label">Categoria de Alimento *</label>
                    <select name="categoria" class="form-control">
                        <option value="Não perecível" <?= $product['alimento_categoria'] === 'Não perecível' ? 'selected' : '' ?>>Não perecível</option>
                        <option value="Perecível" <?= $product['alimento_categoria'] === 'Perecível' ? 'selected' : '' ?>>Perecível</option>
                        <option value="Bebidas" <?= $product['alimento_categoria'] === 'Bebidas' ? 'selected' : '' ?>>Bebidas</option>
                        <option value="Outros" <?= $product['alimento_categoria'] === 'Outros' ? 'selected' : '' ?>>Outros</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Qualidade *</label>
                    <select name="qualidade" class="form-control">
                        <option value="Boa" <?= $product['alimento_qualidade'] === 'Boa' ? 'selected' : '' ?>>Boa (Embalagem intacta)</option>
                        <option value="Ruim" <?= $product['alimento_qualidade'] === 'Ruim' ? 'selected' : '' ?>>Ruim (Embalagem violada/Danificada)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Data de Validade *</label>
                    <input type="date" name="data_validade" class="form-control" value="<?= e($product['alimento_data_validade']) ?>">
                </div>
            </div>
        <?php endif; ?>

        <div style="display: flex; gap: 15px; border-top: 1px solid var(--border-color); padding-top: 20px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
            <a href="<?= url('products') ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
