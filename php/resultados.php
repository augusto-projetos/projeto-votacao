<?php
require 'conexao.php';

// Busca os votos, ordenando pelo mais votado (ranking) e desempatando por nome
$sql = "SELECT nome_grupo, categoria_grupo, COUNT(*) as total 
        FROM votos 
        GROUP BY nome_grupo, categoria_grupo 
        ORDER BY total DESC, nome_grupo ASC";

$result = $conn->query($sql);
$votos = $result->fetch_all(MYSQLI_ASSOC);

// Lógica para encontrar o(s) vencedor(es)
$vencedores = [];
$max_votos = 0;
if (!empty($votos)) {
    // Pega o total de votos do primeiro item (que é o mais votado)
    $max_votos = $votos[0]['total'];
    
    // Filtra todos os grupos que têm o mesmo número máximo de votos (para tratar empates)
    foreach ($votos as $voto) {
        if ($voto['total'] == $max_votos) {
            $vencedores[] = $voto;
        } else {
            // Como a lista está ordenada, podemos parar assim que encontrarmos um com menos votos
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados da Votação</title>
    <link rel="stylesheet" href="../css/detalhes.css">
    <link rel="stylesheet" href="../css/resultados.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <header>
        <h1>Votação Feira Pro 2025</h1>
        <img src="../img/logo.jpg" alt="Logo" id="logo">
    </header>

    <main class="container">
        <h1 class="titulo-principal">Resultado da Votação</h1>

        <!-- Seção do Vencedor(es) -->
        <?php if (!empty($vencedores)): ?>
        <section class="winner-card">
            <div class="winner-icon">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M19.5,9H17.22l-2.4-5.4A1,1,0,0,0,14,3H10a1,1,0,0,0-.85.4L6.78,9H4.5a1,1,0,0,0-1,1.22l2.6,9.37A1,1,0,0,0,7,21H17a1,1,0,0,0,.9-1.41l2.6-9.37A1,1,0,0,0,19.5,9Zm-3.35,10H7.85l-2-7.18h2.09l2.4-5.4h3.32l2.4,5.4h2.09Z"/></svg>
            </div>
            <h2><?= count($vencedores) > 1 ? 'Vencedores' : 'Vencedor' ?></h2>
            <?php foreach ($vencedores as $vencedor): ?>
                <p class="winner-name"><?= htmlspecialchars($vencedor['nome_grupo']) ?></p>
            <?php endforeach; ?>
            <p class="winner-votes">com <strong><?= $max_votos ?></strong> voto(s)</p>
        </section>
        <?php endif; ?>

        <!-- Seção do Gráfico Completo -->
        <section class="chart-container">
            <h2>Ranking Geral</h2>
            <div class="chart-wrapper">
                <canvas id="graficoResultados"></canvas>
            </div>
            <div id="graficoLegenda" class="grafico-legenda"></div>
        </section>
        
        <a href="../index.php" class="btn-voltar">← Voltar para a página inicial</a>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> - Sistema de Votação. Todos os direitos reservados.</p>
        <p>Desenvolvido por <strong>Luiz A. L. Freitas</strong></p>
        <p id="feat">feat Yan C. N. de Paiva & Eduardo C. S. Junior & Isabelly A. Silva</p>
    </footer>

    <script>
        // Transfere os dados do PHP para o JavaScript
        const dadosVotacao = <?php echo json_encode($votos); ?>;

        document.addEventListener('DOMContentLoaded', function () {
            if (!dadosVotacao || dadosVotacao.length === 0) return;

            const ctx = document.getElementById('graficoResultados').getContext('2d');
            const legendaContainer = document.getElementById('graficoLegenda');

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

            // Gera a legenda dinamicamente
            Object.keys(coresPorCategoria).filter(cat => cat !== 'default').forEach(categoria => {
                const cor = coresPorCategoria[categoria];
                legendaContainer.innerHTML += `
                    <div class="legenda-item">
                        <span class="legenda-cor" style="background-color: ${cor};"></span>
                        <span>${categoria}</span>
                    </div>`;
            });

            // Prepara os dados para o gráfico
            const labels = dadosVotacao.map(item => item.nome_grupo || 'Sem Nome');
            const totais = dadosVotacao.map(item => item.total || 0);
            const coresDasBarras = dadosVotacao.map(item => coresPorCategoria[item.categoria_grupo] || coresPorCategoria['default']);

            // Cria o gráfico
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Total de Votos',
                        data: totais,
                        backgroundColor: coresDasBarras,
                        borderRadius: 4,
                    }]
                },
                options: {
                    indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                    scales: { x: { beginAtZero: true, ticks: { precision: 0 } } },
                    plugins: { legend: { display: false }, tooltip: { callbacks: { label: (context) => `${context.raw} votos` } } }
                }
            });
        });
    </script>
</body>
</html>