<?php
// ==============================================
// jogo.php - Lógica Principal do Jogo
// ==============================================

class Jogo {
    private Database $db;
    private int $tamanho;

    public function __construct(Database $db) {
        $this->db     = $db;
        $this->tamanho = TAMANHO_TABULEIRO;
    }

    // ─────────────────────────────────────────
    // PARTIDA
    // ─────────────────────────────────────────

    public function criarPartida(string $sessaoId): array {
        $antiga = $this->db->fetchOne("SELECT id FROM partidas WHERE sessao_id = ?", [$sessaoId]);
        if ($antiga) {
            $this->db->query("DELETE FROM partidas WHERE id = ?", [$antiga['id']]);
        }

        $id = $this->db->insert(
            "INSERT INTO partidas (sessao_id, status) VALUES (?, 'configurando')",
            [$sessaoId]
        );

        $this->posicionarBarcosIA($id);

        return ['partida_id' => $id, 'status' => 'configurando'];
    }

    public function getPartida(string $sessaoId): ?array {
        return $this->db->fetchOne(
            "SELECT * FROM partidas WHERE sessao_id = ?",
            [$sessaoId]
        );
    }

    // ─────────────────────────────────────────
    // POSICIONAMENTO
    // ─────────────────────────────────────────

    public function posicionarBarcosJogador(int $partidaId, array $barcos): array {
        $this->db->query(
            "DELETE FROM barcos WHERE partida_id = ? AND dono = 'jogador'",
            [$partidaId]
        );

        $tabuleiro = array_fill(0, $this->tamanho, array_fill(0, $this->tamanho, false));

        foreach ($barcos as $barco) {
            if (!$this->validarPosicao($barco, $tabuleiro)) {
                return ['erro' => 'Posicao invalida para o barco ' . $barco['tipo']];
            }
            $this->marcarTabuleiro($tabuleiro, $barco['posicoes']);
            $this->db->insert(
                "INSERT INTO barcos (partida_id, dono, tipo, tamanho, posicoes) VALUES (?, 'jogador', ?, ?, ?)",
                [$partidaId, $barco['tipo'], $barco['tamanho'], json_encode($barco['posicoes'])]
            );
        }

        $this->db->query(
            "UPDATE partidas SET status = 'jogando' WHERE id = ?",
            [$partidaId]
        );

        return ['sucesso' => true, 'status' => 'jogando'];
    }

    private function posicionarBarcosIA(int $partidaId): void {
        $config    = json_decode(BARCOS_CONFIG, true);
        $tabuleiro = array_fill(0, $this->tamanho, array_fill(0, $this->tamanho, false));

        foreach ($config as $cfg) {
            for ($q = 0; $q < $cfg['qtd']; $q++) {
                $posicoes = $this->gerarPosicaoAleatoria($cfg['tamanho'], $tabuleiro);
                $this->marcarTabuleiro($tabuleiro, $posicoes);
                $this->db->insert(
                    "INSERT INTO barcos (partida_id, dono, tipo, tamanho, posicoes) VALUES (?, 'ia', ?, ?, ?)",
                    [$partidaId, $cfg['tipo'], $cfg['tamanho'], json_encode($posicoes)]
                );
            }
        }
    }

    private function gerarPosicaoAleatoria(int $tamanho, array &$tabuleiro): array {
        $tentativas = 0;
        do {
            $horizontal = rand(0, 1) === 1;
            if ($horizontal) {
                $linha    = rand(0, $this->tamanho - 1);
                $col      = rand(0, $this->tamanho - $tamanho);
                $posicoes = [];
                for ($i = 0; $i < $tamanho; $i++) {
                    $posicoes[] = ['l' => $linha, 'c' => $col + $i];
                }
            } else {
                $linha    = rand(0, $this->tamanho - $tamanho);
                $col      = rand(0, $this->tamanho - 1);
                $posicoes = [];
                for ($i = 0; $i < $tamanho; $i++) {
                    $posicoes[] = ['l' => $linha + $i, 'c' => $col];
                }
            }
            $tentativas++;
        } while (!$this->posicaoLivre($posicoes, $tabuleiro) && $tentativas < MAX_TENTATIVAS_POSICAO);

        return $posicoes;
    }

