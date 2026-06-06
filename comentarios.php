<?php
// Proteção da página (mantida para seguir o padrão do seu painel)
if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['id'])) {
    die("Você não pode acessar esta página porque não está logado. <a href='login.php'>Clique aqui para fazer login</a>");
}

include('conexao.php'); 

$mensagem = "";
$acao = $_GET['acao'] ?? 'listar';
$id_atual = isset($_GET['id']) ? intval($_GET['id']) : null;

// ---------------------------------------
// CONTROLADOR DE AÇÕES (C, U, D)
// ---------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receita_id = intval($_POST['receita_id']);
    $nome = $mysqli->real_escape_string($_POST['nome']);
    $nota = intval($_POST['nota']);
    $texto = $mysqli->real_escape_string($_POST['texto']);
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;

    if ($id) {
        // UPDATE: Qualquer um pode editar
        $sql = "UPDATE comentarios SET receita_id = $receita_id, nome = '$nome', nota = $nota, texto = '$texto' WHERE id = $id";
        $mysqli->query($sql) or die($mysqli->error);
        $mensagem = "O comentário foi corrigido com sucesso!";
    } else {
        // CREATE: Novo comentário
        $sql = "INSERT INTO comentarios (receita_id, nome, nota, texto) VALUES ($receita_id, '$nome', $nota, '$texto')";
        $mysqli->query($sql) or die($mysqli->error);
        $mensagem = "Avaliação enviada! Obrigado por compartilhar sua experiência.";
    }
    $acao = 'listar'; 
}

// DELETE: Remover comentário ofensivo ou spam
if ($acao === 'deletar' && $id_atual) {
    $sql = "DELETE FROM comentarios WHERE id = $id_atual";
    $mysqli->query($sql) or die($mysqli->error);
    $mensagem = "O comentário foi removido do sistema.";
    $acao = 'listar';
}

// Buscar comentário específico para editar
$comentario_selecionado = null;
if ($acao === 'editar' && $id_atual) {
    $sql = "SELECT * FROM comentarios WHERE id = $id_atual";
    $resultado = $mysqli->query($sql) or die($mysqli->error);
    $comentario_selecionado = $resultado->fetch_assoc();
}

// READ: Buscar todos os comentários (Do mais recente para o mais antigo)
// Usamos um JOIN para trazer o nome da receita avaliada
$sql_todos = "SELECT c.*, r.titulo as nome_receita 
              FROM comentarios c 
              JOIN receitas r ON c.receita_id = r.id 
              ORDER BY c.id DESC";
$lista_comentarios = $mysqli->query($sql_todos) or die($mysqli->error);

// Buscar receitas para o formulário (Dropdown)
$sql_receitas = "SELECT id, titulo FROM receitas ORDER BY titulo ASC";
$lista_receitas_form = $mysqli->query($sql_receitas) or die($mysqli->error);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="imagens/iconAba.ico">
    <title>Comentários - Receitas do Gonger</title>
</head>
<body>

