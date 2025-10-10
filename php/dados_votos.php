<?php
// ATIVA A EXIBIÇÃO DE TODOS OS ERROS DO PHP. Essencial para o diagnóstico.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'conexao.php';

// GARANTE que a conexão com o banco de dados e a resposta usem o encoding correto (UTF-8)
if ($conn->connect_error) {
    // Termina a execução se a conexão falhar, mostrando o erro.
    die(json_encode(['error' => 'Falha na conexão com o banco: ' . $conn->connect_error]));
}
$conn->set_charset("utf8mb4");

// A consulta SQL foi alterada para adicionar um critério de desempate.
// 1. Ordena pelo total de votos (total DESC).
// 2. Se houver empate, ordena pelo nome do grupo em ordem alfabética (nome_grupo ASC).
$sql = "SELECT nome_grupo, categoria_grupo, COUNT(*) as total 
        FROM votos 
        GROUP BY nome_grupo, categoria_grupo 
        ORDER BY total DESC, nome_grupo ASC";

$result = $conn->query($sql);

$votos = [];
// Verifica se a consulta foi executada com sucesso
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $votos[] = $row;
    }
} else {
    // Se a consulta falhar, envia uma mensagem de erro no JSON.
    die(json_encode(['error' => 'Erro na consulta SQL: ' . $conn->error]));
}

// INFORMA ao navegador que a resposta é um JSON com codificação UTF-8
header('Content-Type: application/json; charset=utf-8');

// ENVIA a resposta JSON, com uma flag extra para garantir a codificação correta.
echo json_encode($votos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

$conn->close();
?>