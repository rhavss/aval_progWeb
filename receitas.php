<?php
include('conexao.php'); 

// Protect
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['id'])) {
    die("Você não pode acessar esta página porque não está logado. <a href='login.php'>Clique aqui para fazer login</a>");
}

$mensagem = "";
$acao = $_GET['acao'] ?? 'listar';
$id_atual = isset($_GET['id']) ? intval($_GET['id']) : null;

// Create/Update
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
        $mensagem = "Receita atualizada com carinho por um membro da cozinha!";
    } else {
        // CREATE
        $sql = "INSERT INTO receitas (titulo, categoria, ingredientes, preparo) VALUES ('$titulo', '$categoria', '$ingredientes', '$preparo')";
        $mysqli->query($sql) or die($mysqli->error);
        $mensagem = "Que cheirinho bom! Sua receita foi compartilhada com o livro do Gonger!";
    }
    $acao = 'listar'; 
}

// Delete
if ($acao === 'deletar' && $id_atual) {
    $sql = "DELETE FROM receitas WHERE id = $id_atual";
    $mysqli->query($sql) or die($mysqli->error);
    $mensagem = "A receita foi excluída permanentemente do livro!";
    $acao = 'listar';
}

$receita_selecionada = null;
if (($acao === 'ver' || $acao === 'editar') && $id_atual) {
    $sql = "SELECT * FROM receitas WHERE id = $id_atual";
    $resultado = $mysqli->query($sql) or die($mysqli->error);
    $receita_selecionada = $resultado->fetch_assoc();
}

// READ
$sql_todas = "SELECT * FROM receitas ORDER BY id DESC";
$lista_receitas = $mysqli->query($sql_todas) or die($mysqli->error);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="imagens/iconAba.ico">
    <title>Livro de Receitas do Gonger</title>
</head>
<body>

