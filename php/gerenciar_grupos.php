<?php
session_start();
require 'conexao.php';

// Verificação de segurança, padrão para todas as páginas de admin
$cpf = $_SESSION['cpf'] ?? null;
$admins = $_SESSION['admins'] ?? [];
if (!$cpf || !in_array($cpf, $admins)) {
    header("Location: ../index.php");
    exit();
}

// 1. BUSCA E ORGANIZA OS GRUPOS
// A consulta SQL busca todos os grupos, ordenando por categoria e depois por nome
$sql = "SELECT id, nome, descricao, imagem, categoria FROM grupos ORDER BY categoria ASC, nome ASC";
$result = $conn->query($sql);

// Cria um array para agrupar os resultados. Ex: ['Tecnologia' => [grupo1, grupo2], 'Turismo' => [grupo3]]
$grupos_por_categoria = [];
if ($result) {
    while ($grupo = $result->fetch_assoc()) {
        $grupos_por_categoria[$grupo['categoria']][] = $grupo;
    }
}

// Lógica para exibir mensagens de sucesso que virão de outras páginas
$success_message = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'edit_success') {
        $success_message = 'Grupo atualizado com sucesso!';
    }
    if ($_GET['status'] === 'delete_success') {
        $success_message = 'Grupo removido com sucesso!';
    }
    if ($_GET['status'] === 'add_success') {
        $success_message = 'Grupo adicionado com sucesso!';
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Grupos</title>
    <link rel="stylesheet" href="../css/detalhes.css">
    <link rel="stylesheet" href="../css/gerenciar_grupos.css">
</head>
<body>
    <header>
        <h1>Gerenciar Grupos</h1>
        <img src="../img/logo.jpg" alt="Logo" id="logo">
    </header>

    <main class="container">
        <div class="page-actions">
            <a href="adicionar_grupo.php" class="btn btn-add">Adicionar Novo Grupo</a>
            <a href="admin.php" class="btn btn-secondary">Voltar ao Painel</a>
        </div>
        
        <!-- Exibe a mensagem de sucesso, se houver -->
        <?php if (!empty($success_message)): ?>
            <div class="alert success">
                <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($grupos_por_categoria)): ?>
            <div class="alert info">
                <p>Nenhum grupo cadastrado ainda.</p>
                <p>Clique em "Adicionar Novo Grupo" para começar.</p>
            </div>
        <?php else: ?>
            <!-- Loop para cada CATEGORIA -->
            <?php foreach ($grupos_por_categoria as $categoria => $grupos): ?>
                <section class="categoria-section">
                    <h2 class="categoria-titulo"><?= htmlspecialchars($categoria) ?></h2>
                    <div class="grupo-lista">
                        <!-- Loop para cada GRUPO dentro da categoria -->
                        <?php foreach ($grupos as $grupo): ?>
                            <div class="grupo-item">
                                <img src="../img/<?= htmlspecialchars($grupo['imagem']) ?>" alt="Imagem de <?= htmlspecialchars($grupo['nome']) ?>" class="grupo-imagem">
                                <div class="grupo-info">
                                    <h3 class="grupo-nome"><?= htmlspecialchars($grupo['nome']) ?></h3>
                                    <p class="grupo-descricao"><?= htmlspecialchars($grupo['descricao']) ?></p>
                                </div>
                                <div class="grupo-actions">
                                    <!-- O link "Editar" passa o ID do grupo pela URL -->
                                    <a href="editar_grupo.php?id=<?= $grupo['id'] ?>" class="btn btn-edit">Editar</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> - Sistema de Votação. Todos os direitos reservados.</p>
        <p>Desenvolvido por <strong>Luiz A. L. Freitas</strong></p>
        <p id="feat">feat Yan C. N. de Paiva & Eduardo C. S. Junior & Isabelly A. Silva</p>
    </footer>
</body>
</html>