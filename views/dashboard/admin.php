<?php
// ============================================================
// views/dashboard/admin.php — Painel Administrativo
// Natal Solidário JMF
// Renderizado pelo DashboardController::admin()
// O header/footer são injetados pelo Controller::render()
// ============================================================
?>

<!-- NAVEGAÇÃO — menu horizontal do painel admin -->
<nav class="admin-nav">
    <div class="admin-nav-links">
        <a href="?route=admin" class="admin-nav-link admin-nav-ativo">Dashboard</a>
        <a href="?route=classes" class="admin-nav-link">Turmas</a>
        <a href="?route=products" class="admin-nav-link">Produtos</a>
        <a href="?route=batches" class="admin-nav-link">Lotes / Estoque</a>
        <a href="?route=rifas" class="admin-nav-link">Rifas</a>
        <a href="?route=financeiro" class="admin-nav-link">Financeiro</a>
        <a href="?route=reports" class="admin-nav-link">Relatórios</a>
        <a href="?route=history" class="admin-nav-link">Histórico</a>
    </div>
    <a href="?route=dashboard" class="admin-nav-voltar">&#8592; Voltar ao Painel</a>
</nav>

<!-- CONTEÚDO PRINCIPAL -->
<main class="admin-main">

    <!-- Título da página -->
    <div class="admin-page-header">
        <h2 class="admin-page-titulo">Dashboard Administrativo</h2>
    </div>

    <!-- ======================================================
         INDICADORES
         Exibe "—" quando o valor for null (dados não disponíveis).
         Para conectar: substituir null em $indicadores[] pela query.
         ====================================================== -->
    <section class="admin-secao">
        <h3 class="admin-secao-titulo">Indicadores</h3>
        <div class="admin-cards-grid">
            <?php foreach ($cards as $card):
                $valor = $indicadores[$card['chave']];
            ?>
            <div class="admin-card" style="border-top-color: <?php echo $card['acento']; ?>;">
                <span class="admin-card-titulo"><?php echo htmlspecialchars($card['titulo']); ?></span>
                <?php echo formato_indicador($valor); ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ======================================================
         ARRECADAÇÃO POR CATEGORIA
         ====================================================== -->
    <section class="admin-secao">
        <h3 class="admin-secao-titulo">Arrecadação por Categoria</h3>

        <?php if (empty($grafico_categorias)): ?>
            <div class="admin-estado-vazio">
                <span class="admin-estado-vazio-icone">&#8212;</span>
                <p>Nenhum dado disponível</p>
            </div>
        <?php else:
            $max_cat   = max($grafico_categorias);
            $total_cat = array_sum($grafico_categorias);
        ?>
            <div class="admin-barras">
                <?php foreach ($grafico_categorias as $cat => $qtd):
                    $pct     = ($max_cat > 0)   ? round(($qtd / $max_cat)   * 100) : 0;
                    $pct_tot = ($total_cat > 0) ? round(($qtd / $total_cat) * 100) : 0;
                    $cor     = isset($cores_categorias[$cat]) ? $cores_categorias[$cat] : '#c0392b';
                ?>
                <div class="admin-barra-item">
                    <div class="admin-barra-cabecalho">
                        <span class="admin-barra-nome"><?php echo htmlspecialchars($cat); ?></span>
                        <span class="admin-barra-qtd">
                            <?php echo number_format($qtd, 0, ',', '.'); ?> itens
                            <span class="admin-barra-pct">(<?php echo $pct_tot; ?>%)</span>
                        </span>
                    </div>
                    <div class="admin-barra-track">
                        <div class="admin-barra-fill"
                             style="width: <?php echo $pct; ?>%; background-color: <?php echo $cor; ?>;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- ======================================================
         GRID: ARRECADAÇÃO POR TURMA + RANKING
         ====================================================== -->
    <div class="admin-grid-dois">

        <!-- Arrecadação por Turma -->
        <section class="admin-secao">
            <h3 class="admin-secao-titulo">Arrecadação por Turma</h3>

            <?php if (empty($grafico_turmas)): ?>
                <div class="admin-estado-vazio">
                    <span class="admin-estado-vazio-icone">&#8212;</span>
                    <p>Nenhum dado disponível</p>
                </div>
            <?php else:
                $max_turma = max($grafico_turmas);
            ?>
                <div class="admin-barras admin-barras-compact">
                    <?php foreach ($grafico_turmas as $turma => $pontos):
                        $pct = ($max_turma > 0) ? round(($pontos / $max_turma) * 100) : 0;
                    ?>
                    <div class="admin-barra-item">
                        <div class="admin-barra-cabecalho">
                            <span class="admin-barra-nome"><?php echo htmlspecialchars($turma); ?></span>
                            <span class="admin-barra-qtd"><?php echo number_format($pontos, 0, ',', '.'); ?> pts</span>
                        </div>
                        <div class="admin-barra-track">
                            <div class="admin-barra-fill"
                                 style="width: <?php echo $pct; ?>%; background-color: #c0392b;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Ranking das Turmas -->
        <section class="admin-secao">
            <h3 class="admin-secao-titulo">Ranking das Turmas</h3>

            <?php if (empty($ranking_turmas)): ?>
                <div class="admin-estado-vazio">
                    <span class="admin-estado-vazio-icone">&#8212;</span>
                    <p>Nenhum dado disponível</p>
                </div>
            <?php else: ?>
                <table class="admin-tabela">
                    <thead>
                        <tr>
                            <th>Pos.</th>
                            <th>Turma</th>
                            <th>Pontos</th>
                            <th>Doações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ranking_turmas as $item):
                            $pos = (int)$item['posicao'];
                            $cls = isset($classes_podio[$pos]) ? $classes_podio[$pos] : '';
                        ?>
                        <tr class="<?php echo $cls; ?>">
                            <td class="admin-rank-pos">
                                <span class="admin-rank-num <?php echo $cls; ?>"><?php echo $pos; ?>º</span>
                            </td>
                            <td class="admin-rank-turma"><?php echo htmlspecialchars($item['turma']); ?></td>
                            <td class="admin-rank-pontos"><?php echo number_format((int)$item['pontos'], 0, ',', '.'); ?></td>
                            <td class="admin-rank-doacoes"><?php echo (int)$item['doacoes']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <div class="admin-link-mais">
                <a href="?route=classes/points" class="admin-link-secundario">Ver ranking completo &rarr;</a>
            </div>
        </section>

    </div><!-- /admin-grid-dois -->

    <!-- ======================================================
         EVOLUÇÃO DAS ARRECADAÇÕES
         ====================================================== -->
    <section class="admin-secao">
        <h3 class="admin-secao-titulo">Evolução das Arrecadações</h3>
        <div class="admin-estado-vazio admin-estado-vazio-lg">
            <span class="admin-estado-vazio-icone">&#8212;</span>
            <p>Nenhum dado disponível</p>
        </div>
    </section>

    <!-- ======================================================
         GRID: ALERTAS + RELATÓRIOS
         ====================================================== -->
    <div class="admin-grid-dois">

        <!-- Alertas e Pendências -->
        <section class="admin-secao">
            <h3 class="admin-secao-titulo">Alertas e Pend&ecirc;ncias</h3>

            <?php if (empty($alertas)): ?>
                <p class="admin-sem-alertas">Nenhum alerta no momento.</p>
            <?php else: ?>
                <div class="admin-alertas-lista">
                    <?php foreach ($alertas as $alerta):
                        $simbolo = isset($simbolos_alerta[$alerta['tipo']]) ? $simbolos_alerta[$alerta['tipo']] : 'i';
                        $tipo    = htmlspecialchars($alerta['tipo']);
                    ?>
                    <div class="admin-alerta-item admin-alerta-<?php echo $tipo; ?>">
                        <span class="admin-alerta-simbolo"><?php echo $simbolo; ?></span>
                        <span><?php echo htmlspecialchars($alerta['mensagem']); ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Relatórios -->
        <section class="admin-secao">
            <h3 class="admin-secao-titulo">Relat&oacute;rios</h3>

            <div class="admin-relatorios-grid">
                <?php foreach ($relatorios as $rel): ?>
                    <?php if ($rel['ativo']): ?>
                        <a href="<?php echo htmlspecialchars($rel['href']); ?>"
                           class="admin-relatorio-card">
                            <span class="admin-relatorio-titulo"><?php echo htmlspecialchars($rel['titulo']); ?></span>
                            <span class="admin-relatorio-desc"><?php echo htmlspecialchars($rel['descricao']); ?></span>
                        </a>
                    <?php else: ?>
                        <span class="admin-relatorio-card admin-relatorio-desabilitado"
                              title="Em desenvolvimento">
                            <span class="admin-relatorio-titulo"><?php echo htmlspecialchars($rel['titulo']); ?></span>
                            <span class="admin-relatorio-desc"><?php echo htmlspecialchars($rel['descricao']); ?></span>
                        </span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </section>

    </div><!-- /admin-grid-dois -->

</main>