<div class="container-geral">
    
    <aside class="sidebar">
        <div class="logo-area">
            <a href="receitas.php" class="logo-placeholder"><img src="imagens/iconGrande.png" width="150" height="120" alt="Logo Gonger"></a>
        </div>
        
        <nav class="menu-nav">
            <a href="receitas.php" class="menu-item">
                <span class="icon"><img src="imagens/receitas.png" width="36" height="36" alt="Icone Receitas"></span> Receitas
            </a>
            <a href="comentarios.php" class="menu-item active">
                <span class="icon"><img src="imagens/comentarios.png" width="36" height="36" alt="Icone Comentarios"></span> Comentários
            </a>
            <a href="restaurantes.php" class="menu-item">
                <span class="icon"><img src="imagens/restaurantes.png" width="36" height="36" alt="Icone Restaurantes"></span> Restaurantes
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
                <h2>Encontre aqui as avaliações de nossas receitas e nos ajude deixando a sua:</h2>
            </div>
            <div class="banner-acoes">
                <a href="comentarios.php?acao=novo" class="btn-adicionar">+ AVALIAR RECEITA</a>
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
                    <?php echo $acao === 'editar' ? 'Corrigindo comentário...' : 'Deixe sua avaliação...'; ?>
                </h1>
                
                <form action="comentarios.php" method="POST" class="form-receita">
                    <input type="hidden" name="id" value="<?php echo $comentario_selecionado['id'] ?? ''; ?>">
                    
                    <p>
                        <label>Qual receita você testou?</label>
                        <select name="receita_id" required>
                            <option value="">-- Selecione uma receita --</option>
                            <?php while($rec = $lista_receitas_form->fetch_assoc()): ?>
                                <option value="<?php echo $rec['id']; ?>" 
                                    <?php echo (isset($comentario_selecionado) && $comentario_selecionado['receita_id'] == $rec['id']) ? 'selected' : ''; ?>>
                                    <?php echo $rec['titulo']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </p>

                    <p>
                        <label>Seu Nome</label>
                        <input type="text" name="nome" placeholder="Ex: Chef Elmo" value="<?php echo $comentario_selecionado['nome'] ?? ''; ?>" required>
                    </p>

                    <p>
                        <label>Nota (1 a 5 estrelas)</label>
                        <select name="nota" required>
                            <option value="5" <?php echo (isset($comentario_selecionado) && $comentario_selecionado['nota'] == 5) ? 'selected' : ''; ?>>⭐⭐⭐⭐⭐ (5) Perfeito!</option>
                            <option value="4" <?php echo (isset($comentario_selecionado) && $comentario_selecionado['nota'] == 4) ? 'selected' : ''; ?>>⭐⭐⭐⭐ (4) Muito bom</option>
                            <option value="3" <?php echo (isset($comentario_selecionado) && $comentario_selecionado['nota'] == 3) ? 'selected' : ''; ?>>⭐⭐⭐ (3) Gostoso</option>
                            <option value="2" <?php echo (isset($comentario_selecionado) && $comentario_selecionado['nota'] == 2) ? 'selected' : ''; ?>>⭐⭐ (2) Poderia melhorar</option>
                            <option value="1" <?php echo (isset($comentario_selecionado) && $comentario_selecionado['nota'] == 1) ? 'selected' : ''; ?>>⭐ (1) Não deu certo</option>
                        </select>
                    </p>

                    <p>
                        <label>O que você achou?</label>
                        <textarea name="texto" rows="4" placeholder="Conte como foi fazer essa receita..." required><?php echo $comentario_selecionado['texto'] ?? ''; ?></textarea>
                    </p>

                    <div class="acoes-form">
                        <a href="comentarios.php" class="btn-cancelar">Cancelar</a>
                        <button type="submit" class="btn-salvar">Publicar Avaliação</button>
                    </div>
                </form>

            <?php else: ?>

                <h1 class="titulo-sessao">Mural de Avaliações</h1>

                <?php if ($lista_comentarios->num_rows == 0): ?>
                    <div class="aviso-vazio">
                        <h3>Nenhum prato foi avaliado ainda...</h3>
                        <p>Seja o primeiro a contar o que achou clicando em <strong>"Avaliar Receita"</strong> ali em cima!</p>
                    </div>
                <?php else: ?>
                    <div class="grade-comentarios">
                        <?php while($c = $lista_comentarios->fetch_assoc()): ?>
                            <div class="card-comentario">
                                <div class="card-topo">
                                    <span class="card-nome"><?php echo htmlspecialchars($c['nome']); ?></span>
                                    <span class="card-estrelas"><?php echo str_repeat('⭐', $c['nota']); ?></span>
                                </div>
                                
                                <p class="card-receita-alvo">🥘 Receita: <strong><?php echo $c['nome_receita']; ?></strong></p>
                                
                                <div class="card-texto">
                                    "<?php echo nl2br(htmlspecialchars($c['texto'])); ?>"
                                </div>
                                
                                <div class="card-acoes">
                                    <a href="comentarios.php?acao=editar&id=<?php echo $c['id']; ?>" class="btn-acao-simples">Editar</a>
                                    <a href="comentarios.php?acao=deletar&id=<?php echo $c['id']; ?>" class="btn-acao-denunciar" onclick="return confirm('Este comentário é ofensivo ou spam? Tem certeza que deseja removê-lo?');">Denunciar/Remover</a>
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

    /* CSS Principal (Mantendo a identidade do receitas.php) */
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
        padding-left: 20px;
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
        border-left: 5px solid #3b82f6;
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

    /* Listagem de Comentários (Design dos Cards) */
    .grade-comentarios {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .card-comentario {
        background: #fdfbf7;
        border: 1px solid #eadeca;
        padding: 20px;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .card-topo {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px dashed #d1c4b2;
        padding-bottom: 8px;
    }

    .card-nome {
        font-weight: 700;
        color: #8c2d19;
        font-size: 15px;
    }

    .card-estrelas {
        font-size: 14px;
    }

    .card-receita-alvo {
        font-size: 12px;
        color: #d95b3f;
        text-transform: uppercase;
        font-weight: 600;
    }

    .card-texto {
        font-size: 14px;
        color: #42342b;
        line-height: 1.5;
        font-style: italic;
        padding: 10px 0;
    }

    .card-acoes {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        margin-top: 5px;
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
    .btn-acao-denunciar:hover { color: #8c0000; text-decoration: none }

    .aviso-vazio { color: #6e5645; }
    .aviso-vazio strong { color: #d95b3f; }

</style>
</body>
</html>