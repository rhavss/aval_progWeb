CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    senha VARCHAR(100) NOT NULL
);

INSERT INTO usuarios (email, senha) VALUES ('gonger@gmail.com', '123456');

CREATE TABLE IF NOT EXISTS receitas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    ingredientes TEXT NOT NULL,
    preparo TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS comentarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receita_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    nota INT NOT NULL CHECK(nota >= 1 AND nota <= 5),
    texto TEXT NOT NULL,
    FOREIGN KEY (receita_id) REFERENCES receitas(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS restaurantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    tipo_comida VARCHAR(50) NOT NULL,
    localizacao VARCHAR(150) NOT NULL,
    nota INT NOT NULL CHECK(nota >= 1 AND nota <= 5),
    descricao TEXT NOT NULL
);