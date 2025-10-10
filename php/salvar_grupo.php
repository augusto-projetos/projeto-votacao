<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Se a sessão de admin não existir ou não for verdadeira, nega o acesso.
    header("Location: ../index.php");
    exit();
}

// 1. VERIFICA SE O FORMULÁRIO FOI ENVIADO
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: adicionar_grupo.php");
    exit();
}

// 2. COLETA E LIMPA OS DADOS DO FORMULÁRIO
$nome = trim($_POST['nome'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$categoria = trim($_POST['categoria'] ?? '');
$imagem = $_FILES['imagem'] ?? null;

// 3. VALIDAÇÃO BÁSICA DOS DADOS
if (empty($nome) || empty($descricao) || empty($categoria) || $imagem === null) {
    header("Location: adicionar_grupo.php?error=empty_fields");
    exit();
}

// 4. VERIFICAÇÃO DE DUPLICIDADE (NOVA LÓGICA)
// Esta verificação acontece ANTES do upload da imagem para economizar recursos.
$sql_check = "SELECT id FROM grupos WHERE nome = ? AND categoria = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ss", $nome, $categoria);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    // Se encontrou um registro, o grupo já existe nesta categoria. Redireciona com erro.
    header("Location: adicionar_grupo.php?error=duplicate_group");
    exit();
}
$stmt_check->close();

// 5. PROCESSAMENTO DO UPLOAD DA IMAGEM (continua como antes)
if ($imagem['error'] !== UPLOAD_ERR_OK) {
    header("Location: adicionar_grupo.php?error=upload_failed");
    exit();
}

$diretorio_upload = '../img/';
$extensao = strtolower(pathinfo($imagem['name'], PATHINFO_EXTENSION));
$extensoes_permitidas = ['jpg', 'jpeg', 'png', 'gif'];
if (!in_array($extensao, $extensoes_permitidas)) {
    header("Location: adicionar_grupo.php?error=invalid_type");
    exit();
}

$novo_nome_imagem = uniqid('grupo_', true) . '.' . $extensao;
$caminho_arquivo = $diretorio_upload . $novo_nome_imagem;

if (!move_uploaded_file($imagem['tmp_name'], $caminho_arquivo)) {
    header("Location: adicionar_grupo.php?error=move_failed");
    exit();
}

// 6. INSERÇÃO NO BANCO DE DADOS (continua como antes)
try {
    $sql = "INSERT INTO grupos (nome, descricao, imagem, categoria) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nome, $descricao, $novo_nome_imagem, $categoria);
    
    if ($stmt->execute()) {
        header("Location: gerenciar_grupos.php?status=add_success");
        exit();
    } else {
        unlink($caminho_arquivo); 
        header("Location: adicionar_grupo.php?error=db_error");
        exit();
    }
} catch (Exception $e) {
    if (file_exists($caminho_arquivo)) {
        unlink($caminho_arquivo);
    }
    header("Location: adicionar_grupo.php?error=db_exception");
    exit();
}

?>