const FLUXO_MES = JSON.parse(
    document.getElementById('fluxo-data').textContent
);

(function () {
    const dados = FLUXO_MES;

    const labels = dados.map(d => d.meses);
    const vendas = dados.map(d => parseFloat(d.totalVendas) || 0);
    const despesas = dados.map(d => parseFloat(d.totalDespesas) || 0);
    const repasses = dados.map(d => parseFloat(d.totalRepasses) || 0);

    const ctx = document.getElementById('fluxoChart').getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Vendas', data: vendas, stack: 'stack1' },
                { label: 'Despesas', data: despesas, stack: 'stack1' },
                { label: 'Repasses', data: repasses, stack: 'stack1' }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: {
                    title: {
                        display: true,
                        text: 'R$'
                    }
                }
            }
        }
    });
})();