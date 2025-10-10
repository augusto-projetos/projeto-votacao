<?php
session_start();
require 'conexao.php';

// 1. VERIFICAÇÃO DE SEGURANÇA
// Garante que apenas administradores autenticados possam executar este script.
$cpf = $_SESSION['cpf'] ?? null;
$admins = $_SESSION['admins'] ?? [];
if (!$cpf || !in_array($cpf, $admins)) {
    die("Acesso negado.");
}

// Garante que a requisição seja feita via POST para maior segurança.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: gerenciar_grupos.php");
    exit();
}

// 2. VALIDAÇÃO DO INPUT
$grupo_id = $_POST['id'] ?? null;
if (!$grupo_id) {
    // Se nenhum ID for fornecido, volta para a página de gerenciamento.
    header("Location: gerenciar_grupos.php?error=invalid_id");
    exit();
}

// Inicia uma transação para garantir que todas as operações sejam bem-sucedidas ou nenhuma delas.
$conn->begin_transaction();

try {
    // 3. BUSCA OS DADOS DO GRUPO ANTES DE APAGAR
    // Precisamos do nome do grupo, categoria e nome da imagem para apagar os 'dependentes'.
    $stmt_find = $conn->prepare("SELECT nome, categoria, imagem FROM grupos WHERE id = ?");
    $stmt_find->bind_param("i", $grupo_id);
    $stmt_find->execute();
    $result = $stmt_find->get_result();
    $grupo = $result->fetch_assoc();
    $stmt_find->close();

    if (!$grupo) {
        // Se o grupo não existir, não há nada a fazer.
        throw new Exception("Grupo não encontrado.");
    }

    // 4. APAGA A IMAGEM FÍSICA DA PASTA 'img/'
    $caminho_arquivo = '../img/' . $grupo['imagem'];
    if (file_exists($caminho_arquivo)) {
        // A função unlink() apaga o arquivo do servidor.
        unlink($caminho_arquivo);
    }

    // 5. APAGA TODOS OS VOTOS ASSOCIADOS A ESTE GRUPO
    // Isso mantém a integridade da sua tabela de votos.
    $stmt_delete_votes = $conn->prepare("DELETE FROM votos WHERE nome_grupo = ? AND categoria_grupo = ?");
    $stmt_delete_votes->bind_param("ss", $grupo['nome'], $grupo['categoria']);
    $stmt_delete_votes->execute();
    $stmt_delete_votes->close();

    // 6. APAGA O REGISTRO DO GRUPO DA TABELA 'grupos'
    // Este é o último passo, após todos os seus 'dependentes' terem sido removidos.
    $stmt_delete_group = $conn->prepare("DELETE FROM grupos WHERE id = ?");
    $stmt_delete_group->bind_param("i", $grupo_id);
    $stmt_delete_group->execute();
    $stmt_delete_group->close();

    // Se todas as operações acima foram bem-sucedidas, confirma a transação.
    $conn->commit();

    // 7. REDIRECIONA COM MENSAGEM DE SUCESSO
    header("Location: gerenciar_grupos.php?status=delete_success");
    exit();

} catch (Exception $e) {
    // Se qualquer uma das operações falhar, desfaz todas as alterações (rollback).
    $conn->rollback();
    
    // Redireciona com uma mensagem de erro genérica.
    // Em um ambiente de produção, você poderia logar o erro $e->getMessage() para depuração.
    header("Location: gerenciar_grupos.php?error=delete_failed");
    exit();
}

?>