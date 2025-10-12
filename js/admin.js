document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('graficoVotos').getContext('2d');
    const statusUpdate = document.getElementById('status-update');
    const legendaContainer = document.getElementById('graficoLegenda'); // Pega o container da legenda
    let graficoVotos;

    // Mapeamento fixo de Categoria para Cor.
    const coresPorCategoria = {
        'Administração': '#4285F4',
        'Logística': '#FBBC05',
        'Estética': '#E91E63',
        'Beleza': '#9C27B0',
        'Enfermagem': '#EA4335',
        'Turismo': '#FF9800',
        'Segurança do Trabalho': '#607D8B',
        'Informática': '#34A853',
        'default': '#CCCCCC'
    };

    // --- NOVA FUNÇÃO PARA CRIAR A LEGENDA ---
    function criarLegenda() {
        legendaContainer.innerHTML = ''; // Limpa a legenda antes de recriar
        // Pega todas as chaves (nomes das categorias) do objeto de cores, exceto 'default'
        const categorias = Object.keys(coresPorCategoria).filter(cat => cat !== 'default');
        
        categorias.forEach(categoria => {
            const cor = coresPorCategoria[categoria];
            
            const itemDiv = document.createElement('div');
            itemDiv.className = 'legenda-item';
            
            // Cria o HTML para cada item da legenda
            itemDiv.innerHTML = `
                <span class="legenda-cor" style="background-color: ${cor};"></span>
                <span>${categoria}</span>
            `;
            
            legendaContainer.appendChild(itemDiv);
        });
    }

    async function atualizarDados() {
        try {
            const response = await fetch('../php/dados_votos.php');
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            
            const dados = await response.json();
            
            if (!Array.isArray(dados)) throw new Error("Formato de dados inválido recebido do servidor.");

            statusUpdate.textContent = `Atualizado em: ${new Date().toLocaleTimeString()}`;

            const labels = dados.map(item => item.nome_grupo || 'Sem Nome');
            
            const totais = dados.map(item => item.total || 0);
            const coresDasBarras = dados.map(item => 
                coresPorCategoria[item.categoria_grupo] || coresPorCategoria['default']
            );

            if (!graficoVotos) {
                graficoVotos = criarGrafico(labels, totais, coresDasBarras);
            } else {
                graficoVotos.data.labels = labels;
                graficoVotos.data.datasets[0].data = totais;
                graficoVotos.data.datasets[0].backgroundColor = coresDasBarras;
                graficoVotos.update();
            }

        } catch (error) {
            console.error("Erro ao atualizar dados:", error);
            statusUpdate.textContent = "Erro ao carregar dados.";
        }
    }

    function criarGrafico(labels, totais, cores) {
        return new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Total de Votos',
                    data: totais,
                    backgroundColor: cores,
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: { label: (context) => `${context.raw} votos` }
                    }
                }
            }
        });
    }

    // --- CHAMA A FUNÇÃO PARA CRIAR A LEGENDA QUANDO A PÁGINA CARREGA ---
    criarLegenda();
    
    // Primeira chamada para buscar os dados e depois atualiza a cada 5 segundos
    atualizarDados();
    setInterval(atualizarDados, 5000);
});