<?php
// ==============================================
// gemini.php - IA via Groq (gratuito, sem cartão)
// Modelo: LLaMA 3 — rápido e sem cota apertada
// ==============================================

class GeminiIA {
    public string $ultimoErro = '';

    private string $apiKey = ''; //chave da api do grok
    private string $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';
    private string $modelo = 'llama-3.1-8b-instant'; 

    private string $systemPrompt = "Você é o Almirante ZEUS, um comandante naval sarcástico, dramático e engraçado em um jogo de Batalha Naval em PHP. Você comanda a frota inimiga contra o jogador humano. Responda SEMPRE em português brasileiro informal. Seja dramático, use metáforas navais, seja levemente intimidador mas divertido. Responda em no máximo 3 frases curtas e impactantes. Sem markdown, só texto puro. Contexto do jogo: tabuleiro 10x10, barcos de 1 a 3 células, jogador tenta afundar sua frota enquanto você atira no dele.";

    public function perguntar(string $prompt, string $contexto = ''): string {
        if ($this->apiKey === 'SUA_CHAVE_GROQ_AQUI' || empty($this->apiKey)) {
            $this->ultimoErro = 'Chave Groq não configurada';
            return $this->fallback();
        }

        $mensagens = [
            ['role' => 'system', 'content' => $this->systemPrompt . ' Situação atual: ' . $contexto],
            ['role' => 'user',   'content' => $prompt],
        ];

        $payload = json_encode([
            'model'       => $this->modelo,
            'messages'    => $mensagens,
            'max_tokens'  => 150,
            'temperature' => 0.9,
        ]);

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false || !empty($curlError)) {
            $this->ultimoErro = "cURL: {$curlError}";
            return $this->fallback();
        }

        if ($httpCode !== 200) {
            $decoded = json_decode($response, true);
            $this->ultimoErro = "HTTP {$httpCode}: " . ($decoded['error']['message'] ?? substr($response, 0, 200));
            return $this->fallback();
        }

        $data  = json_decode($response, true);
        $texto = $data['choices'][0]['message']['content'] ?? null;

        if (!$texto) {
            $this->ultimoErro = 'Resposta vazia da Groq';
            return $this->fallback();
        }

        return trim($texto);
    }

    public function comentarioTiro(string $resultado, int $linha, int $col, string $contexto = ''): string {
        return ''; // tiros usam frases locais em jogo.php para economizar chamadas
    }

    public function getUltimoErro(): string { return $this->ultimoErro; }

    private function fallback(): string {
        $r = [
            "Os mares estão agitados recruta, mas minha estratégia permanece impecável!",
            "O Almirante ZEUS está calculando sua próxima derrota... aguarde!",
            "Minha comunicação foi cortada mas minha artilharia não!",
        ];
        return $r[array_rand($r)];
    }
}