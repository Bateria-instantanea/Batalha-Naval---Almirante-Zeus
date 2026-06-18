<?php
// ==============================================
// api.php - Endpoints da API (JSON)
// ==============================================

require_once 'config.php';
require_once 'db.php';
require_once 'gemini.php';
require_once 'jogo.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$db   = Database::getInstance();
$jogo = new Jogo($db);
$ia   = new GeminiIA();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {

        // ── Nova Partida ──────────────────────────
        case 'nova_partida':
            $result = $jogo->criarPartida($_SESSION['id']);
            echo json_encode($result);
            break;

        // ── Posicionar Barcos do Jogador ──────────
        case 'posicionar_barcos':
            $partida = $jogo->getPartida($_SESSION['id']);
            if (!$partida) {
                echo json_encode(['erro' => 'Partida não encontrada.']);
                break;
            }
            $barcos = json_decode($_POST['barcos'] ?? '[]', true);
            if (empty($barcos)) {
                echo json_encode(['erro' => 'Nenhum barco enviado.']);
                break;
            }
            $result = $jogo->posicionarBarcosJogador($partida['id'], $barcos);
            echo json_encode($result);
            break;

        // ── Tiro do Jogador + Resposta da IA ─────
        case 'atirar':
            $partida = $jogo->getPartida($_SESSION['id']);
            if (!$partida || $partida['status'] !== 'jogando') {
                echo json_encode(['erro' => 'Partida não está em andamento.']);
                break;
            }

            $linha = (int)($_POST['linha'] ?? -1);
            $col   = (int)($_POST['coluna'] ?? -1);

            if ($linha < 0 || $linha >= TAMANHO_TABULEIRO || $col < 0 || $col >= TAMANHO_TABULEIRO) {
                echo json_encode(['erro' => 'Posição inválida.']);
                break;
            }

            $resJogador = $jogo->atirarJogador($partida['id'], $linha, $col);
            if (isset($resJogador['erro'])) {
                echo json_encode($resJogador);
                break;
            }

            $resIA = null;
            if (!$resJogador['venceu']) {
                $resIA = $jogo->atirarIA($partida['id']);
            // Adiciona posições do barco afundado da IA para animação
            if ($resIA && $resIA['resultado'] === 'afundou') {
                $resIA['posicoes_afundadas'] = $jogo->getPosicoesBarcosAfundados($partida['id'], 'jogador', $resIA['linha'], $resIA['coluna']);
            }
            }

            // Adiciona posições do barco afundado do jogador para animação
            if ($resJogador['resultado'] === 'afundou') {
                $resJogador['posicoes_afundadas'] = $jogo->getPosicoesBarcosAfundados($partida['id'], 'ia', $linha, $col);
            }

            echo json_encode([
                'jogador' => $resJogador,
                'ia'      => $resIA,
            ]);
            break;

        // ── Obter Estado Completo ─────────────────
        case 'estado':
            $partida = $jogo->getPartida($_SESSION['id']);
            if (!$partida) {
                echo json_encode(['erro' => 'Partida não encontrada.']);
                break;
            }

            $barcosJogador = $jogo->getBarcosJogador($partida['id']);
            $barcosIA      = $jogo->getBarcosIA($partida['id']);
            $tirosJogador  = $jogo->getTirosJogador($partida['id']);
            $tirosIA       = $jogo->getTirosIA($partida['id']);

            // Barcos da IA: só mostra posições se afundados
            $barcosIAFiltrado = array_map(function($b) {
                return [
                    'posicoes' => $b['afundado'] ? json_decode($b['posicoes'], true) : [],
                    'afundado' => (bool)$b['afundado'],
                ];
            }, $barcosIA);

            echo json_encode([
                'partida'       => $partida,
                'barcos_jogador'=> array_map(fn($b) => [
                    'posicoes' => json_decode($b['posicoes'], true),
                    'afundado' => (bool)$b['afundado'],
                    'tipo'     => $b['tipo'],
                ], $barcosJogador),
                'barcos_ia'     => $barcosIAFiltrado,
                'tiros_jogador' => $tirosJogador,
                'tiros_ia'      => $tirosIA,
                'chat'          => $jogo->getHistoricoChat($partida['id']),
            ]);
            break;

        // ── Chat com IA ───────────────────────────
        case 'chat':
            $partida = $jogo->getPartida($_SESSION['id']);
            if (!$partida) {
                echo json_encode(['erro' => 'Partida não encontrada. Inicie uma nova partida.']);
                break;
            }

            $pergunta = trim($_POST['mensagem'] ?? '');
            if (empty($pergunta)) {
                echo json_encode(['erro' => 'Mensagem vazia.']);
                break;
            }
            if (mb_strlen($pergunta) > 300) {
                echo json_encode(['erro' => 'Mensagem muito longa (máx. 300 caracteres).']);
                break;
            }

            $jogo->salvarMensagemChat($partida['id'], 'pergunta', $pergunta);
            $contexto = "Status da partida: {$partida['status']}. "
                      . "Tiros do jogador: {$partida['tiros_jogador']}, acertos: {$partida['acertos_jogador']}. "
                      . "Meus tiros (IA): {$partida['tiros_ia']}, acertos: {$partida['acertos_ia']}.";
            $resposta = $ia->perguntar($pergunta, $contexto);
            $jogo->salvarMensagemChat($partida['id'], 'resposta', $resposta);

            $result = ['resposta' => $resposta];
            // Inclui erro de debug se houve fallback
            if (!empty($ia->getUltimoErro())) {
                $result['_debug_erro'] = $ia->getUltimoErro();
            }
            echo json_encode($result);
            break;

        // ── Debug Gemini (acesse: api.php?action=debug_gemini) ────
        case 'debug_gemini':
            $teste   = $ia->perguntar('Diga apenas: ZEUS ONLINE', 'teste de conexão');
            $erro    = $ia->getUltimoErro();
            $chaveOk = (GEMINI_API_KEY !== 'SUA_CHAVE_GEMINI_AQUI' && !empty(GEMINI_API_KEY));
            echo json_encode([
                'chave_configurada' => $chaveOk,
                'chave_prefixo'     => $chaveOk ? substr(GEMINI_API_KEY, 0, 8) . '...' : 'NÃO CONFIGURADA',
                'url'               => GEMINI_API_URL,
                'resposta'          => $teste,
                'erro'              => $erro ?: 'nenhum',
                'curl_disponivel'   => function_exists('curl_init'),
            ]);
            break;

        default:
            echo json_encode(['erro' => 'Ação desconhecida.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro interno: ' . $e->getMessage()]);
}