    private function validarPosicao(array $barco, array $tabuleiro): bool {
        foreach ($barco['posicoes'] as $pos) {
            if ($pos['l'] < 0 || $pos['l'] >= $this->tamanho ||
                $pos['c'] < 0 || $pos['c'] >= $this->tamanho) {
                return false;
            }
        }
        return $this->posicaoLivre($barco['posicoes'], $tabuleiro);
    }

    private function posicaoLivre(array $posicoes, array $tabuleiro): bool {
        foreach ($posicoes as $pos) {
            if ($tabuleiro[$pos['l']][$pos['c']]) return false;
        }
        return true;
    }

    private function marcarTabuleiro(array &$tabuleiro, array $posicoes): void {
        foreach ($posicoes as $pos) {
            $tabuleiro[$pos['l']][$pos['c']] = true;
        }
    }

    // ─────────────────────────────────────────
    // TIROS
    // ─────────────────────────────────────────

    public function atirarJogador(int $partidaId, int $linha, int $col): array {
        $jaAtirou = $this->db->fetchOne(
            "SELECT id FROM tiros WHERE partida_id = ? AND atirador = 'jogador' AND linha = ? AND coluna = ?",
            [$partidaId, $linha, $col]
        );
        if ($jaAtirou) {
            return ['erro' => 'Voce ja atirou nessa posicao!'];
        }

        $resultado     = 'agua';
        $barcoAfundado = null;
        $barcos        = $this->db->fetchAll(
            "SELECT * FROM barcos WHERE partida_id = ? AND dono = 'ia' AND afundado = 0",
            [$partidaId]
        );

        $barcoAcertado = null;
        foreach ($barcos as $barco) {
            $posicoes = json_decode($barco['posicoes'], true);
            foreach ($posicoes as $pos) {
                if ($pos['l'] === $linha && $pos['c'] === $col) {
                    $barcoAcertado = $barco;
                    $resultado     = 'acerto';
                    break 2;
                }
            }
        }

        if ($barcoAcertado) {
            $tirosNesseBarco   = $this->getTirosNosBarco($partidaId, 'jogador', $barcoAcertado);
            $tirosNesseBarco[] = ['l' => $linha, 'c' => $col];
            $posicoes          = json_decode($barcoAcertado['posicoes'], true);

            if (count($tirosNesseBarco) >= count($posicoes)) {
                $resultado     = 'afundou';
                $barcoAfundado = $barcoAcertado['tipo'];
                $this->db->query(
                    "UPDATE barcos SET afundado = 1 WHERE id = ?",
                    [$barcoAcertado['id']]
                );
            }
        }

        $this->db->insert(
            "INSERT INTO tiros (partida_id, atirador, linha, coluna, resultado) VALUES (?, 'jogador', ?, ?, ?)",
            [$partidaId, $linha, $col, $resultado]
        );

        $this->db->query(
            "UPDATE partidas SET tiros_jogador = tiros_jogador + 1" .
            ($resultado !== 'agua' ? ", acertos_jogador = acertos_jogador + 1" : "") .
            " WHERE id = ?",
            [$partidaId]
        );

        $venceu = $this->verificarVitoria($partidaId, 'ia');
        if ($venceu) {
            $this->db->query(
                "UPDATE partidas SET status = 'finalizada', vencedor = 'jogador' WHERE id = ?",
                [$partidaId]
            );
        }

        return [
            'resultado'      => $resultado,
            'barco_afundado' => $barcoAfundado,
            'venceu'         => $venceu,
        ];
    }

