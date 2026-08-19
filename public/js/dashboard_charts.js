/**
 * Natal Solidário — Dashboard SVG Charts & Dynamic Updates Engine
 */
document.addEventListener('DOMContentLoaded', function() {

    // 1. Gráfico Donut de Produtos por Categoria
    renderCategoryDonutChart();

    // 2. Gráfico de Status de Rifas
    renderRifaStatusChart();

    // 3. Gráfico de Evolução de Doações
    renderDonationEvolutionChart();
});

function renderCategoryDonutChart() {
    const container = document.getElementById('chart_categorias');
    if (!container) return;

    const rawData = container.getAttribute('data-json');
    if (!rawData) return;

    try {
        const categories = JSON.parse(rawData);
        if (!categories || categories.length === 0) {
            container.innerHTML = '<div class="chart-empty">Nenhum produto cadastrado ainda.</div>';
            return;
        }

        const colorMap = {
            'alimento': '#10b981',   // Emerald Green
            'roupa': '#f43f5e',      // Red
            'brinquedo': '#6366f1',  // Indigo
            'higiene': '#0ea5e9'     // Sky Blue
        };

        const labelMap = {
            'alimento': 'Alimentos',
            'roupa': 'Roupas',
            'brinquedo': 'Brinquedos',
            'higiene': 'Higiene'
        };

        let total = 0;
        categories.forEach(c => { total += parseInt(c.total_quantidade) || 0; });

        if (total === 0) {
            container.innerHTML = '<div class="chart-empty">Nenhum produto cadastrado ainda.</div>';
            return;
        }

        const cx = 100, cy = 100, radius = 70;
        const circumference = 2 * Math.PI * radius;
        let accumulatedPercent = 0;

        let svgHtml = `<svg viewBox="0 0 200 200" class="chart-svg">`;

        categories.forEach(item => {
            const qty = parseInt(item.total_quantidade) || 0;
            if (qty > 0) {
                const percent = qty / total;
                const strokeDasharray = `${percent * circumference} ${circumference}`;
                const strokeDashoffset = -accumulatedPercent * circumference;
                const color = colorMap[item.categoria] || '#94a3b8';
                const label = labelMap[item.categoria] || item.categoria;

                svgHtml += `
                    <circle cx="${cx}" cy="${cy}" r="${radius}"
                            fill="transparent"
                            stroke="${color}"
                            stroke-width="25"
                            stroke-dasharray="${strokeDasharray}"
                            stroke-dashoffset="${strokeDashoffset}"
                            transform="rotate(-90 ${cx} ${cy})"
                            class="bar-hover"
                            title="${label}: ${qty} itens (${Math.round(percent * 100)}%)">
                    </circle>
                `;
                accumulatedPercent += percent;
            }
        });

        svgHtml += `
            <circle cx="${cx}" cy="${cy}" r="50" fill="var(--bg-card, #161c2d)"></circle>
            <text x="${cx}" y="${cy - 4}" text-anchor="middle" fill="#fff" font-size="16" font-weight="700">${total}</text>
            <text x="${cx}" y="${cy + 14}" text-anchor="middle" fill="#94a3b8" font-size="9" font-weight="600">ITENS</text>
        </svg>`;

        let legendHtml = '<div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; margin-top: 10px; font-size: 12px;">';
        categories.forEach(item => {
            const qty = parseInt(item.total_quantidade) || 0;
            const pct = Math.round((qty / total) * 100);
            const color = colorMap[item.categoria] || '#94a3b8';
            const label = labelMap[item.categoria] || item.categoria;
            legendHtml += `
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="display: inline-block; width: 10px; height: 10px; border-radius: 2px; background-color: ${color};"></span>
                    <span>${label}: <strong>${qty}</strong> <small style="color: #94a3b8">(${pct}%)</small></span>
                </div>
            `;
        });
        legendHtml += '</div>';

        container.innerHTML = svgHtml + legendHtml;
    } catch (e) {
        console.error("Erro ao renderizar gráfico de categorias: ", e);
    }
}

