<?php

$host = 'localhost';
$nome = '';
$ingredientes = '';
$preparo = '';
$database = 'telareceitas';

$mysqli = new mysqli($host, $nome, $ingredientes, $preparo, $database);

if($mysqli->error) {
    die("Falha ao conectar ao banco de dados: " . $mysqli->error);
}