    // atirarIA sem Gemini — comentarios locais para economizar tokens
    public function atirarIA(int $partidaId): array {
        $tirosAnteriores = $this->db->fetchAll(
            "SELECT linha, coluna, resultado FROM tiros WHERE partida_id = ? AND atirador = 'ia'",
            [$partidaId]
        );

        $atirados = [];
        foreach ($tirosAnteriores as $t) {
            $atirados[$t['linha'] . '_' . $t['coluna']] = true;
        }

        $alvo = $this->estrategiaIA($tirosAnteriores, $atirados);

        $resultado = 'agua';
        $barcos    = $this->db->fetchAll(
            "SELECT * FROM barcos WHERE partida_id = ? AND dono = 'jogador' AND afundado = 0",
            [$partidaId]
        );

        $barcoAcertado = null;
        foreach ($barcos as $barco) {
            $posicoes = json_decode($barco['posicoes'], true);
            foreach ($posicoes as $pos) {
                if ($pos['l'] === $alvo['l'] && $pos['c'] === $alvo['c']) {
                    $barcoAcertado = $barco;
                    $resultado     = 'acerto';
                    break 2;
                }
            }
        }

        $barcoAfundado = null;
        if ($barcoAcertado) {
            $tirosNesseBarco   = $this->getTirosNosBarco($partidaId, 'ia', $barcoAcertado);
            $tirosNesseBarco[] = $alvo;
            $posicoes          = json_decode($barcoAcertado['posicoes'], true);

            if (count($tirosNesseBarco) >= count($posicoes)) {
                $resultado     = 'afundou';
                $barcoAfundado = $barcoAcertado['tipo'];
                $this->db->query(
                    "UPDATE barcos SET afundado = 1 WHERE id = ?",
                    [$barcoAcertado['id']]
                );
            }
        }

        // Comentario local — SEM chamada ao Gemini aqui
        $comentario = $this->comentarioLocalTiro($resultado);

        $this->db->insert(
            "INSERT INTO tiros (partida_id, atirador, linha, coluna, resultado, mensagem_ia) VALUES (?, 'ia', ?, ?, ?, ?)",
            [$partidaId, $alvo['l'], $alvo['c'], $resultado, $comentario]
        );

        $this->db->query(
            "UPDATE partidas SET tiros_ia = tiros_ia + 1" .
            ($resultado !== 'agua' ? ", acertos_ia = acertos_ia + 1" : "") .
            " WHERE id = ?",
            [$partidaId]
        );

        $venceu = $this->verificarVitoria($partidaId, 'jogador');
        if ($venceu) {
            $this->db->query(
                "UPDATE partidas SET status = 'finalizada', vencedor = 'ia' WHERE id = ?",
                [$partidaId]
            );
        }

        $letras = ['A','B','C','D','E','F','G','H','I','J'];
        return [
            'linha'          => $alvo['l'],
            'coluna'         => $alvo['c'],
            'posicao_texto'  => $letras[$alvo['c']] . ($alvo['l'] + 1),
            'resultado'      => $resultado,
            'barco_afundado' => $barcoAfundado,
            'comentario'     => $comentario,
            'venceu'         => $venceu,
        ];
    }

    // ─────────────────────────────────────────
    // COMENTARIOS LOCAIS DO ALMIRANTE ZEUS
    // ─────────────────────────────────────────

