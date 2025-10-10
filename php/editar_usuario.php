<?php
session_start();
require 'conexao.php';

// Verificação de segurança
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../index.php");
    exit();
}

// Obter o ID do usuário
$usuario_id = $_GET['id'] ?? null;
if (!$usuario_id) {
    header("Location: gerenciar_usuarios.php");
    exit();
}

// Buscar os dados atuais do usuário
$stmt = $conn->prepare("SELECT nome, cpf, is_admin FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();

if (!$usuario) {
    header("Location: gerenciar_usuarios.php");
    exit();
}

// LÓGICA PARA EXIBIR MENSAGENS DE ERRO (ATUALIZADA)
$message = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'empty_fields':
            $message = 'O campo Nome não pode ficar em branco.';
            break;
        // --- NOVA MENSAGEM DE ERRO ADICIONADA ---
        case 'last_admin':
            $message = 'Ação bloqueada: Não é possível remover o status de administrador do único admin existente no sistema.';
            break;
        // ----------------------------------------
        default:
            $message = 'Ocorreu um erro inesperado.';
            break;
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário: <?= htmlspecialchars($usuario['nome']) ?></title>
    <link rel="stylesheet" href="../css/detalhes.css">
    <link rel="stylesheet" href="../css/editar_usuario.css">
</head>
<body>
    <header>
        <h1>Editar Usuário</h1>
        <img src="../img/logo.jpg" alt="Logo" id="logo">
    </header>

    <main class="container">
        <!-- Formulário principal para SALVAR as alterações -->
        <form action="salvar_edicao_usuario.php" method="POST" class="form-edit-user">
            <input type="hidden" name="id" value="<?= htmlspecialchars($usuario_id) ?>">

            <?php if (!empty($message)): ?>
                <div class="form-message error"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <div class="form-group">
                <label for="nome">Nome do Usuário</label>
                <input type="text" id="nome" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
            </div>

            <div class="form-group">
                <label for="cpf">CPF (não pode ser alterado)</label>
                <input type="text" id="cpf" name="cpf" value="<?= htmlspecialchars($usuario['cpf']) ?>" disabled>
            </div>
            
            <div class="form-group">
                <label for="is_admin">Status do Usuário</label>
                <select id="is_admin" name="is_admin" required>
                    <option value="0" <?= ($usuario['is_admin'] == 0) ? 'selected' : '' ?>>Usuário Comum</option>
                    <option value="1" <?= ($usuario['is_admin'] == 1) ? 'selected' : '' ?>>Administrador</option>
                </select>
            </div>

            <div class="form-actions-edit">
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        </form>

        <!-- Formulário para REMOVER o usuário -->
        <form action="remover_usuario.php" method="POST" onsubmit="return confirm('ATENÇÃO: Esta ação é permanente e removerá o usuário e todos os seus votos. Tem certeza que deseja continuar?');">
            <input type="hidden" name="id" value="<?= htmlspecialchars($usuario_id) ?>">
            <button type="submit" class="btn btn-danger">Remover Usuário</button>
        </form>
            </div>

            <a href="gerenciar_usuarios.php" class="cancel-link">Cancelar e Voltar</a>
    </main>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> - Sistema de Votação. Todos os direitos reservados.</p>
        <p>Desenvolvido por <strong>Luiz A. L. Freitas</strong></p>
        <p id="feat">feat Yan C. N. de Paiva & Eduardo C. S. Junior & Isabelly A. Silva</p>
    </footer>
</body>
</html>