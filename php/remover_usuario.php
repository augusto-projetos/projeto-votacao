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

// 2. VALIDAÇÃO DO INPUT
$usuario_id = $_POST['id'] ?? null;
if (!$usuario_id) {
    header("Location: gerenciar_usuarios.php?error=invalid_id");
    exit();
}

// Inicia a transação
$conn->begin_transaction();

try {
    // 3. BUSCA OS DADOS DO USUÁRIO, INCLUINDO O STATUS DE ADMIN
    $stmt_find = $conn->prepare("SELECT cpf, is_admin FROM usuarios WHERE id = ?");
    $stmt_find->bind_param("i", $usuario_id);
    $stmt_find->execute();
    $result = $stmt_find->get_result();
    $usuario = $result->fetch_assoc();
    $stmt_find->close();

    if (!$usuario) {
        throw new Exception("Usuário não encontrado.");
    }

    // --- LÓGICA DE PROTEÇÃO ATUALIZADA ---
    // 4. VERIFICA SE O USUÁRIO-ALVO É O ÚLTIMO ADMIN
    if ($usuario['is_admin'] == 1) {
        // Se for um admin, contamos quantos existem no total.
        $result_contagem = $conn->query("SELECT COUNT(id) as total_admins FROM usuarios WHERE is_admin = 1");
        $contagem = $result_contagem->fetch_assoc();

        // Se houver apenas 1 admin (ou menos, por segurança), a remoção é bloqueada.
        if ($contagem && $contagem['total_admins'] <= 1) {
            header("Location: gerenciar_usuarios.php?error=last_admin_delete"); // Novo erro!
            exit();
        }
    }
    // ------------------------------------

    $cpf_a_remover = $usuario['cpf'];

    // 5. REMOVER TODOS OS VOTOS ASSOCIADOS AO CPF DO USUÁRIO
    $stmt_delete_votes = $conn->prepare("DELETE FROM votos WHERE cpf_votante = ?");
    $stmt_delete_votes->bind_param("s", $cpf_a_remover);
    $stmt_delete_votes->execute();
    $stmt_delete_votes->close();

    // 6. REMOVER O REGISTRO DO USUÁRIO DA TABELA 'usuarios'
    $stmt_delete_user = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt_delete_user->bind_param("i", $usuario_id);
    $stmt_delete_user->execute();
    $stmt_delete_user->close();

    // Se tudo deu certo, confirma as alterações no banco.
    $conn->commit();

    // 7. REDIRECIONA COM MENSAGEM DE SUCESSO
    header("Location: gerenciar_usuarios.php?status=delete_success");
    exit();

} catch (Exception $e) {
    // Se algo der errado, desfaz tudo.
    $conn->rollback();
    
    header("Location: gerenciar_usuarios.php?error=delete_failed");
    exit();
}
?>