    private function comentarioLocalTiro(string $resultado): string {
        $frases = [
            'agua' => [
                "Malditas ondas! Meu tiro desviou, mas minha proxima bala e sua!",
                "Agua! Bah! Ate os peixes riram de mim agora... mas nao por muito tempo!",
                "Errei?! IMPOSSIVEL! O vento traiu meu canhao... desta vez!",
                "Splash! Alimentei os polvos... mas voce ainda esta no meu radar, recruta!",
                "Agua salgada molhou minha bala. Mas minha raiva esta mais quente que nunca!",
                "Falha temporaria! O Almirante ZEUS nao erra duas vezes seguidas!",
                "O mar me desafiou! Amanha ele vai se arrepender junto com voce!",
            ],
            'acerto' => [
                "ACERTEI! Sinto o cheiro de ferro queimado... e e delicioso!",
                "HA! Meu canhao e infalivel! Sua frota esta condenada, recruta!",
                "DIRETO NO ALVO! Cada tiro meu e uma obra de arte da destruicao!",
                "Sentiu isso? E o toque carinhoso do Almirante ZEUS!",
                "Acerto confirmado! Continue tentando escapar... e divertido te ver tentar!",
                "BOOM! Mais um presente da minha artilharia para voce!",
                "Isso e apenas o começo, recruta. Minha mira so melhora!",
            ],
            'afundou' => [
                "AFUNDOU! GLUGLUGLUGLU! Mais um barco no fundo do mar onde merece estar!",
                "NAUFRAGIO CONFIRMADO! Os caranguejos agradecem o jantar, recruta!",
                "FUI UM COM O MAR... mas foram SEUS barcos! HAHAHA!",
                "Outro navio destruido! Minha colecao de destrocos esta ficando magnifica!",
                "AFUNDADO! Poseidon mesmo aplaudiu meu tiro! Sua frota e historia!",
                "DESTRUIDO! Aquele barco agora e um recife artificial. De nada, peixes!",
                "GLUB GLUB! Adeus, navio patético! O Almirante ZEUS e implacavel!",
            ],
        ];

        $lista = $frases[$resultado] ?? $frases['agua'];
        return $lista[array_rand($lista)];
    }

    // ─────────────────────────────────────────
    // ESTRATEGIA 
    // ─────────────────────────────────────────

    // ═══════════════════════════════════════════════════════════════
    // PROBABILITY DENSITY FUNCTION (PDF) — Algoritmo de elite
    // Mediana: ~42 tiros. Referência: Nick Berry / DataGenetics
    //
    // Funcionamento:
    //   1. Para cada célula livre, conta quantas configurações dos
    //      barcos RESTANTES ainda cabem ali (horizontal + vertical)
    //   2. Em modo CAÇA (acerto ativo), multiplica o peso das células
    //      adjacentes ao acerto, forçando a IA a afundar o barco
    //   3. Escolhe sempre a célula de maior densidade — sem aleatoriedade
    // ═══════════════════════════════════════════════════════════════
    private function estrategiaIA(array $tirosAnteriores, array $atirados): array {
        $T = $this->tamanho;

        // Monta mapas de estado para acesso O(1)
        $acertado = [];  // células com acerto (barco atingido mas não afundado)
        $errado   = [];  // células com água
        foreach ($tirosAnteriores as $t) {
            $k = (int)$t['linha'] . '_' . (int)$t['coluna'];
            if ($t['resultado'] === 'acerto') {
                $acertado[$k] = true;
            } elseif ($t['resultado'] === 'agua') {
                $errado[$k] = true;
            }
            // 'afundou' fica em $atirados mas NÃO em $acertado
        }

        // Tamanhos dos barcos ainda não afundados
        // (barcos com resultado 'afundou' já foram removidos dos acertos)
        $barcosRestantes = $this->getBarcosRestantesIA($tirosAnteriores);

        // ── Inicializa grade de probabilidade com zero ──────────────
        $prob = [];
        for ($l = 0; $l < $T; $l++) {
            for ($c = 0; $c < $T; $c++) {
                $prob[$l][$c] = 0;
            }
        }

        // ── Para cada barco restante, tenta todas as posições válidas ──
        foreach ($barcosRestantes as $tamanho) {
            // Tentativa horizontal
            for ($l = 0; $l < $T; $l++) {
                for ($c = 0; $c <= $T - $tamanho; $c++) {
                    if ($this->posicaoValida_PDF($l, $c, $tamanho, true, $atirados, $errado, $acertado, $T)) {
                        // Conta quantas células dessa configuração têm acerto
                        $hits = 0;
                        for ($i = 0; $i < $tamanho; $i++) {
                            if (isset($acertado[$l . '_' . ($c + $i)])) $hits++;
                        }
                        // Configurações que cobrem acertos valem mais
                        $peso = ($hits > 0) ? pow(10, $hits) : 1;
                        for ($i = 0; $i < $tamanho; $i++) {
                            if (!isset($atirados[$l . '_' . ($c + $i)])) {
                                $prob[$l][$c + $i] += $peso;
                            }
                        }
                    }
                }
            }
            // Tentativa vertical
            for ($l = 0; $l <= $T - $tamanho; $l++) {
                for ($c = 0; $c < $T; $c++) {
                    if ($this->posicaoValida_PDF($l, $c, $tamanho, false, $atirados, $errado, $acertado, $T)) {
                        $hits = 0;
                        for ($i = 0; $i < $tamanho; $i++) {
                            if (isset($acertado[($l + $i) . '_' . $c])) $hits++;
                        }
                        $peso = ($hits > 0) ? pow(10, $hits) : 1;
                        for ($i = 0; $i < $tamanho; $i++) {
                            if (!isset($atirados[($l + $i) . '_' . $c])) {
                                $prob[$l + $i][$c] += $peso;
                            }
                        }
                    }
                }
            }
        }

        // ── Escolhe a célula com maior probabilidade ────────────────
        $melhorProb = -1;
        $melhores   = [];
        for ($l = 0; $l < $T; $l++) {
            for ($c = 0; $c < $T; $c++) {
                if (isset($atirados[$l . '_' . $c])) continue;
                if ($prob[$l][$c] > $melhorProb) {
                    $melhorProb = $prob[$l][$c];
                    $melhores   = [['l' => $l, 'c' => $c]];
                } elseif ($prob[$l][$c] === $melhorProb) {
                    $melhores[] = ['l' => $l, 'c' => $c];
                }
            }
        }

        // Empate: sorteia entre os melhores (evita padrão previsível)
        if (!empty($melhores)) {
            return $melhores[array_rand($melhores)];
        }

        // Fallback absoluto (não deve ocorrer)
        for ($l = 0; $l < $T; $l++) {
            for ($c = 0; $c < $T; $c++) {
                if (!isset($atirados[$l . '_' . $c])) return ['l' => $l, 'c' => $c];
            }
        }
        return ['l' => 0, 'c' => 0];
    }