<div class="container-geral">
    
    <aside class="sidebar">
        <div class="logo-area">
            <a href="receitas.php" class="logo-placeholder"><img src="imagens/iconGrande.png" width="150" height="120"></a>
        </div>
        
        <nav class="menu-nav">
            <a href="receitas.php" class="menu-item active">
                <span class="icon"><img src="imagens/receitas.png" width="36" height="36"></span> Receitas
            </a>
            <a href="comentarios.php" class="menu-item">
                <span class="icon"><img src="imagens/comentarios.png" width="36" height="36"></span> Comentários
            </a>
            <a href="restaurantes.php" class="menu-item">
                <span class="icon"><img src="imagens/restaurantes.png" width="36" height="36"></span> Restaurantes
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <a href="login.php" class="btn-sair">
                <span class="icon"><img src="imagens/sair.png" width="32" height="32"></span> Sair
            </a>
        </div>
    </aside>

    <div class="conteudo-principal">
        
        <header class="banner-topo">

            <img src="imagens/gonger.png" widht="100" height="100"> 

            <div class="banner-texto">
                <h2>Veja e interaja a vontade com o livro comunitário de receitas do Gonger:</h2>
            </div>
            <div class="banner-acoes">
                <a href="receitas.php?acao=novo" class="btn-adicionar">+ ADICIONAR RECEITA</a>
            </div>
        </header>

        <main class="bloco-receitas-container">

            <?php if (!empty($mensagem)): ?>
                <div class="mensagem-sucesso">
                    <?php echo $mensagem; ?>
                </div>
            <?php endif; ?>

            <?php if ($acao === 'novo' || $acao === 'editar'): ?>
                
                <h1 class="titulo-sessao">
                    <?php echo $acao === 'editar' ? 'Ajustando os temperos...' : 'Escrevendo nova receita...'; ?>
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
                        <textarea name="ingredientes" rows="5" placeholder="Ex:&#10;3 cenouras&#10;2 xícaras de açúcar" required><?php echo $receita_selecionada['ingredientes'] ?? ''; ?></textarea>
                    </p>

                    <p>
                        <label>Modo de Preparo</label>
                        <textarea name="preparo" rows="6" placeholder="Explique o passo a passo com calma..." required><?php echo $receita_selecionada['preparo'] ?? ''; ?></textarea>
                    </p>

                    <div class="acoes-form">
                        <a href="receitas.php" class="btn-cancelar">Cancelar</a>
                        <button type="submit" class="btn-salvar">Salvar no Caderno</button>
                    </div>
                </form>

            <?php elseif ($acao === 'ver' && $receita_selecionada): ?>
                
                <div class="detalhe-receita">
                    <span class="categoria-tag"><?php echo $receita_selecionada['categoria']; ?></span>
                    <h1 class="titulo-detalhe"><?php echo $receita_selecionada['titulo']; ?></h1>
                    
                    <div class="botoes-gerenciamento">
                        <a href="receitas.php?acao=editar&id=<?php echo $receita_selecionada['id']; ?>" class="btn-editar">Editar</a>
                        <a href="receitas.php?acao=deletar&id=<?php echo $receita_selecionada['id']; ?>" class="btn-deletar" onclick="return confirm('Tem certeza que deseja apagar essa receita do livro?');">Remover</a>
                    </div>

                    <div class="secao-conteudo">
                        <h3>> Ingredientes</h3>
                        <p><?php echo nl2br($receita_selecionada['ingredientes']); ?></p>
                    </div>

                    <div class="secao-conteudo">
                        <h3>> Modo de Preparo</h3>
                        <p><?php echo nl2br($receita_selecionada['preparo']); ?></p>
                    </div>

                    <a href="receitas.php" class="btn-cancelar">← Voltar para o Livro</a>

                </div>

            <?php else: ?>

                <h1 class="titulo-sessao">Página de Receitas</h1>

                <?php if ($lista_receitas->num_rows == 0): ?>
                    <div class="aviso-vazio">
                        <h3>O fogão está desligado...</h3>
                        <p>Nenhuma receita foi enviada ainda. Que tal estrear o caderno clicando em <strong>"Adicionar Receita"</strong> ali em cima?</p>
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
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap');

    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Montserrat', sans-serif;
        background-color: #0d0f12;
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .container-geral {
        display: flex;
        width: 100vw;
        height: 100vh;
        background-image: url(imagens/bg.png);
        background-position: center;
        background-size: cover;
        overflow: hidden;
    }

    /* --- SIDEBAR --- */
    .sidebar {
        width: 200px;
        background-color: #8c2d19;
        display: flex;
        flex-direction: column;
        padding: 20px 0;
        justify-content: space-between;
        flex-shrink: 0;
    }

    .logo-area {
        padding: 10px 20px;
    }

    .menu-nav {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: -120px;
    }

    .menu-item {
        display: flex;
        align-items: center;
        gap: 15px;
        color: #fbd6cb;
        text-decoration: none;
        padding: 12px 12px;
        font-size: 15px;
        font-weight: 600;
        transition: background 0.2s;
    }

    .menu-item:hover, .menu-item.active {
        background-color: #92311f;
        color: #fffdf9;
    }

    .sidebar-footer {
        padding: 12px 12px;
    }
    
    .sidebar-footer .btn-sair {
        display: flex;
        align-items: center;
        font-size: 13px;
        gap: 15px;
        color: #fbd6cb;
        text-decoration: none;
        font-weight: 600;
    }

    .sidebar-footer .btn-sair:hover {
        color: #fffdf9;
    }

    /* --- CONTEÚDO PRINCIPAL (DIREITA) --- */
    .conteudo-principal {
        flex: 1;
        display: flex;
        flex-direction: column;
        padding: 30px;
        gap: 20px;
        height: 100vh;
        overflow: hidden; /* Deixa o scroll apenas para o quadrinho interno se necessário */
    }

    /* Banner Superior */
    .banner-topo {
        background-color: #fffdf9;
        border: 1px solid #d1c4b2;
        border-left: 15px solid #8c2d19;
        border-radius: 6px 16px 16px 6px;
        padding: 12px 54px 12px 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        flex-shrink: 0;
    }

    .banner-texto {
        display: flex;
        align-items: center;
    }
    

    .banner-texto h2 {
        font-family: 'Crimson Pro', Georgia, serif;
        color: #8c2d19;
        font-size: 21px;
        font-weight: 700;
        max-width: 900px;
        line-height: 1.4;
        border-left: double 5px #e0d5c1;
        padding: 22px 0px;
        padding-left: 18px;
    }

    .btn-adicionar {
        background-color: #d95b3f;
        color: #fffdf9;
        text-decoration: none;
        padding: 13.5px 26px;
        border-radius: 25px;
        font-weight: 700;
        font-size: 13.5px;
        letter-spacing: 0.5px;
        transition: background 0.2s;
        white-space: nowrap;
    }
    .btn-adicionar:hover {
        background-color: #be482e;
    }

    /* O QUADRINHO (Consertado para conter tudo sem vazar) */
    .bloco-receitas-container {
        flex: 1;
        background-color: #fffdf9;
        border: 1px solid #d1c4b2;
        border-left: 15px solid #8c2d19;
        border-radius: 6px 16px 16px 6px;
        padding: 35px;
        overflow-y: auto; /* Adiciona scroll interno dinâmico caso o form cresça */
    }

    /* --- ELEMENTOS DO FORMULÁRIO --- */
    .titulo-sessao {
        font-family: 'Crimson Pro', Georgia, serif;
        color: #8c2d19;
        font-size: 22px;
        margin-bottom: 50px;
        font-weight: 600;
        letter-spacing: 0.5px;
        padding-bottom: 12px;
        border-bottom: 5px double #e0d5c1;
    }

    .form-receita p {
        margin-bottom: 20px;
    }

    .form-receita label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #6e5645;
        font-size: 14px;
    }

    .form-receita input, .form-receita select, .form-receita textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #dbdbdb;
        border-radius: 6px;
        font-family: inherit;
        font-size: 14px;
        color: #6e5645;
        background-color: #ffffff;
    }

    .form-receita input:focus, .form-receita select:focus, .form-receita textarea:focus {
        outline: none;
        border-color: #8c2d19;
    }

    .acoes-form {
        display: flex;
        gap: 15px;
        margin-top: 10px;
    }

    .btn-editar, .btn-deletar, .btn-cancelar {
        text-decoration: none;
        background-color: #f0e4d8;
        color: #8c2d19;
        border-radius: 10px;
        font-weight: 600;
        font-family: 'Montserrat', sans-serif;
        padding: 7px 10px;
        font-size: 12px;
        transition: 0.2s;
    }

    .btn-editar:hover, .btn-deletar:hover, .btn-cancelar:hover {
        color: #be482e
    }

    .btn-salvar {
        border: none;
        outline: none;
        cursor: pointer;
        text-decoration: none;
        background-color: #8c2d19;
        color: #f0e4d8;
        border-radius: 10px;
        font-weight: 600;
        font-family: 'Montserrat', sans-serif;
        padding: 7px 10px;
        font-size: 12px;
        transition: 0.2s;
    }

    .btn-salvar:hover {
        color: #fbd6cb;
    }

    .detalhe-receita {
        color: #6e5645
    }

    .detalhe-receita h1, .detalhe-receita h3 {
        margin: 16px 0px;
    }

    .secao-conteudo {
        margin: 18px 0px;
        border-top: double 5px #d1c4b2;
    }

    /* Listagem de Cards */
    .grade-receitas {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 20px;
    }

    .card-receita {
        background: #fdfbf7;
        border: 1px solid #eadeca;
        padding: 20px;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 12px;
    }

    .card-categoria {
        font-size: 12px;
        font-weight: bold;
        color: #d95b3f;
    }

    .card-titulo {
        font-size: 16px;
        color: #7d2617;
    }

    .card-resumo {
        font-size: 13px;
        color: #6e5645;
        line-height: 1.4;
    }

    .btn-ver-mais {
        font-size: 13px;
        color: #d95b3f;
        text-decoration: none;
        font-weight: 600;
    }

    .mensagem-sucesso {
        background-color: #e6eff4;
        color: #134573;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .aviso-vazio {
        color: #6e5645;
    }

    .aviso-vazio strong {
        color: #d95b3f;
    }

</style>

</body>
</html>