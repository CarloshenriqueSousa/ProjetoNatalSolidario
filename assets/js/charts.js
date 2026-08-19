/**
 * Natal Solidário - Pure Vanilla JS SVG Chart Engine
 */
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Donut Chart: Product Distribution
    const donutContainer = document.getElementById('type_donut_chart');
    if (donutContainer) {
        const roupas = parseInt(donutContainer.getAttribute('data-roupas') || 0);
        const brinquedos = parseInt(donutContainer.getAttribute('data-brinquedos') || 0);
        const alimentos = parseInt(donutContainer.getAttribute('data-alimentos') || 0);
        
        const total = roupas + brinquedos + alimentos;
        
        if (total === 0) {
            donutContainer.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--text-secondary);">Nenhum dado cadastrado para exibir no gráfico.</div>';
        } else {
            // Draw SVG Donut
            const data = [
                { label: 'Roupas', value: roupas, color: '#f43f5e' },
                { label: 'Brinquedos', value: brinquedos, color: '#6366f1' },
                { label: 'Alimentos', value: alimentos, color: '#10b981' }
            ];
            
            let svgHtml = `<svg viewBox="0 0 200 200" class="chart-svg">`;
            
            let accumulatedPercent = 0;
            const radius = 70;
            const cx = 100;
            const cy = 100;
            const circumference = 2 * Math.PI * radius;
            
            data.forEach(item => {
                if (item.value > 0) {
                    const percent = item.value / total;
                    const strokeDasharray = `${percent * circumference} ${circumference}`;
                    const strokeDashoffset = -accumulatedPercent * circumference;
                    
                    svgHtml += `
                        <circle cx="${cx}" cy="${cy}" r="${radius}" 
                                fill="transparent" 
                                stroke="${item.color}" 
                                stroke-width="25" 
                                stroke-dasharray="${strokeDasharray}" 
                                stroke-dashoffset="${strokeDashoffset}"
                                transform="rotate(-90 ${cx} ${cy})"
                                class="bar-hover"
                                title="${item.label}: ${item.value} (${Math.round(percent * 100)}%)">
                        </circle>
                    `;
                    accumulatedPercent += percent;
                }
            });
            
            // Add central text & inner cutout
            svgHtml += `
                <circle cx="${cx}" cy="${cy}" r="50" fill="var(--bg-card)"></circle>
                <text x="${cx}" y="${cy - 5}" text-anchor="middle" fill="#fff" font-size="16" font-weight="700">${total}</text>
                <text x="${cx}" y="${cy + 15}" text-anchor="middle" fill="var(--text-secondary)" font-size="10" font-weight="500">PRODUTOS</text>
            </svg>`;
            
            // Add custom legend
            let legendHtml = '<div style="display: flex; justify-content: space-around; margin-top: 15px; font-size: 13px;">';
            data.forEach(item => {
                const pct = total > 0 ? Math.round((item.value / total) * 100) : 0;
                legendHtml += `
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="display: inline-block; width: 12px; height: 12px; border-radius: 3px; background-color: ${item.color};"></span>
                        <span>${item.label}: <strong>${item.value}</strong> <small style="color: var(--text-secondary)">(${pct}%)</small></span>
                    </div>
                `;
            });
            legendHtml += '</div>';
            
            donutContainer.innerHTML = svgHtml + legendHtml;
        }
    }

    // 2. Bar Chart: Classroom Leaderboard Points
    const barContainer = document.getElementById('ranking_bar_chart');
    if (barContainer) {
        const rawData = barContainer.getAttribute('data-ranking');
        if (rawData) {
            try {
                const rankings = JSON.parse(rawData);
                
                if (!rankings || rankings.length === 0) {
                    barContainer.innerHTML = '<div style="display: flex; align-items: center; justify-content: center; height: 100%; color: var(--text-secondary);">Nenhuma turma com pontos cadastrados.</div>';
                } else {
                    // Maximum points for scale
                    const maxPoints = Math.max(...rankings.map(r => parseInt(r.total_pontos) || 0), 1);
                    
                    let svgHtml = `<svg viewBox="0 0 500 240" class="chart-svg" style="background: transparent;">`;
                    
                    const barHeight = 24;
                    const gap = 12;
                    const xStart = 100; // Leave space for labels
                    const widthMax = 350; // Scale length of bars
                    
                    rankings.forEach((turma, index) => {
                        const pts = parseInt(turma.total_pontos) || 0;
                        const qty = parseInt(turma.total_quantidade) || 0;
                        const pctWidth = pts / maxPoints;
                        const width = pctWidth * widthMax;
                        const y = index * (barHeight + gap) + 15;
                        
                        // Select color based on rank position
                        let barColor = '#6366f1'; // Indigo base
                        if (index === 0) barColor = '#fbbf24'; // Gold
                        else if (index === 1) barColor = '#94a3b8'; // Silver
                        else if (index === 2) barColor = '#b45309'; // Bronze
                        
                        svgHtml += `
                            <!-- Classroom Label -->
                            <text x="${xStart - 10}" y="${y + 16}" text-anchor="end" fill="var(--text-secondary)" font-size="11" font-weight="600">${turma.nome}</text>
                            
                            <!-- Background track bar -->
                            <rect x="${xStart}" y="${y}" width="${widthMax}" height="${barHeight}" rx="4" fill="rgba(255,255,255,0.02)" stroke="var(--border-color)"></rect>
                            
                            <!-- Filled rank bar with animation -->
                            <rect x="${xStart}" y="${y}" width="0" height="${barHeight}" rx="4" fill="${barColor}" class="bar-hover">
                                <animate attributeName="width" from="0" to="${width}" dur="0.8s" fill="freeze" />
                            </rect>
                            
                            <!-- Points display text -->
                            <text x="${xStart + width + 10}" y="${y + 16}" fill="#fff" font-size="11" font-weight="700">${pts} pts <tspan font-weight="500" fill="var(--text-secondary)">(${qty} itens)</tspan></text>
                        `;
                    });
                    
                    svgHtml += `</svg>`;
                    barContainer.innerHTML = svgHtml;
                }
            } catch (err) {
                console.error("Erro ao desenhar o gráfico de turmas: ", err);
            }
        }
    }
});