    // Verifica se um barco CABE nessa posição sem conflito
    private function posicaoValida_PDF(
        int $l, int $c, int $tam, bool $horiz,
        array $atirados, array $errado, array $acertado, int $T
    ): bool {
        for ($i = 0; $i < $tam; $i++) {
            $rl = $horiz ? $l      : $l + $i;
            $rc = $horiz ? $c + $i : $c;
            if ($rl >= $T || $rc >= $T) return false;
            $k = $rl . '_' . $rc;
            // Não pode cair em água confirmada nem em barco já afundado
            if (isset($errado[$k]))   return false;
            if (isset($atirados[$k]) && !isset($acertado[$k])) return false;
        }
        return true;
    }

    // Retorna os tamanhos dos barcos da IA que ainda não foram afundados
    private function getBarcosRestantesIA(array $tirosAnteriores): array {
        // Barcos possíveis no jogo (deve bater com BARCOS_CONFIG)
        $todosBarcos = [];
        foreach (json_decode(BARCOS_CONFIG, true) as $cfg) {
            for ($q = 0; $q < $cfg['qtd']; $q++) {
                $todosBarcos[] = $cfg['tamanho'];
            }
        }

        // Conta células afundadas agrupadas por barco
        // Simplificação conservadora: desconta 1 barco por afundamento registrado
        $afundados = 0;
        foreach ($tirosAnteriores as $t) {
            if ($t['resultado'] === 'afundou') $afundados++;
        }

        // Remove os barcos menores primeiro (heurística)
        sort($todosBarcos);
        for ($i = 0; $i < $afundados && !empty($todosBarcos); $i++) {
            array_shift($todosBarcos);
        }
        return $todosBarcos;
    }

