<div class="card" style="max-width: 800px; margin: 0 auto;">
    <h3 class="card-title" style="border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 25px;">Registrar Nova Doação Arrecadada</h3>

    <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <span><?= e($error) ?></span>
        </div>
    <?php endif; ?>

    <form action="<?= url('products/create') ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <!-- General attributes -->
        <div class="form-group">
            <label class="form-label">Tipo de Categoria *</label>
            <select name="tipo" id="tipo_produto_select" class="form-control" required>
                <option value="">Selecione a Categoria</option>
                <option value="roupa">Roupas (Vestuário)</option>
                <option value="brinquedo">Brinquedos</option>
                <option value="alimento">Alimentos (Alimentação)</option>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label">Nome do Produto *</label>
            <input type="text" name="nome" class="form-control" placeholder="Ex: Arroz Tio João 5kg, Boneca Barbie, Camisa polo G" required>
        </div>

        <div class="form-group">
            <label class="form-label">Quantidade Inicial *</label>
            <input type="number" name="quantidade" class="form-control" min="1" placeholder="Ex: 10" required>
        </div>

        <div class="form-group">
            <label class="form-label">Lote Associado *</label>
            <select name="lote_id" class="form-control" required>
                <option value="">Selecione o Lote</option>
                <?php foreach ($batches as $b): ?>
                    <option value="<?= $b['id'] ?>"><?= e($b['codigo']) ?> - (Criado por: <?= e($b['responsavel_nome']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if (!$isClass): ?>
        <div class="form-group">
            <label class="form-label">Turma Responsável *</label>
            <select name="turma_id" class="form-control" required>
                <option value="">Selecione a Turma</option>
                <?php foreach ($classes as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= e($c['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <!-- Dynamic properties: Roupas -->
        <div id="fields_roupa" class="dynamic-section">
            <div style="border-top: 1px solid var(--border-color); padding-top: 20px; margin-top: 25px; margin-bottom: 25px;">
                <h4 style="margin-bottom: 15px; color: var(--color-accent);">Atributos de Roupas</h4>
                
                <div class="form-group">
                    <label class="form-label">Qualidade *</label>
                    <select name="qualidade" class="form-control">
                        <option value="Boa">Boa (Pronta para uso)</option>
                        <option value="Ruim">Ruim (Danificada/Sem condições)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Faixa Etária *</label>
                    <select name="faixa_etaria" class="form-control">
                        <option value="Bebê">Bebê</option>
                        <option value="Criança">Criança</option>
                        <option value="Adolescente">Adolescente</option>
                        <option value="Adulto">Adulto</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Dynamic properties: Brinquedos -->
        <div id="fields_brinquedo" class="dynamic-section">
            <div style="border-top: 1px solid var(--border-color); padding-top: 20px; margin-top: 25px; margin-bottom: 25px;">
                <h4 style="margin-bottom: 15px; color: var(--color-accent);">Atributos de Brinquedos</h4>
                
                <div class="form-group">
                    <label class="form-label">Faixa Etária Recomendada *</label>
                    <select name="faixa_etaria" class="form-control">
                        <option value="0-4 anos">0 a 4 anos (Primeira infância)</option>
                        <option value="5+ anos">5+ anos (Crianças maiores)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Dynamic properties: Alimentos -->
        <div id="fields_alimento" class="dynamic-section">
            <div style="border-top: 1px solid var(--border-color); padding-top: 20px; margin-top: 25px; margin-bottom: 25px;">
                <h4 style="margin-bottom: 15px; color: var(--color-accent);">Atributos de Alimentos</h4>
                
                <div class="form-group">
                    <label class="form-label">Categoria de Alimento *</label>
                    <select name="categoria" class="form-control">
                        <option value="Não perecível">Não perecível (Grãos, enlatados)</option>
                        <option value="Perecível">Perecível (Frutas, laticínios)</option>
                        <option value="Bebidas">Bebidas</option>
                        <option value="Outros">Outros</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Qualidade *</label>
                    <select name="qualidade" class="form-control">
                        <option value="Boa">Boa (Embalagem intacta)</option>
                        <option value="Ruim">Ruim (Embalagem violada/Danificada)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Data de Validade *</label>
                    <input type="date" name="data_validade" class="form-control">
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 15px; border-top: 1px solid var(--border-color); padding-top: 20px; margin-top: 30px;">
            <button type="submit" class="btn btn-primary">Salvar Produto</button>
            <a href="<?= url('products') ?>" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
