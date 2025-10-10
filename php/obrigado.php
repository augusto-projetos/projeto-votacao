<?php
session_start();
require 'conexao.php';
date_default_timezone_set('America/Sao_Paulo');

// Verifica se tem CPF na sessão, se não redireciona para o início
if (!isset($_SESSION['cpf'])) {
    header("Location: index.php");
    exit();
}

$cpf = $_SESSION['cpf'];

// Você pode buscar o nome do usuário se quiser exibir
$stmt = $conn->prepare("SELECT nome FROM usuarios WHERE cpf = ?");
$stmt->bind_param("s", $cpf);
$stmt->execute();
$result = $stmt->get_result();

$nome = '';
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $nome = $row['nome'];
}

?>

<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../css/detalhes.css">
        <link rel="stylesheet" href="../css/obrigado.css">
        <title>Obrigado por Votar</title>
    </head>
    <body>
        <header>
            <h1>Votação Feira Pro 2025</h1>
            <img src="../img/logo.jpg" alt="Logo" id="logo">
        </header>

        <main>
            <div id="obrigado">
                <h1 id="h1obrigado">Obrigado por votar, <?php echo htmlspecialchars($nome ?: 'participante'); ?>!</h1>
                <p>Seu voto foi registrado com sucesso.</p>
                <p>CPF: <?php echo htmlspecialchars($cpf); ?></p>

                <a href="votar.php">Voltar para a página de voto</a>
            </div>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> - Sistema de Votação. Todos os direitos reservados.</p>
            <p>Desenvolvido por <strong>Luiz A. L. Freitas</strong></p>
            <p id="feat">feat Yan C. N. de Paiva & Eduardo C. S. Junior & Isabelly A. Silva</p>
        </footer>
    </body>
</html>