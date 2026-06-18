<?php
// ==============================================
// config.php - Configurações do Sistema
// ==============================================

// Configurações do Banco de Dados MySQL
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'Batalha');
define('DB_USER', 'root');       // ⚠️ Altere para seu usuário
define('DB_PASS', '');           // ⚠️ Altere para sua senha

// API Gemini
define('GEMINI_API_KEY', 'AIzaSyANPCeGAlFwrSsQE2GK9S1X2Lx8HenqNX0');  // ⚠️ Coloque sua chave aqui
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent');

// Configurações do Jogo
define('TAMANHO_TABULEIRO', 10);
define('MAX_TENTATIVAS_POSICAO', 100);

// Barcos disponíveis: [nome, tamanho, quantidade]
define('BARCOS_CONFIG', json_encode([
    ['tipo' => 'submarino',         'nome' => 'Submarino',         'tamanho' => 1, 'qtd' => 2, 'emoji' => '🚢'],
    ['tipo' => 'contratorpedeiro',  'nome' => 'Contratorpedeiro',  'tamanho' => 2, 'qtd' => 2, 'emoji' => '⚓'],
    ['tipo' => 'cruzador',          'nome' => 'Cruzador',          'tamanho' => 3, 'qtd' => 1, 'emoji' => '🛳️'],
]));

// Sessão
session_start();
if (empty($_SESSION['id'])) {
    $_SESSION['id'] = bin2hex(random_bytes(32));
}