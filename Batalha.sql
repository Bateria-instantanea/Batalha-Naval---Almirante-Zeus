-- ==============================================
-- BATALHA NAVAL - Script do Banco de Dados MySQL
-- ==============================================

CREATE DATABASE IF NOT EXISTS Batalha CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE Batalha;

-- Tabela de partidas
CREATE TABLE IF NOT EXISTS partidas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sessao_id VARCHAR(64) NOT NULL UNIQUE,
    status ENUM('configurando', 'jogando', 'finalizada') DEFAULT 'configurando',
    vencedor ENUM('jogador', 'ia') DEFAULT NULL,
    tiros_jogador INT DEFAULT 0,
    tiros_ia INT DEFAULT 0,
    acertos_jogador INT DEFAULT 0,
    acertos_ia INT DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de barcos
CREATE TABLE IF NOT EXISTS barcos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    partida_id INT NOT NULL,
    dono ENUM('jogador', 'ia') NOT NULL,
    tipo ENUM('submarino', 'contratorpedeiro', 'cruzador') NOT NULL,
    tamanho INT NOT NULL,
    posicoes JSON NOT NULL,
    afundado TINYINT(1) DEFAULT 0,
    FOREIGN KEY (partida_id) REFERENCES partidas(id) ON DELETE CASCADE
);

-- Tabela de tiros
CREATE TABLE IF NOT EXISTS tiros (
    id INT AUTO_INCREMENT PRIMARY KEY,
    partida_id INT NOT NULL,
    atirador ENUM('jogador', 'ia') NOT NULL,
    linha INT NOT NULL,
    coluna INT NOT NULL,
    resultado ENUM('agua', 'acerto', 'afundou') NOT NULL,
    mensagem_ia TEXT DEFAULT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (partida_id) REFERENCES partidas(id) ON DELETE CASCADE
);

-- Tabela de chat com IA
CREATE TABLE IF NOT EXISTS chat_ia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    partida_id INT NOT NULL,
    tipo ENUM('pergunta', 'resposta') NOT NULL,
    mensagem TEXT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (partida_id) REFERENCES partidas(id) ON DELETE CASCADE
);

-- Índices para performance
CREATE INDEX idx_partidas_sessao ON partidas(sessao_id);
CREATE INDEX idx_tiros_partida ON tiros(partida_id);
CREATE INDEX idx_chat_partida ON chat_ia(partida_id);



