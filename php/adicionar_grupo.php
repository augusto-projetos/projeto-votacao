<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    // Se a sessão de admin não existir ou não for verdadeira, nega o acesso.
    header("Location: ../index.php");
    exit();
}

// Lista de categorias disponíveis
$categorias = [
    'Administração',
    'Logística',
    'Estética',
    'Beleza',
    'Enfermagem',
    'Turismo',
    'Segurança do Trabalho',
    'Informática'
];

// --- LÓGICA ADICIONADA PARA TRATAR MENSAGENS DE ERRO ---
$message = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'empty_fields':
            $message = 'Todos os campos são obrigatórios. Por favor, preencha tudo.';
            break;
        case 'duplicate_group':
            $message = 'Erro: Já existe um grupo com este nome nesta categoria.';
            break;
        case 'invalid_type':
            $message = 'Erro: O formato da imagem é inválido. Use JPG, PNG ou GIF.';
            break;
        case 'upload_failed':
        case 'move_failed':
            $message = 'Ocorreu um erro inesperado ao enviar a imagem. Tente novamente.';
            break;
        case 'db_error':
        case 'db_exception':
            $message = 'Ocorreu um erro ao salvar os dados no banco. Contate o suporte.';
            break;
        default:
            $message = 'Ocorreu um erro desconhecido.';
            break;
    }
}
// ---------------------------------------------------------

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Novo Grupo</title>
    <link rel="stylesheet" href="../css/detalhes.css">
    <link rel="stylesheet" href="../css/adicionar_grupo.css">
</head>
<body>
    <header>
        <h1>Adicionar Novo Grupo</h1>
        <img src="../img/logo.jpg" alt="Logo" id="logo">
    </header>

    <main class="container">
        <form action="salvar_grupo.php" method="POST" enctype="multipart/form-data" class="form-add-grupo">
            
            <!-- ÁREA ADICIONADA PARA EXIBIR A MENSAGEM DE ERRO -->
            <?php if (!empty($message)): ?>
                <div class="form-message error">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            <!-- ----------------------------------------------- -->

            <div class="form-group">
                <label for="nome">Nome do Grupo</label>
                <input type="text" id="nome" name="nome" placeholder="Ex: Tech Vision" maxlength="100" required>
            </div>

            <div class="form-group">
                <label for="descricao">Descrição</label>
                <textarea id="descricao" name="descricao" rows="4" placeholder="Descreva o projeto do grupo..." required></textarea>
            </div>

            <div class="form-group">
                <label>Imagem do Projeto</label>
                <div class="file-upload-wrapper">
                    <input type="file" id="imagem" name="imagem" accept="image/jpeg, image/png, image/gif" required>
                    <label for="imagem" class="file-upload-label">Escolher Arquivo</label>
                    <span class="file-upload-filename">Nenhum arquivo selecionado</span>
                </div>
            </div>

            <div class="form-group">
                <label for="categoria">Categoria</label>
                <select id="categoria" name="categoria" required>
                    <option value="" disabled selected>-- Selecione uma categoria --</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-actions">
                <a href="gerenciar_grupos.php" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Salvar Grupo</button>
            </div>

        </form>
    </main>

    <script>
        document.getElementById('imagem').addEventListener('change', function() {
            const fileNameSpan = document.querySelector('.file-upload-filename');
            if (this.files && this.files.length > 0) {
                fileNameSpan.textContent = this.files[0].name;
            } else {
                fileNameSpan.textContent = 'Nenhum arquivo selecionado';
            }
        });
    </script>
</body>
</html>