    private function getTirosNosBarco(int $partidaId, string $atirador, array $barco): array {
        $posicoes = json_decode($barco['posicoes'], true);
        $tiros    = $this->db->fetchAll(
            "SELECT linha, coluna FROM tiros WHERE partida_id = ? AND atirador = ? AND resultado != 'agua'",
            [$partidaId, $atirador]
        );

        $acertos = [];
        foreach ($tiros as $tiro) {
            foreach ($posicoes as $pos) {
                if ($pos['l'] === (int)$tiro['linha'] && $pos['c'] === (int)$tiro['coluna']) {
                    $acertos[] = $tiro;
                }
            }
        }
        return $acertos;
    }

    private function verificarVitoria(int $partidaId, string $donoDosBarcos): bool {
        $restantes = $this->db->fetchOne(
            "SELECT COUNT(*) as total FROM barcos WHERE partida_id = ? AND dono = ? AND afundado = 0",
            [$partidaId, $donoDosBarcos]
        );
        return (int)($restantes['total'] ?? 1) === 0;
    }

    // ─────────────────────────────────────────
    // ESTADO DO JOGO
    // ─────────────────────────────────────────

    public function getEstado(int $partidaId, string $dono = 'jogador'): array {
        $partida = $this->db->fetchOne("SELECT * FROM partidas WHERE id = ?", [$partidaId]);
        $barcos  = $this->db->fetchAll(
            "SELECT * FROM barcos WHERE partida_id = ? AND dono = ?",
            [$partidaId, $dono]
        );
        $tiros   = $this->db->fetchAll(
            "SELECT * FROM tiros WHERE partida_id = ? ORDER BY id ASC",
            [$partidaId]
        );
        return compact('partida', 'barcos', 'tiros');
    }

    public function getBarcosIA(int $partidaId): array {
        return $this->db->fetchAll(
            "SELECT posicoes, afundado FROM barcos WHERE partida_id = ? AND dono = 'ia'",
            [$partidaId]
        );
    }

    public function getBarcosJogador(int $partidaId): array {
        return $this->db->fetchAll(
            "SELECT posicoes, afundado, tipo FROM barcos WHERE partida_id = ? AND dono = 'jogador'",
            [$partidaId]
        );
    }

    public function getTirosIA(int $partidaId): array {
        return $this->db->fetchAll(
            "SELECT linha, coluna, resultado FROM tiros WHERE partida_id = ? AND atirador = 'ia'",
            [$partidaId]
        );
    }

    public function getTirosJogador(int $partidaId): array {
        return $this->db->fetchAll(
            "SELECT linha, coluna, resultado FROM tiros WHERE partida_id = ? AND atirador = 'jogador'",
            [$partidaId]
        );
    }

    public function salvarMensagemChat(int $partidaId, string $tipo, string $mensagem): void {
        $this->db->insert(
            "INSERT INTO chat_ia (partida_id, tipo, mensagem) VALUES (?, ?, ?)",
            [$partidaId, $tipo, $mensagem]
        );
    }

    // Retorna posições do barco afundado na célula informada
    public function getPosicoesBarcosAfundados(int $partidaId, string $dono, int $linha, int $col): array {
        $barcos = $this->db->fetchAll(
            "SELECT posicoes FROM barcos WHERE partida_id = ? AND dono = ? AND afundado = 1",
            [$partidaId, $dono]
        );
        foreach ($barcos as $barco) {
            $posicoes = json_decode($barco['posicoes'], true);
            foreach ($posicoes as $pos) {
                if ($pos['l'] === $linha && $pos['c'] === $col) {
                    return $posicoes;
                }
            }
        }
        return [];
    }

    public function getHistoricoChat(int $partidaId): array {
        return $this->db->fetchAll(
            "SELECT tipo, mensagem, criado_em FROM chat_ia WHERE partida_id = ? ORDER BY id ASC LIMIT 20",
            [$partidaId]
        );
    }
}