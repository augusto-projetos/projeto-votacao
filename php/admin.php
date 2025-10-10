<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Se a sessão de admin não existir ou não for verdadeira, nega o acesso.
    header("Location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Painel do Administrador</title>
</head>
<body>
    <div class="page-container">
        <header class="admin-header">
            <h1>Painel de Votos</h1>
            <p id="status-update">Carregando dados...</p>
        </header>

        <div class="admin-actions">
            <a href="gerenciar_grupos.php" class="btn-action">Gerenciar Grupos</a>
            <a href="gerenciar_usuarios.php" class="btn-action btn-secondary">Gerenciar Usuários</a>
        </div>

        <main class="dashboard">
            <section class="card" id="grafico-card">
                <h2>Gráfico de Votação</h2>
                <div class="chart-wrapper">
                    <canvas id="graficoVotos"></canvas>
                </div>
                <div id="graficoLegenda" class="grafico-legenda">
                    <!-- A legenda será gerada pelo JavaScript -->
                </div>
                <!-- ============================================= -->
            </section>
        </main>
        
        <a href="../index.php" class="voltar">← Voltar para a página inicial</a>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> - Sistema de Votação. Todos os direitos reservados.</p>
        <p>Desenvolvido por <strong>Luiz A. L. Freitas</strong></p>
        <p id="feat">feat Yan C. N. de Paiva & Eduardo C. S. Junior & Isabelly A. Silva</p>
    </footer>

    <script src="../js/admin.js"></script>
</body>
</html>