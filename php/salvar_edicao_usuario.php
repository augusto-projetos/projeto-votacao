<?php
session_start();
require 'conexao.php';

// 1. VERIFICAÇÃO DE SEGURANÇA
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    die("Acesso negado.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: gerenciar_usuarios.php");
    exit();
}

// 2. COLETA E VALIDAÇÃO DOS DADOS
$usuario_id = $_POST['id'] ?? null;
$nome = trim($_POST['nome'] ?? '');
$is_admin_novo = $_POST['is_admin'] ?? null; // Novo status vindo do formulário

// Verifica se os campos essenciais não estão vazios
if (empty($usuario_id) || empty($nome) || !in_array($is_admin_novo, ['0', '1'])) {
    header("Location: editar_usuario.php?id=" . $usuario_id . "&error=empty_fields");
    exit();
}

// 3. LÓGICA PARA PROTEGER O ÚLTIMO ADMIN
// Primeiro, verifica se estamos tentando rebaixar um admin (de 1 para 0)
$stmt_status_atual = $conn->prepare("SELECT is_admin FROM usuarios WHERE id = ?");
$stmt_status_atual->bind_param("i", $usuario_id);
$stmt_status_atual->execute();
$usuario_atual = $stmt_status_atual->get_result()->fetch_assoc();
$stmt_status_atual->close();

if ($usuario_atual && $usuario_atual['is_admin'] == 1 && $is_admin_novo == 0) {
    // Se a intenção é rebaixar, contamos quantos admins existem no total.
    $result_contagem = $conn->query("SELECT COUNT(id) as total_admins FROM usuarios WHERE is_admin = 1");
    $contagem = $result_contagem->fetch_assoc();

    // Se houver apenas 1 admin (ou menos, por segurança), a ação é bloqueada.
    if ($contagem && $contagem['total_admins'] <= 1) {
        header("Location: editar_usuario.php?id=" . $usuario_id . "&error=last_admin");
        exit();
    }
}

// 4. ATUALIZAÇÃO NO BANCO DE DADOS (se passou pela verificação)
try {
    $sql = "UPDATE usuarios SET nome = ?, is_admin = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $nome, $is_admin_novo, $usuario_id);
    
    if ($stmt->execute()) {
        header("Location: gerenciar_usuarios.php?status=edit_success");
        exit();
    } else {
        header("Location: editar_usuario.php?id=" . $usuario_id . "&error=db_error");
        exit();
    }
} catch (Exception $e) {
    header("Location: editar_usuario.php?id=" . $usuario_id . "&error=db_exception");
    exit();
}
?>