<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Se a sessão de admin não existir ou não for verdadeira, nega o acesso.
    header("Location: ../index.php");
    exit();
}

// 1. VERIFICA SE O FORMULÁRIO FOI ENVIADO CORRETAMENTE
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: gerenciar_grupos.php");
    exit();
}

// 2. COLETA E LIMPA OS DADOS DO FORMULÁRIO
$grupo_id = $_POST['id'] ?? null;
$nome = trim($_POST['nome'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');

// 3. VALIDAÇÃO DOS DADOS
// Verifica se os campos essenciais não estão vazios
if (empty($grupo_id) || empty($nome) || empty($descricao) || empty($categoria)) {
    // Se algo estiver faltando, volta para a página de edição com erro
    header("Location: editar_grupo.php?id=$grupo_id&error=empty_fields");
    exit();
}

// 4. VERIFICAÇÃO DE DUPLICIDADE (A LÓGICA MAIS IMPORTANTE)
// Procura por outro grupo com o mesmo nome na mesma categoria,
// mas IGNORA o próprio grupo que estamos editando (usando "AND id != ?").
$sql_check = "SELECT id FROM grupos WHERE nome = ? AND categoria = ? AND id != ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ssi", $nome, $categoria, $grupo_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Se encontrou outro grupo, a alteração não é permitida.
    header("Location: editar_grupo.php?id=$grupo_id&error=duplicate_group");
    exit();
}
$stmt_check->close();


// 5. ATUALIZAÇÃO NO BANCO DE DADOS
try {
    $sql_update = "UPDATE grupos SET nome = ?, descricao = ?, categoria = ? WHERE id = ?";
    $stmt_update = $conn->prepare($sql_update);
    // "sssi" indica 3 strings (nome, desc, cat) e 1 integer (id)
    $stmt_update->bind_param("sssi", $nome, $descricao, $categoria, $grupo_id);
    
    if ($stmt_update->execute()) {
        // Sucesso! Redireciona para a lista de gerenciamento com uma mensagem de sucesso.
        header("Location: gerenciar_grupos.php?status=edit_success");
        exit();
    } else {
        // Se a atualização falhar por algum motivo do banco.
        header("Location: editar_grupo.php?id=$grupo_id&error=db_error");
        exit();
    }
} catch (Exception $e) {
    // Em caso de uma exceção inesperada.
    header("Location: editar_grupo.php?id=$grupo_id&error=db_exception");
    exit();
}
?>