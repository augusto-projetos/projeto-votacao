<?php
session_start();
require 'conexao.php';

// Verificação de segurança, padrão para todas as páginas de admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: ../index.php");
    exit();
}

// 1. BUSCAR TODOS OS USUÁRIOS DO BANCO DE DADOS
// Ordena por nome para facilitar a visualização
$sql = "SELECT id, nome, cpf, is_admin FROM usuarios ORDER BY nome ASC";
$result = $conn->query($sql);
$usuarios = $result->fetch_all(MYSQLI_ASSOC);

// 2. LÓGICA PARA EXIBIR MENSAGENS DE FEEDBACK (ATUALIZADA)
$message = '';
$message_type = ''; // 'success' ou 'error'

if (isset($_GET['status'])) {
    $message_type = 'success';
    switch ($_GET['status']) {
        case 'edit_success':
            $message = 'Usuário atualizado com sucesso!';
            break;
        case 'delete_success':
            $message = 'Usuário removido com sucesso!';
            break;
    }
} elseif (isset($_GET['error'])) {
    $message_type = 'error';
    switch ($_GET['error']) {
        case 'delete_failed':
            $message = 'Erro ao remover o usuário.';
            break;
        case 'last_admin_delete':
            $message = 'Ação bloqueada: Não é possível remover o último administrador do sistema.';
            break;
        default:
            $message = 'Ocorreu um erro desconhecido.';
            break;
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários</title>
    <link rel="stylesheet" href="../css/detalhes.css">
    <link rel="stylesheet" href="../css/gerenciar_usuarios.css">
</head>
<body>
    <header>
        <h1>Gerenciar Usuários</h1>
        <img src="../img/logo.jpg" alt="Logo" id="logo">
    </header>

    <main class="container">
        <div class="page-actions">
            <a href="admin.php" class="btn btn-secondary">Voltar ao Painel</a>
        </div>
        
        <!-- Exibe a mensagem de feedback, se houver -->
        <?php if (!empty($message)): ?>
            <div class="alert <?= $message_type === 'success' ? 'success' : 'error' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="4">Nenhum usuário encontrado.</td>
                        </tr>
                    <?php else: ?>
                        <!-- Loop para cada USUÁRIO -->
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td data-label="Nome"><?= htmlspecialchars($usuario['nome']) ?></td>
                                <td data-label="CPF"><?= htmlspecialchars($usuario['cpf']) ?></td>
                                <td data-label="Status">
                                    <?php if ($usuario['is_admin'] == 1): ?>
                                        <span class="status-admin">Administrador</span>
                                    <?php else: ?>
                                        <span class="status-user">Usuário</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Ações">
                                    <a href="editar_usuario.php?id=<?= $usuario['id'] ?>" class="btn btn-edit">Editar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
    
    <footer>
        <p>&copy; <?php echo date('Y'); ?> - Sistema de Votação. Todos os direitos reservados.</p>
        <p>Desenvolvido por <strong>Luiz A. L. Freitas</strong></p>
        <p id="feat">feat Yan C. N. de Paiva & Eduardo C. S. Junior & Isabelly A. Silva</p>
    </footer>
</body>
</html>