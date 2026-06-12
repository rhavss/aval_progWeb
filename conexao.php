<?php

$host = 'db'; 
$usuario = 'root';
$senha = 'root'; 
$database = 'bdCrudPw';

try {
    $dsn = "mysql:host=$host;dbname=$database;charset=utf8mb4";
    
    $pdo = new PDO($dsn, $usuario, $senha);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    die("Falha ao conectar ao banco de dados: " . $e->getMessage());
}

?>