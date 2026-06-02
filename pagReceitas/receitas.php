<?php
// Proteção da página: Garante que só quem passou pelo login.php possa acessar
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['id'])) {
    die("Você não pode acessar esta página porque não está logado. <a href='../login.php'>Clique aqui para fazer login</a>");
}

// Conexão com o banco (Usando o seu arquivo padrão)
include('../pagLogin/conexao.php'); 

$mensagem = "";
$acao = $_GET['acao'] ?? 'listar';
$id_atual = isset($_GET['id']) ? intval($_GET['id']) : null;

// ---------------------------------------
// CONTROLADOR DE AÇÕES (C, U, D)
// ---------------------------------------

// Ação: Salvar (Criar ou Editar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $mysqli->real_escape_string($_POST['titulo']);
    $categoria = $mysqli->real_escape_string($_POST['categoria']);
    $ingredientes = $mysqli->real_escape_string($_POST['ingredientes']);
    $preparo = $mysqli->real_escape_string($_POST['preparo']);
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;

    if ($id) {
        // UPDATE
        $sql = "UPDATE receitas SET titulo = '$titulo', categoria = '$categoria', ingredientes = '$ingredientes', preparo = '$preparo' WHERE id = $id";
        $mysqli->query($sql) or die($mysqli->error);
        $mensagem = "Receita atualizada com carinho por um membro da cozinha! ✏️";
    } else {
        // CREATE
        $sql = "INSERT INTO receitas (titulo, categoria, ingredientes, preparo) VALUES ('$titulo', '$categoria', '$ingredientes', '$preparo')";
        $mysqli->query($sql) or die($mysqli->error);
        $mensagem = "Que cheirinho bom! Sua receita foi compartilhada com o caderno do Gonger! 🍳";
    }
    $acao = 'listar'; 
}

// Ação: Deletar (Exclusão Definitiva do banco de dados)
if ($acao === 'deletar' && $id_atual) {
    $sql = "DELETE FROM receitas WHERE id = $id_atual";
    $mysqli->query($sql) or die($mysqli->error);
    $mensagem = "A receita foi excluída permanentemente do livro. 🧹";
    $acao = 'listar';
}

// Buscar receita específica para ver detalhes ou editar
$receita_selecionada = null;
if (($acao === 'ver' || $acao === 'editar') && $id_atual) {
    $sql = "SELECT * FROM receitas WHERE id = $id_atual";
    $resultado = $mysqli->query($sql) or die($mysqli->error);
    $receita_selecionada = $resultado->fetch_assoc();
}

// READ (Buscar todas as receitas para a vitrine)
$sql_todas = "SELECT * FROM receitas ORDER BY id DESC";
$lista_receitas = $mysqli->query($sql_todas) or die($mysqli->error);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../imagens/iconAba.ico">
    <title>Livro de Receitas do Gonger</title>
</head>
<body>

<div class="header-comunidade">
    <h2>Olá, <?php echo $_SESSION['nome']; ?>! 👋</h2>
    <div class="botoes-topo">
        <a href="receitas.php?acao=novo" class="btn-principal">✨ Enviar Nova Receita</a>
        <a href="../pagLogin/login.php" class="btn-sair">Sair da Cozinha</a>
    </div>
</div>

