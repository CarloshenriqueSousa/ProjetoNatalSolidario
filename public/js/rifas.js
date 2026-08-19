// Cálculos dinâmicos em tempo real para o módulo de Rifas
document.addEventListener('DOMContentLoaded', () => {
    const inputEntregue = document.getElementById('quantidade_entregue');
    const inputUnitario = document.getElementById('valor_unitario');
    const spanValorEsperadoTotal = document.getElementById('valor_esperado_total');

    // Cálculo em tempo real na criação de lote
    if (inputEntregue && inputUnitario && spanValorEsperadoTotal) {
        const calcularTotal = () => {
            const qtd = parseInt(inputEntregue.value) || 0;
            const unit = parseFloat(inputUnitario.value) || 0;
            const total = qtd * unit;
            spanValorEsperadoTotal.textContent = total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        };

        inputEntregue.addEventListener('input', calcularTotal);
        inputUnitario.addEventListener('input', calcularTotal);
        calcularTotal();
    }

    // Cálculo em tempo real na Prestação de Contas
    const inputVendida = document.getElementById('quantidade_vendida');
    const inputDevolvida = document.getElementById('quantidade_devolvida');
    const inputPerdida = document.getElementById('quantidade_perdida');
    const inputValorEntregue = document.getElementById('valor_entregue');

    const spanSomaQtd = document.getElementById('calc_soma_quantidades');
    const spanValorCalculado = document.getElementById('calc_valor_calculado');
    const spanDiferenca = document.getElementById('calc_diferenca');
    const divStatusBadge = document.getElementById('calc_status_badge');

    const metaQtdEntregue = parseInt(document.getElementById('meta_quantidade_entregue')?.value || '0');
    const metaValorUnitario = parseFloat(document.getElementById('meta_valor_unitario')?.value || '0');

    if (inputVendida && inputDevolvida && inputPerdida && inputValorEntregue) {
        const atualizarPrestacaoRealtime = () => {
            const qVend = parseInt(inputVendida.value) || 0;
            const qDev = parseInt(inputDevolvida.value) || 0;
            const qPerd = parseInt(inputPerdida.value) || 0;
            const vEntregue = parseFloat(inputValorEntregue.value) || 0;

            const somaQtd = qVend + qDev + qPerd;
            const valorCalculado = qVend * metaValorUnitario;
            const diferenca = vEntregue - valorCalculado;

            if (spanSomaQtd) {
                spanSomaQtd.textContent = `${somaQtd} / ${metaQtdEntregue}`;
                spanSomaQtd.style.color = (somaQtd === metaQtdEntregue) ? '#2ecc71' : '#e74c3c';
            }

            if (spanValorCalculado) {
                spanValorCalculado.textContent = valorCalculado.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
            }

            if (spanDiferenca) {
                spanDiferenca.textContent = diferenca.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                spanDiferenca.style.color = (diferenca === 0) ? '#2ecc71' : (diferenca > 0 ? '#f39c12' : '#e74c3c');
            }

            if (divStatusBadge) {
                if (somaQtd === metaQtdEntregue && diferenca === 0 && qPerd === 0) {
                    divStatusBadge.className = 'badge badge-success';
                    divStatusBadge.textContent = 'Prestação Perfeita (Sem Divergências)';
                } else {
                    divStatusBadge.className = 'badge badge-warning';
                    divStatusBadge.textContent = 'Prestação com Divergência Detectada';
                }
            }
        };

        inputVendida.addEventListener('input', atualizarPrestacaoRealtime);
        inputDevolvida.addEventListener('input', atualizarPrestacaoRealtime);
        inputPerdida.addEventListener('input', atualizarPrestacaoRealtime);
        inputValorEntregue.addEventListener('input', atualizarPrestacaoRealtime);
        atualizarPrestacaoRealtime();
    }
});
