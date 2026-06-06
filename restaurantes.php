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
    $nome = $mysqli->real_escape_string($_POST['nome']);
    $tipo_comida = $mysqli->real_escape_string($_POST['tipo_comida']);
    $localizacao = $mysqli->real_escape_string($_POST['localizacao']);
    $nota = intval($_POST['nota']);
    $descricao = $mysqli->real_escape_string($_POST['descricao']);
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;

    if ($id) {
        // UPDATE
        $sql = "UPDATE restaurantes SET nome = '$nome', tipo_comida = '$tipo_comida', localizacao = '$localizacao', nota = $nota, descricao = '$descricao' WHERE id = $id";
        $mysqli->query($sql) or die($mysqli->error);
        $mensagem = "A recomendação do restaurante foi atualizada!";
    } else {
        // CREATE
        $sql = "INSERT INTO restaurantes (nome, tipo_comida, localizacao, nota, descricao) VALUES ('$nome', '$tipo_comida', '$localizacao', $nota, '$descricao')";
        $mysqli->query($sql) or die($mysqli->error);
        $mensagem = "Novo restaurante adicionado ao guia gastronômico do Gonger!";
    }
    $acao = 'listar'; 
}

// DELETE
if ($acao === 'deletar' && $id_atual) {
    $sql = "DELETE FROM restaurantes WHERE id = $id_atual";
    $mysqli->query($sql) or die($mysqli->error);
    $mensagem = "O restaurante foi removido do nosso guia.";
    $acao = 'listar';
}

$restaurante_selecionado = null;
if ($acao === 'editar' && $id_atual) {
    $sql = "SELECT * FROM restaurantes WHERE id = $id_atual";
    $resultado = $mysqli->query($sql) or die($mysqli->error);
    $restaurante_selecionado = $resultado->fetch_assoc();
}

// READ
$sql_todos = "SELECT * FROM restaurantes ORDER BY id DESC";
$lista_restaurantes = $mysqli->query($sql_todos) or die($mysqli->error);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="imagens/iconAba.ico">
    <title>Restaurantes - Receitas do Gonger</title>
</head>
<body>

