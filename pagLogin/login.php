<?php
include('conexao.php');

$erro = "";

if(isset($_POST['email']) || isset($_POST['senha'])) {

    if(strlen($_POST['email']) == 0) {
        $erro = "Você esqueceu de preencher seu email";
    } else if(strlen($_POST['senha']) == 0) {
        $erro = "Você esqueceu de preencher sua senha";
    } else {

        $email = $mysqli->real_escape_string($_POST['email']);
        $senha = $mysqli->real_escape_string($_POST['senha']);

        $sql_code = "SELECT * FROM usuarios WHERE email = '$email' AND senha = '$senha'";
        $sql_query = $mysqli->query($sql_code) or die("Falha na execução do código SQL: " . $mysqli->error);

        $quantidade = $sql_query->num_rows;

        if($quantidade == 1) {
            $usuario = $sql_query->fetch_assoc();

            if(!isset($_SESSION)) {
                session_start();
            }

            $_SESSION['id'] = $usuario['id'];
            $_SESSION['nome'] = $usuario['nome'];

            header("Location: ../pagReceitas/receitas.php");
            exit();
        } else {
            $erro = "Seu e-mail ou sua senha estão incorretos";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../imagens/iconAba.ico">
    <title>Login - Receitas do Gonger</title>
</head>
<body>
    
<form action="" method="POST">
    <div class="titulo">

        <img class="img2" src="../imagens/icon.png">
        <h1>Acesse o livro de receitas online de Gonger</h1>

    </div>

    <?php if(!empty($erro)): ?>

        <div class="mensagem-erro" style="color: #dd5d5d; font-weight: bold; margin-bottom: 20px;">
            <?php echo $erro; ?>
        </div>

    <?php endif; ?>
    
    <div class="conteudo-form">

        <div class="camposLogin">
            <p>
                <label>E-mail</label>
                <input type="text" name="email">
            </p>
            <p>
                <label>Senha</label>
                <input type="password" name="senha">
            </p>
            <button type="submit">Entrar</button>
        </div>

        <img class="img1" src="../imagens/imgForm.png">

    </div> 
</form>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Crimson+Pro:ital,wght@0,400;0,600;1,400&family=Montserrat:wght@400;700&display=swap');

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Crimson Pro', Georgia, serif;
        background-color: #f4eedb;
        background-image: url(../imagens/bg.png);
        background-position: center;
        background-size: cover;
        color: #42342b;
        min-height: 97vh;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }

    form {
        background-color: #fffdf9;
        border: 1px solid #d1c4b2;
        border-left: 22px solid #8c2d19;
        border-radius: 6px 16px 16px 6px;
        padding: 40px 35px;
        width: 100%;
        max-width: 650px;
        box-shadow: 0 10px 25px rgba(84, 59, 45, 0.2),
                    inset 1px 0 0 rgba(255, 255, 255, 0.2);
    }

    .conteudo-form {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 25px;
        width: 100%;
    }

    h1 {
        font-family: 'Crimson Pro', Georgia, serif;
        font-size: 25.3px;
        color: #8c2d19; 
        text-align: center;
        font-weight: 600;
        border-bottom: 5px double #e0d5c1;
        padding-bottom: 12px;
        letter-spacing: 0.5px;
    }

    .camposLogin {
        flex: 1;
    }

    .titulo {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .img1 {
        display: block;
        width: 282px;
        height: 230px;
        object-fit: cover; 
        border: 1px solid #d1c4b2;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(84, 59, 45, 0.2);
    }

    .img2 {
        display: block;
        width: 80px;
        height: 80px;
    }

    form::after {
        content: '';
        position: absolute;
        top: 0;
        left: -12px;
        bottom: 0;
        width: 2px;
        border-left: 2px dashed #d9a05b;
        opacity: 0.6;
    }

    p {
        margin-bottom: 20px;
    }

    label {
        display: block;
        font-family: 'Montserrat', sans-serif;
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 700;
        color: #6e5645;
        margin-bottom: 6px;
        letter-spacing: 1px;
    }

    input[type="text"],
    input[type="password"] {
        width: 100%;
        padding: 12px 14px;
        font-family: 'Crimson Pro', serif;
        font-size: 16px;
        border: 1px solid #c2b29d;
        border-radius: 4px;
        background-color: #faf8f2;
        color: #33261c;
        transition: all 0.3s ease;
    }

    input[type="text"]:focus,
    input[type="password"]:focus {
        outline: none;
        border-color: #8c2d19;
        background-color: #ffffff;
        box-shadow: 0 0 6px rgba(140, 45, 25, 0.2);
    }

    button[type="submit"] {
        width: 100%;
        background-color: #d95a36;
        color: #ffffff;
        border: none;
        border-radius: 30px;
        padding: 14px;
        font-family: 'Montserrat', sans-serif;
        font-size: 13px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        cursor: pointer;
        box-shadow: 0 4px 0 #b33e1d;
        transition: all 0.1s ease;
        margin-top: 10px;
    }

    button[type="submit"]:hover {
        background-color: #e66540;
    }

    button[type="submit"]:active {
        transform: translateY(3px);
        box-shadow: 0 1px 0 #b33e1d;
    }
</style>

</body>
</html>