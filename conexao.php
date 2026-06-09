<?php

$host = 'db'; 
$usuario = 'root';
$senha = 'root'; 
$database = 'bdCrudPw';

$mysqli = new mysqli($host, $usuario, $senha, $database);

if($mysqli->error) {
    die("Falha ao conectar ao banco de dados: " . $mysqli->error);
}
?>