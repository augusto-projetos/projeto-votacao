<?php
require 'conexao.php';

if (isset($_POST['cpf'])) {
    $cpf = trim($_POST['cpf']);

    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE cpf = ?");
    $stmt->bind_param("s", $cpf);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        echo "existe";
    } else {
        echo "nao_existe";
    }
}
?>