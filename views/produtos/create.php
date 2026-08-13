<div class="card" style="max-width: 650px; margin: 0 auto;">
    <div class="card-header">
        <h3 class="card-title">📦 Cadastro de Lote de Produto</h3>
        <a href="/produtos" class="btn btn-secondary">Voltar</a>
    </div>

    <form action="/produtos/salvar" method="POST">
        <div class="form-group">
            <label for="turma_id" class="form-label">Turma Beneficiária / Responsável *</label>
            <select name="turma_id" id="turma_id" class="form-control" required>
                <?php foreach ($turmas as $t): ?>
                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nome'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="categoria" class="form-label">Categoria do Produto *</label>
            <select name="categoria" id="categoria" class="form-control" required>
                <option value="alimento">Alimentos</option>
                <option value="roupa">Roupas</option>
                <option value="brinquedo">Brinquedos</option>
                <option value="higiene">Higiene</option>
            </select>
        </div>

        <div class="form-group">
            <label for="quantidade" class="form-label">Quantidade Total (Unidades / Kilos) *</label>
            <input type="number" id="quantidade" name="quantidade" class="form-control" min="1" value="10" required>
        </div>

        <!-- GRUPO ROUPA -->
        <div id="group_roupa" style="display: none;">
            <div class="form-group">
                <label for="faixa_etaria_roupa" class="form-label">Faixa Etária</label>
                <select name="faixa_etaria" id="faixa_etaria_roupa" class="form-control">
                    <option value="bebe">Bebê</option>
                    <option value="crianca">Criança</option>
                    <option value="adolescente">Adolescente</option>
                    <option value="adulto">Adulto</option>
                </select>
            </div>
            <div class="form-group">
                <label for="qualidade_roupa" class="form-label">Estado de Conservação / Qualidade</label>
                <select name="qualidade" id="qualidade_roupa" class="form-control">
                    <option value="boa">Boa / Nova</option>
                    <option value="ruim">Com Danos / Descarte</option>
                </select>
            </div>
        </div>

        <!-- GRUPO BRINQUEDO -->
        <div id="group_brinquedo" style="display: none;">
            <div class="form-group">
                <label for="faixa_etaria_brinquedo" class="form-label">Faixa Etária Recomendada</label>
                <select name="faixa_etaria" id="faixa_etaria_brinquedo" class="form-control">
                    <option value="0-4">0 a 4 Anos</option>
                    <option value="5+">5+ Anos</option>
                </select>
            </div>
        </div>

        <!-- GRUPO ALIMENTO -->
        <div id="group_alimento" style="display: block;">
            <div class="form-group">
                <label for="tipo_alimento" class="form-label">Tipo de Alimento</label>
                <select name="tipo_alimento" id="tipo_alimento" class="form-control">
                    <option value="nao_perecivel">Não Perecível (Arroz, Feijão, Macarrão, etc.)</option>
                    <option value="perecivel">Perecível</option>
                    <option value="bebida">Bebidas</option>
                    <option value="outros">Outros</option>
                </select>
            </div>
            <div class="form-group">
                <label for="data_validade" class="form-label">Data de Validade (se houver)</label>
                <input type="date" id="data_validade" name="data_validade" class="form-control">
            </div>
        </div>

        <!-- GRUPO HIGIENE -->
        <div id="group_higiene" style="display: none;">
            <div class="form-group">
                <label for="descricao_higiene" class="form-label">Descrição dos Itens de Higiene</label>
                <input type="text" id="descricao_higiene" name="descricao" class="form-control" placeholder="Ex: Sabonete, Creme Dental, Fralda D'água">
            </div>
        </div>

        <button type="submit" class="btn btn-success" style="width: 100%; justify-content: center; padding: 12px;">Salvar Lote de Produtos</button>
    </form>
</div>