function renderRifaStatusChart() {
    const container = document.getElementById('chart_rifa_status');
    if (!container) return;

    const rawData = container.getAttribute('data-json');
    if (!rawData) return;

    try {
        const statusData = JSON.parse(rawData);
        if (!statusData || statusData.length === 0) {
            container.innerHTML = '<div class="chart-empty">Nenhum lote de rifa registrado.</div>';
            return;
        }

        const colorMap = {
            'entregue': '#6366f1',
            'prestacao_realizada': '#10b981',
            'com_divergencia': '#ef4444',
            'em_atraso': '#f59e0b',
            'finalizado': '#0ea5e9'
        };

        const labelMap = {
            'entregue': 'Em Andamento',
            'prestacao_realizada': 'Prestação OK',
            'com_divergencia': 'Divergência',
            'em_atraso': 'Em Atraso',
            'finalizado': 'Finalizado'
        };

        let totalLotes = 0;
        statusData.forEach(s => { totalLotes += parseInt(s.total) || 0; });

        let html = '<div style="display: flex; flex-direction: column; gap: 10px; width: 100%; padding: 10px 0;">';
        statusData.forEach(item => {
            const count = parseInt(item.total) || 0;
            const pct = Math.round((count / totalLotes) * 100);
            const color = colorMap[item.status] || '#64748b';
            const label = labelMap[item.status] || item.status;

            html += `
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 4px;">
                        <span><strong style="color: ${color};">●</strong> ${label}</span>
                        <span><strong>${count} lotes</strong> <small style="color: #94a3b8">(${pct}%)</small></span>
                    </div>
                    <div style="height: 8px; background: rgba(255,255,255,0.05); border-radius: 4px; overflow: hidden;">
                        <div style="width: ${pct}%; height: 100%; background: ${color}; transition: width 0.6s ease;"></div>
                    </div>
                </div>
            `;
        });
        html += '</div>';

        container.innerHTML = html;
    } catch (e) {
        console.error("Erro ao renderizar gráfico de status de rifas: ", e);
    }
}

function renderDonationEvolutionChart() {
    const container = document.getElementById('chart_evolucao_doacoes');
    if (!container) return;

    const rawData = container.getAttribute('data-json');
    if (!rawData) return;

    try {
        const days = JSON.parse(rawData);
        if (!days || days.length === 0) {
            container.innerHTML = '<div class="chart-empty">Nenhum registro nos últimos 30 dias.</div>';
            return;
        }

        const maxVal = Math.max(...days.map(d => parseInt(d.total_itens) || 0), 1);
        const height = 180;
        const width = 480;
        const barWidth = Math.max(12, Math.floor((width - 40) / days.length) - 6);

        let svgHtml = `<svg viewBox="0 0 ${width} ${height + 40}" class="chart-svg">`;

        days.forEach((day, idx) => {
            const val = parseInt(day.total_itens) || 0;
            const h = Math.round((val / maxVal) * height);
            const x = 30 + idx * (barWidth + 6);
            const y = height - h + 10;
            const dateParts = day.data_registro.split('-');
            const dateFormatted = `${dateParts[2]}/${dateParts[1]}`;

            svgHtml += `
                <rect x="${x}" y="${y}" width="${barWidth}" height="${h}" rx="3" fill="#6366f1" class="bar-hover" title="${dateFormatted}: ${val} itens">
                    <animate attributeName="height" from="0" to="${h}" dur="0.5s" fill="freeze" />
                    <animate attributeName="y" from="${height + 10}" to="${y}" dur="0.5s" fill="freeze" />
                </rect>
                <text x="${x + barWidth/2}" y="${height + 25}" text-anchor="middle" fill="#94a3b8" font-size="9">${dateFormatted}</text>
                ${val > 0 ? `<text x="${x + barWidth/2}" y="${y - 4}" text-anchor="middle" fill="#fff" font-size="9" font-weight="bold">${val}</text>` : ''}
            `;
        });

        svgHtml += `</svg>`;
        container.innerHTML = svgHtml;
    } catch (e) {
        console.error("Erro ao renderizar gráfico de evolução: ", e);
    }
}
