<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Se a sessão de admin não existir ou não for verdadeira, nega o acesso.
    header("Location: ../index.php");
    exit();
}

// 1. PEGA O ID DO GRUPO DA URL
$grupo_id = $_GET['id'] ?? null;
if (!$grupo_id) {
    // Se não houver ID, redireciona de volta para a lista
    header("Location: gerenciar_grupos.php");
    exit();
}

// 2. BUSCA OS DADOS ATUAIS DO GRUPO NO BANCO
$stmt = $conn->prepare("SELECT nome, descricao, imagem, categoria FROM grupos WHERE id = ?");
$stmt->bind_param("i", $grupo_id);
$stmt->execute();
$result = $stmt->get_result();
$grupo = $result->fetch_assoc();

// Se nenhum grupo for encontrado com esse ID, redireciona
if (!$grupo) {
    header("Location: gerenciar_grupos.php");
    exit();
}

// Lista de todas as categorias possíveis
$categorias = [
    "Administração",
    "Logística",
    "Estética",
    "Beleza",
    "Turismo",
    "Segurança do Trabalho",
    "Informática"
];

// Lógica para exibir mensagens de erro (ex: se a atualização falhar)
$message = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'empty_fields') {
        $message = 'Nome e descrição não podem ficar em branco.';
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Grupo: <?= htmlspecialchars($grupo['nome']) ?></title>
    <link rel="stylesheet" href="../css/detalhes.css?">
    <link rel="stylesheet" href="../css/editar_grupo.css?">
</head>
<body>
    <header>
        <h1>Editar Grupo</h1>
        <img src="../img/logo.jpg" alt="Logo" id="logo">
    </header>

    <main class="container">
        <!-- Formulário principal para SALVAR as alterações -->
        <form action="salvar_edicao_grupo.php" method="POST" class="form-add-grupo">
            <!-- É crucial enviar o ID do grupo para sabermos qual registro atualizar -->
            <input type="hidden" name="id" value="<?= htmlspecialchars($grupo_id) ?>">

            <?php if (!empty($message)): ?>
                <div class="form-message error"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <div class="form-group">
                <label for="nome">Nome do Grupo</label>
                <!-- O atributo 'value' preenche o campo com o dado do banco -->
                <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($grupo['nome']) ?>" required>
            </div>

            <div class="form-group">
                <label for="descricao">Descrição</label>
                <!-- Para textareas, o conteúdo vai entre as tags -->
                <textarea id="descricao" name="descricao" rows="4" required><?= htmlspecialchars($grupo['descricao']) ?></textarea>
            </div>
            
            <div class="form-group">
                <label>Imagem Atual (não pode ser alterada)</label>
                <img src="../img/<?= htmlspecialchars($grupo['imagem']) ?>" alt="Imagem atual" class="imagem-preview">
            </div>

            <div class="form-group">
                <label for="categoria">Categoria</label>
                <select id="categoria" name="categoria" required>
                    <?php foreach ($categorias as $cat): ?>
                        <!-- A mágica aqui é o 'selected' para a categoria correta -->
                        <option value="<?= htmlspecialchars($cat) ?>" <?= ($grupo['categoria'] === $cat) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions-edit">
                <!-- Botão para salvar as alterações -->
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        </form> <!-- Fim do formulário de salvar -->

        <!-- Formulário separado e exclusivo para EXCLUIR o grupo -->
        <form action="remover_grupo.php" method="POST" onsubmit="return confirm('ATENÇÃO: Esta ação é permanente. Tem certeza que deseja excluir este grupo e todos os seus votos?');">
            <input type="hidden" name="id" value="<?= htmlspecialchars($grupo_id) ?>">
            <button type="submit" class="btn btn-danger">Excluir Grupo</button>
        </form>
            </div>

            <a href="gerenciar_grupos.php" class="cancel-link">Cancelar e Voltar</a>

    </main>
</body>
</html>