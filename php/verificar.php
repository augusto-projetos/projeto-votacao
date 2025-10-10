<?php
session_start();
require 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome']);
    $cpf = trim($_POST['cpf']);

    $_SESSION['cpf'] = $cpf;

    // CPFs autorizados como administradores
    $admins = ["000.000.000-00"]; // Adicione o CPF do Administrador aqui
    $_SESSION['admins'] = $admins;

    // Se for admin, vai direto para admin.php
    if (in_array($cpf, $admins)) {
        header("Location: admin.php");
        exit();
    }

    // Verifica se o CPF já existe no banco
    $stmt = $conn->prepare("SELECT cpf FROM usuarios WHERE cpf = ?");
    $stmt->bind_param("s", $cpf);
    $stmt->execute();
    $resultado = $stmt->get_result();

    // Se o usuário não existir, insere no banco
    if ($resultado->num_rows === 0) {
        $stmt_insert = $conn->prepare("INSERT INTO usuarios (nome, cpf) VALUES (?, ?)");
        $stmt_insert->bind_param("ss", $nome, $cpf);
        $stmt_insert->execute();
    }

    // Envia o usuário para a página de votação em todos os casos
    header("Location: votar.php");
    exit();
}
?>