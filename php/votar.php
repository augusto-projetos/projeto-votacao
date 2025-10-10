<?php
session_start();
require 'conexao.php';
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['cpf'])) {
    header("Location: ../index.php");
    exit();
}
$cpf = $_SESSION['cpf'];

// ... (A lógica de processar o voto continua a mesma) ...
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['voto'])) {
    $grupo_id = $_POST['voto'];
    $stmt = $conn->prepare("SELECT nome, categoria FROM grupos WHERE id = ?");
    $stmt->bind_param("i", $grupo_id);
    $stmt->execute();
    $grupo_votado = $stmt->get_result()->fetch_assoc();
    $categoria_votada = $grupo_votado['categoria'];
    
    $stmt_check = $conn->prepare("SELECT id FROM votos WHERE cpf_votante = ? AND categoria_grupo = ?");
    $stmt_check->bind_param("ss", $cpf, $categoria_votada);
    $stmt_check->execute();
    
    if ($stmt_check->get_result()->num_rows === 0) {
        $stmt_insert = $conn->prepare("INSERT INTO votos (cpf_votante, nome_grupo, categoria_grupo) VALUES (?, ?, ?)");
        $stmt_insert->bind_param("sss", $cpf, $grupo_votado['nome'], $grupo_votado['categoria']);
        $stmt_insert->execute();
    }
    
    header("Location: obrigado.php");
    exit();
}

// Busca apenas as categorias nas quais o usuário AINDA NÃO VOTOU.
$sql_categorias = "SELECT DISTINCT categoria FROM grupos WHERE categoria NOT IN (SELECT DISTINCT categoria_grupo FROM votos WHERE cpf_votante = ?) ORDER BY categoria ASC";
$stmt = $conn->prepare($sql_categorias);
$stmt->bind_param("s", $cpf);
$stmt->execute();
$result = $stmt->get_result();
$categorias_disponiveis = $result->fetch_all(MYSQLI_ASSOC);

// Função auxiliar para criar IDs amigáveis para as seções
function criarSlug($string) {
    $string = strtolower($string);
    $string = preg_replace('/[^a-z0-9\-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/detalhes.css">
    <link rel="stylesheet" href="../css/votar.css">
    <title>Sistema de Votação</title>
</head>
<body>
    <header>
        <h1>Votação Feira Pro 2025</h1>
        <img src="../img/logo.jpg" alt="Logo" id="logo">
        <p id="headerCpf">CPF: <?php echo htmlspecialchars($cpf); ?></p>
    </header>

    <main>
        <!-- ===== ESTRUTURA DO TÍTULO MODIFICADA ===== -->
        <div class="titulo-wrapper">
            <h1 class="titulo-principal">Vote em um grupo de cada categoria</h1>
            <a href="../index.php" class="btn-sair">Sair</a>
        </div>
        <!-- ============================================= -->

        <?php if (!empty($categorias_disponiveis)): ?>
            <!-- Menu de Navegação Rápida -->
            <div class="categoria-filtro-wrapper">
                <label for="filtro-categoria">Ir para a categoria:</label>
                <select id="filtro-categoria">
                    <option value="">-- Selecione uma categoria --</option>
                    <?php foreach ($categorias_disponiveis as $cat): ?>
                        <option value="#<?= criarSlug($cat['categoria']) ?>">
                            <?= htmlspecialchars($cat['categoria']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="categorias-grid">
                <?php foreach ($categorias_disponiveis as $cat): 
                    $categoria_atual = $cat['categoria'];
                    $slug_categoria = criarSlug($categoria_atual);
                ?>
                    <section class="categoria-coluna" id="<?= $slug_categoria ?>">
                        <h2><?= htmlspecialchars($categoria_atual) ?></h2>
                        <div class="grupos-coluna">
                            <?php
                            $stmtGrupos = $conn->prepare("SELECT * FROM grupos WHERE categoria = ?");
                            $stmtGrupos->bind_param("s", $categoria_atual);
                            $stmtGrupos->execute();
                            $grupos = $stmtGrupos->get_result();
                            while ($grupo = $grupos->fetch_assoc()):
                            ?>
                                <form method="post" class="grupo-card">
                                    <img src="../img/<?= htmlspecialchars($grupo['imagem']) ?>" alt="<?= htmlspecialchars($grupo['nome']) ?>">
                                    <h3><?= htmlspecialchars($grupo['nome']) ?></h3>
                                    <p><?= htmlspecialchars($grupo['descricao']) ?></p>
                                    <input type="hidden" name="voto" value="<?= $grupo['id'] ?>">
                                    <button type="submit">Votar</button>
                                </form>
                            <?php endwhile; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="aviso-final">
                <h2>Obrigado por participar!</h2>
                <p>Você já votou em todas as categorias disponíveis.</p>
                <a href="../index.php" class="btn-voltar">Voltar para a página inicial</a>
            </div>
        <?php endif; ?>
    </main>

    <script>
        // Adiciona um null check para o caso da página de "aviso-final" não ter o filtro
        const filtroCategoria = document.getElementById('filtro-categoria');
        if (filtroCategoria) {
            filtroCategoria.addEventListener('change', function() {
                const targetId = this.value;
                if (targetId) {
                    const element = document.getElementById(targetId.substring(1));
                    if (element) {
                        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });
        }
    </script>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> - Sistema de Votação. Todos os direitos reservados.</p>
        <p>Desenvolvido por <strong>Luiz A. L. Freitas</strong></p>
        <p id="feat">feat Yan C. N. de Paiva & Eduardo C. S. Junior & Isabelly A. Silva</p>
    </footer>
</body>
</html>