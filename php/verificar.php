<?php
session_start();
require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $cpf = trim($_POST['cpf']);

    // Armazena o CPF na sessão para uso geral
    $_SESSION['cpf'] = $cpf;

    // Procura pelo usuário no banco de dados
    $stmt = $conn->prepare("SELECT id, is_admin FROM usuarios WHERE cpf = ?");
    $stmt->bind_param("s", $cpf);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();

    // Se o usuário não existir, cria um novo registro com is_admin = 0
    if (!$usuario) {
        $stmt_insert = $conn->prepare("INSERT INTO usuarios (nome, cpf, is_admin) VALUES (?, ?, 0)");
        $stmt_insert->bind_param("ss", $nome, $cpf);
        $stmt_insert->execute();
        $is_admin = 0; // O novo usuário não é admin
    } else {
        $is_admin = $usuario['is_admin'];
    }

    // VERIFICAÇÃO DE ADMIN
    if ($is_admin == 1) {
        // Se for admin, define uma variável de sessão de segurança e redireciona
        $_SESSION['is_admin'] = true;
        header("Location: admin.php");
        exit();
    } else {
        // Se não for admin, garante que a variável de sessão não exista
        unset($_SESSION['is_admin']);
        header("Location: votar.php");
        exit();
    }
}
?>