<main class="bloco-principal">

    <?php if (!empty($mensagem)): ?>
        <div class="mensagem-sucesso">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <?php if ($acao === 'novo' || $acao === 'editar'): ?>
        
        <h1 class="titulo-sessao">
            <?php echo $acao === 'editar' ? '✏️ Ajustar os Temperos (Editar)' : '✍️ Escrever Nova Receita'; ?>
        </h1>
        
        <form action="receitas.php" method="POST" class="form-receita">
            <input type="hidden" name="id" value="<?php echo $receita_selecionada['id'] ?? ''; ?>">
            
            <p>
                <label>Título da Receita</label>
                <input type="text" name="titulo" placeholder="Ex: Bolo de Cenoura com Cobertura" value="<?php echo $receita_selecionada['titulo'] ?? ''; ?>" required>
            </p>

            <p>
                <label>Categoria</label>
                <select name="categoria" required>
                    <option value="🍰 Doce" <?php echo (isset($receita_selecionada) && $receita_selecionada['categoria'] == '🍰 Doce') ? 'selected' : ''; ?>>🍰 Doce</option>
                    <option value="🍕 Salgado" <?php echo (isset($receita_selecionada) && $receita_selecionada['categoria'] == '🍕 Salgado') ? 'selected' : ''; ?>>🍕 Salgado</option>
                    <option value="🍹 Bebida" <?php echo (isset($receita_selecionada) && $receita_selecionada['categoria'] == '🍹 Bebida') ? 'selected' : ''; ?>>🍹 Bebida</option>
                </select>
            </p>

            <p>
                <label>Ingredientes (Coloque um por linha)</label>
                <textarea name="ingredientes" rows="6" placeholder="Ex:&#10;3 cenouras&#10;2 xícaras de açúcar" required><?php echo $receita_selecionada['ingredientes'] ?? ''; ?></textarea>
            </p>

            <p>
                <label>Modo de Preparo</label>
                <textarea name="preparo" rows="8" placeholder="Explique o passo a passo com calma..." required><?php echo $receita_selecionada['preparo'] ?? ''; ?></textarea>
            </p>

            <div class="acoes-form">
                <a href="receitas.php" class="btn-cancelar">Cancelar</a>
                <button type="submit" class="btn-salvar">Salvar no Caderno 🚀</button>
            </div>
        </form>

    <?php elseif ($acao === 'ver' && $receita_selecionada): ?>
        
        <div class="detalhe-receita">
            <span class="categoria-tag"><?php echo $receita_selecionada['categoria']; ?></span>
            <h1 class="titulo-detalhe"><?php echo $receita_selecionada['titulo']; ?></h1>
            
            <div class="botoes-gerenciamento">
                <a href="receitas.php?acao=editar&id=<?php echo $receita_selecionada['id']; ?>" class="btn-editar">✏️ Editar</a>
                <a href="receitas.php?acao=deletar&id=<?php echo $receita_selecionada['id']; ?>" class="btn-deletar" onclick="return confirm('Tem certeza que deseja apagar definitivamente essa receita do sistema? 😮');">🗑️ Remover</a>
            </div>

            <div class="secao-conteudo">
                <h3>📋 Ingredientes</h3>
                <p><?php echo nl2br($receita_selecionada['ingredientes']); ?></p>
            </div>

            <div class="secao-conteudo">
                <h3>🍳 Modo de Preparo</h3>
                <p><?php echo nl2br($receita_selecionada['preparo']); ?></p>
            </div>

            <div class="voltar-centro">
                <a href="receitas.php" class="btn-cancelar">← Voltar para o Livro</a>
            </div>
        </div>

    <?php else: ?>

        <h1 class="titulo-sessao">📖 O Livro Comunitário de Receitas</h1>
        <p class="subtitulo">Sinta-se em casa! Se encontrar algum erro ou quiser complementar o preparo de uma receita, sinta-se livre para editar.</p>

        <?php if ($lista_receitas->num_rows == 0): ?>
            <div class="aviso-vazio">
                <h3>O fogão está desligado... 🥞</h3>
                <p>Nenhuma receita foi enviada ainda. Que tal estrear o caderno clicando em <strong>"Enviar Nova Receita"</strong> ali em cima?</p>
            </div>
        <?php else: ?>
            <div class="grade-receitas">
                <?php while($r = $lista_receitas->fetch_assoc()): ?>
                    <div class="card-receita">
                        <span class="card-categoria"><?php echo $r['categoria']; ?></span>
                        <h3 class="card-titulo"><?php echo $r['titulo']; ?></h3>
                        <p class="card-resumo"><?php echo mb_strimwidth($r['ingredientes'], 0, 90, "..."); ?></p>
                        <a href="receitas.php?acao=ver&id=<?php echo $r['id']; ?>" class="btn-ver-mais">Ver Receita Completa →</a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>

</main>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,600;1,400&family=Montserrat:wght@400;700&display=swap');

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Crimson Pro', Georgia, serif;
        background-color: #f4eedb;
        background-image: url(../imagens/bg.png);
        background-position: center;
        background-size: cover;
        color: #42342b;
        padding: 30px 20px;
        min-height: 100vh;
    }

    .header-comunidade {
        max-width: 900px;
        margin: 0 auto 20px auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: rgba(255, 253, 249, 0.8);
        padding: 15px 25px;
        border-radius: 8px;
        border: 1px solid #d1c4b2;
    }
    .header-comunidade h2 { font-size: 20px; color: #6e5645; }
    .botoes-topo { display: flex; gap: 15px; align-items: center; }

    .bloco-principal {
        background-color: #fffdf9;
        border: 1px solid #d1c4b2;
        border-left: 22px solid #8c2d19;
        border-radius: 6px 16px 16px 6px;
        padding: 40px;
        width: 100%;
        max-width: 900px;
        margin: 0 auto;
        box-shadow: 0 10px 25px rgba(84, 59, 45, 0.2);
        position: relative;
    }

    .bloco-principal::after {
        content: '';
        position: absolute;
        top: 0; left: -12px; bottom: 0;
        width: 2px;
        border-left: 2px dashed #d9a05b;
        opacity: 0.6;
    }

    .titulo-sessao {
        font-size: 28px;
        color: #8c2d19;
        text-align: center;
        margin-bottom: 10px;
        border-bottom: 5px double #e0d5c1;
        padding-bottom: 12px;
    }
    .subtitulo { text-align: center; margin-bottom: 35px; color: #6e5645; font-style: italic;}

    .mensagem-sucesso {
        background-color: #e1efe0;
        color: #3e6b3c;
        padding: 15px;
        border-radius: 6px;
        font-weight: bold;
        margin-bottom: 25px;
        text-align: center;
        border: 1px solid #b8dbb5;
    }
    .aviso-vazio { text-align: center; padding: 40px 0; color: #7a685c; }

    .grade-receitas {
        display: grid;
        grid