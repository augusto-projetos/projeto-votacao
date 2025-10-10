<?php
session_start();

require 'php/conexao.php';
date_default_timezone_set('America/Sao_Paulo');

?>

<!DOCTYPE html>
<html lang="pt-br">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="css/detalhes.css">
        <link rel="stylesheet" href="css/index.css">
        <title>Sistema de Votação</title>
    </head>
    <body>
        <header>
            <h1>Votação Feira Pro 2025</h1>
            <img src="img/logo.jpg" alt="Logo" id="logo">
        </header>

        <div id="form">
            <h1>Acesse o Sistema de Votação:</h1>
            <form action="php/verificar.php" method="post" id="formulario">
                <input type="text" name="nome" id="nomeForm" placeholder="Escreva seu Nome e Sobrenome" maxlength="30" required><br>

                <input type="tel" name="cpf" id="cpf" placeholder="000.000.000-00" onclick="adicionarCpf()" required><br>

                <button id="btn-form">Enviar</button>
            </form>
        </div>

        <script src="js/index.js"></script>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> - Sistema de Votação. Todos os direitos reservados.</p>
            <p>Desenvolvido por <strong>Luiz A. L. Freitas</strong></p>
            <p id="feat">feat Yan C. N. de Paiva & Eduardo C. S. Junior & Isabelly A. Silva</p>
        </footer>
    </body>
</html>