<div class="container-geral">
    
    <aside class="sidebar">
        <div class="logo-area">
            <a href="receitas.php" class="logo-placeholder"><img src="imagens/iconGrande.png" width="150" height="120" alt="Logo"></a>
        </div>
        
        <nav class="menu-nav">
            <a href="receitas.php" class="menu-item">
                <span class="icon"><img src="imagens/receitas.png" width="36" height="36" alt="Icone"></span> Receitas
            </a>
            <a href="comentarios.php" class="menu-item">
                <span class="icon"><img src="imagens/comentarios.png" width="36" height="36" alt="Icone"></span> Comentários
            </a>
            <a href="restaurantes.php" class="menu-item active">
                <span class="icon"><img src="imagens/restaurantes.png" width="36" height="36" alt="Icone"></span> Restaurantes
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <a href="login.php" class="btn-sair">
                <span class="icon"><img src="imagens/sair.png" width="32" height="32" alt="Sair"></span> Sair
            </a>
        </div>
    </aside>

    <div class="conteudo-principal">
        
        <header class="banner-topo">
            <img src="imagens/gonger.png" width="100" height="100" alt="Gonger"> 

            <div class="banner-texto">
                <h2>Cansado de cozinhar? Compartilhe os melhores restaurantes da região:</h2>
            </div>
            <div class="banner-acoes">
                <a href="restaurantes.php?acao=novo" class="btn-adicionar">+ INDICAR RESTAURANTE</a>
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
                    <?php echo $acao === 'editar' ? 'Atualizando as informações do local...' : 'Indicando um novo restaurante...'; ?>
                </h1>
                
                <form action="restaurantes.php" method="POST" class="form-receita">
                    <input type="hidden" name="id" value="<?php echo $restaurante_selecionado['id'] ?? ''; ?>">
                    
                    <p>
                        <label>Nome do Restaurante</label>
                        <input type="text" name="nome" placeholder="Ex: Cantina do Mario" value="<?php echo $restaurante_selecionado['nome'] ?? ''; ?>" required>
                    </p>

                    <p>
                        <label>Especialidade / Tipo de Comida</label>
                        <input type="text" name="tipo_comida" placeholder="Ex: Italiana, Hambúrguer, Sushi..." value="<?php echo $restaurante_selecionado['tipo_comida'] ?? ''; ?>" required>
                    </p>

                    <p>
                        <label>Localização</label>
                        <input type="text" name="localizacao" placeholder="Ex: Centro, Rua das Flores..." value="<?php echo $restaurante_selecionado['localizacao'] ?? ''; ?>" required>
                    </p>

                    <p>
                        <label>Sua Nota (1 a 5 estrelas)</label>
                        <select name="nota" required>
                            <option value="5" <?php echo (isset($restaurante_selecionado) && $restaurante_selecionado['nota'] == 5) ? 'selected' : ''; ?>>⭐⭐⭐⭐⭐ (5) Incrível!</option>
                            <option value="4" <?php echo (isset($restaurante_selecionado) && $restaurante_selecionado['nota'] == 4) ? 'selected' : ''; ?>>⭐⭐⭐⭐ (4) Muito bom</option>
                            <option value="3" <?php echo (isset($restaurante_selecionado) && $restaurante_selecionado['nota'] == 3) ? 'selected' : ''; ?>>⭐⭐⭐ (3) Vale a visita</option>
                            <option value="2" <?php echo (isset($restaurante_selecionado) && $restaurante_selecionado['nota'] == 2) ? 'selected' : ''; ?>>⭐⭐ (2) Mediano</option>
                            <option value="1" <?php echo (isset($restaurante_selecionado) && $restaurante_selecionado['nota'] == 1) ? 'selected' : ''; ?>>⭐ (1) Não recomendo</option>
                        </select>
                    </p>

                    <p>
                        <label>Por que você indica esse lugar? (O que pedir? Como é o ambiente?)</label>
                        <textarea name="descricao" rows="4" placeholder="Conta pra gente sua experiência..." required><?php echo $restaurante_selecionado['descricao'] ?? ''; ?></textarea>
                    </p>

                    <div class="acoes-form">
                        <a href="restaurantes.php" class="btn-cancelar">Cancelar</a>
                        <button type="submit" class="btn-salvar">Salvar Indicação</button>
                    </div>
                </form>

            <?php else: ?>

                <h1 class="titulo-sessao">Guia de Restaurantes da Comunidade</h1>

                <?php if ($lista_restaurantes->num_rows == 0): ?>
                    <div class="aviso-vazio">
                        <h3>O guia está vazio...</h3>
                        <p>Nenhum restaurante foi indicado ainda. Que tal ser o primeiro clicando em <strong>"Indicar Restaurante"</strong> ali em cima?</p>
                    </div>
                <?php else: ?>
                    <div class="grade-restaurantes">
                        <?php while($rest = $lista_restaurantes->fetch_assoc()): ?>
                            <div class="card-restaurante">
                                <div class="rest-topo">
                                    <h3 class="rest-nome"><?php echo htmlspecialchars($rest['nome']); ?></h3>
                                    <span class="rest-estrelas"><?php echo str_repeat('⭐', $rest['nota']); ?></span>
                                </div>
                                
                                <div class="rest-tags">
                                    <span class="tag-tipo">🍽️ <?php echo htmlspecialchars($rest['tipo_comida']); ?></span>
                                    <span class="tag-local">📍 <?php echo htmlspecialchars($rest['localizacao']); ?></span>
                                </div>
                                
                                <p class="rest-descricao">"<?php echo nl2br(htmlspecialchars($rest['descricao'])); ?>"</p>
                                
                                <div class="card-acoes">
                                    <a href="restaurantes.php?acao=editar&id=<?php echo $rest['id']; ?>" class="btn-acao-simples">Editar</a>
                                    <a href="restaurantes.php?acao=deletar&id=<?php echo $rest['id']; ?>" class="btn-acao-denunciar" onclick="return confirm('Tem certeza que deseja remover este restaurante do guia?');">Remover</a>
                                </div>
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

    /* CSS Principal (Mantendo a identidade do sistema) */
    .container-geral {
        display: flex;
        width: 100vw;
        height: 100vh;
        background-image: url(imagens/bg.png);
        background-position: center;
        background-size: cover;
        overflow: hidden;
    }

    .sidebar {
        width: 200px;
        background-color: #8c2d19;
        display: flex;
        flex-direction: column;
        padding: 20px 0;
        justify-content: space-between;
        flex-shrink: 0;
    }
    .logo-area { padding: 10px 20px; }
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
    .sidebar-footer { padding: 12px 12px; }
    .btn-sair {
        display: flex;
        align-items: center;
        font-size: 13px;
        gap: 15px;
        color: #fbd6cb;
        text-decoration: none;
        font-weight: 600;
    }
    .btn-sair:hover { color: #fffdf9; }

    .conteudo-principal {
        flex: 1;
        display: flex;
        flex-direction: column;
        padding: 30px;
        gap: 20px;
        height: 100vh;
        overflow: hidden; 
    }

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
    .banner-texto { display: flex; align-items: center; }
    .banner-texto h2 {
        font-family: 'Crimson Pro', Georgia, serif;
        color: #8c2d19;
        font-size: 21px;
        font-weight: 700;
        max-width: 900px;
        line-height: 1.4;
        border-left: double 5px #e0d5c1;
        padding: 22px 0px;
        padding-left: 16px;
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
    .btn-adicionar:hover { background-color: #be482e; }

    .bloco-receitas-container {
        flex: 1;
        background-color: #fffdf9;
        border: 1px solid #d1c4b2;
        border-left: 15px solid #8c2d19;
        border-radius: 6px 16px 16px 6px;
        padding: 35px;
        overflow-y: auto; 
    }

    .titulo-sessao {
        font-family: 'Crimson Pro', Georgia, serif;
        color: #8c2d19;
        font-size: 22px;
        margin-bottom: 30px;
        font-weight: 600;
        letter-spacing: 0.5px;
        padding-bottom: 12px;
        border-bottom: 5px double #e0d5c1;
    }

    .mensagem-sucesso {
        background-color: #e6eff4;
        color: #134573;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 20px;
        font-size: 14px;

    }

    /* Formulário */
    .form-receita p { margin-bottom: 20px; }
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
    .acoes-form { display: flex; gap: 15px; margin-top: 10px; }
    
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

    /* Listagem de Restaurantes (Grid de Cards) */
    .grade-restaurantes {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 20px;
    }

    .card-restaurante {
        background: #fdfbf7;
        border: 1px solid #eadeca;
        padding: 20px;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .rest-topo {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
    }

    .rest-nome {
        font-weight: 700;
        color: #8c2d19;
        font-size: 16px;
        line-height: 1.2;
    }

    .rest-estrelas {
        font-size: 12px;
        white-space: nowrap;
    }

    .rest-tags {
        display: flex;
        gap: 10px;
        font-size: 12px;
        font-weight: 600;
    }

    .tag-tipo {
        color: #d95b3f;
    }

    .tag-local {
        color: #6e5645;
    }

    .rest-descricao {
        font-size: 13.5px;
        color: #42342b;
        line-height: 1.5;
        font-style: italic;
        padding-top: 5px;
        border-top: 1px dashed #d1c4b2;
    }

    .card-acoes {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        margin-top: auto;
        padding-top: 10px;
    }

    .btn-acao-simples {
        font-size: 12px;
        color: #6e5645;
        text-decoration: none;
        font-weight: 600;
    }
    .btn-acao-simples:hover { color: #8c2d19; }

    .btn-acao-denunciar {
        font-size: 12px;
        color: #c94444;
        text-decoration: none;
        font-weight: 600;
    }
    .btn-acao-denunciar:hover { color: #8c0000; text-decoration: none; }

    .aviso-vazio { color: #6e5645; }
    .aviso-vazio strong { color: #d95b3f; }

</style>
</body>
</html>