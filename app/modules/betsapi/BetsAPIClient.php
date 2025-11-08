<?php

/**
 * BetsAPI Client
 * Classe para integração com a BetsAPI
 * 
 * @author LeagueBet
 * @version 1.0
 */

class BetsAPIClient {
    
    private $apiToken;
    private $baseUrl = 'https://api.b365api.com/v1/';
    private $timeout = 30;
    
    /**
     * Construtor
     */
    public function __construct() {
        // Token deve ser configurado no banco de dados (tabela: betsapi_config)
        // ou via variável de ambiente
        $this->apiToken = $this->getTokenFromDatabase();
    }
    
    /**
     * Busca o token do banco de dados
     */
    private function getTokenFromDatabase() {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT valor FROM betsapi_config WHERE chave = 'api_token' LIMIT 1");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['valor'] : '';
        } catch (Exception $e) {
            error_log("Erro ao buscar token da BetsAPI: " . $e->getMessage());
            return '';
        }
    }
    
    /**
     * Faz requisição para a API
     * 
     * @param string $endpoint
     * @param array $params
     * @return array|false
     */
    private function request($endpoint, $params = []) {
        $params['token'] = $this->apiToken;
        
        $url = $this->baseUrl . $endpoint . '?' . http_build_query($params);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            $errorMsg = "BetsAPI Error: HTTP {$httpCode}";
            if ($curlError) {
                $errorMsg .= " - cURL Error: {$curlError}";
            }
            if ($response) {
                $errorMsg .= " - Response: " . substr($response, 0, 200);
            }
            error_log($errorMsg);
            return false;
        }
        
        $data = json_decode($response, true);
        
        if (!$data) {
            error_log("BetsAPI Error: Invalid JSON response - " . substr($response, 0, 200));
            return false;
        }
        
        if (!isset($data['success']) || $data['success'] != 1) {
            $errorMsg = "BetsAPI Error: " . ($data['error'] ?? 'Unknown error');
            if (isset($data['error_detail'])) {
                $errorMsg .= " - Detail: " . $data['error_detail'];
            }
            error_log($errorMsg);
            return false;
        }
        
        return $data;
    }
    
    /**
     * Lista esportes disponíveis
     * 
     * @return array|false
     */
    public function getSports() {
        return $this->request('event/sports');
    }
    
    /**
     * Lista ligas/campeonatos de um esporte
     * 
     * @param string $sportId (1 = Soccer, 18 = Basketball, etc)
     * @return array|false
     */
    public function getLeagues($sportId = '1') {
        return $this->request('league', ['sport_id' => $sportId]);
    }
    
    /**
     * Busca jogos próximos (upcoming)
     * 
     * @param string $sportId
     * @param int $days Dias futuros (1-7)
     * @param string $leagueId (opcional)
     * @return array|false
     */
    public function getUpcomingEvents($sportId = '1', $days = 3, $leagueId = null) {
        $params = [
            'sport_id' => $sportId,
            'day' => date('Ymd'),
            'skip_esports' => '1'
        ];
        
        if ($leagueId) {
            $params['league_id'] = $leagueId;
        }
        
        return $this->request('events/upcoming', $params);
    }
    
    /**
     * Busca jogos em andamento (ao vivo)
     * 
     * @param string $sportId
     * @return array|false
     */
    public function getInPlayEvents($sportId = '1') {
        return $this->request('events/inplay', ['sport_id' => $sportId]);
    }
    
    /**
     * Busca detalhes de um evento específico
     * 
     * @param string $eventId
     * @return array|false
     */
    public function getEvent($eventId) {
        return $this->request('event/view', ['event_id' => $eventId]);
    }
    
    /**
     * Busca odds de um evento
     * 
     * @param string $eventId
     * @return array|false
     */
    public function getEventOdds($eventId) {
        return $this->request('event/odds', ['event_id' => $eventId]);
    }
    
    /**
     * Busca odds resumidas (principais mercados)
     * 
     * @param string $eventId
     * @return array|false
     */
    public function getEventOddsSummary($eventId) {
        return $this->request('event/odds/summary', ['event_id' => $eventId]);
    }
    
    /**
     * Busca estatísticas ao vivo de um evento
     * 
     * @param string $eventId
     * @return array|false
     */
    public function getEventStats($eventId) {
        return $this->request('event/stats', ['event_id' => $eventId]);
    }
    
    /**
     * Busca resultados finais
     * 
     * @param string $sportId
     * @param string $date (YYYYMMDD)
     * @return array|false
     */
    public function getResults($sportId = '1', $date = null) {
        if (!$date) {
            $date = date('Ymd', strtotime('-1 day'));
        }
        
        return $this->request('events/ended', [
            'sport_id' => $sportId,
            'day' => $date
        ]);
    }
    
    /**
     * Converte evento da BetsAPI para formato LeagueBet
     * 
     * @param array $event
     * @return array
     */
    public function convertEventToLeagueBet($event) {
        // Extrai data e hora
        $timestamp = $event['time'] ?? time();
        $dateTime = new DateTime();
        $dateTime->setTimestamp($timestamp);
        
        // Busca odds principais (1X2)
        $odds = $this->getMainOdds($event['id']);
        
        return [
            'api_id' => $event['id'],
            'casa' => $event['home']['name'] ?? '',
            'fora' => $event['away']['name'] ?? '',
            'data' => $dateTime->format('Y-m-d'),
            'hora' => $dateTime->format('H:i:s'),
            'dateTime' => $dateTime->format('Y-m-d H:i:s'),
            'pais_id' => $event['league']['cc'] ?? 'BR',
            'pais_nome' => $this->getCountryName($event['league']['cc'] ?? 'BR'),
            'campeonato_id' => $event['league']['id'] ?? 0,
            'campeonato_nome' => $event['league']['name'] ?? '',
            'ao_vivo' => isset($event['time_status']) && $event['time_status'] > 0,
            'placar_casa' => $event['ss'] ?? null,
            'placar_fora' => $event['ss'] ?? null,
            'cotacoes' => $odds
        ];
    }
    
    /**
     * Extrai odds principais de um evento
     * 
     * @param string $eventId
     * @return array
     */
    public function getMainOdds($eventId) {
        $odds = [
            '90' => [
                'casa' => 1.50,
                'empate' => 3.50,
                'fora' => 5.00,
                'casa_empate' => 1.25,
                'casa_fora' => 1.30,
                'empate_fora' => 2.00,
                'ambas_marcam_sim' => 1.80,
                'ambas_marcam_nao' => 2.00,
                'mais_1_5' => 1.30,
                'menos_1_5' => 3.50,
                'mais_2_5' => 1.85,
                'menos_2_5' => 1.95,
                'mais_3_5' => 2.50,
                'menos_3_5' => 1.50
            ],
            'pt' => [
                'casa' => 2.30,
                'empate' => 2.00,
                'fora' => 3.50
            ],
            'st' => [
                'casa' => 2.60,
                'empate' => 3.40,
                'fora' => 2.70
            ]
        ];
        
        // Tenta buscar odds reais da API
        $oddsData = $this->getEventOddsSummary($eventId);
        
        if (!$oddsData || !isset($oddsData['results']) || empty($oddsData['results'])) {
            // Retorna odds padrão se não conseguir buscar
            return $odds;
        }
        
        // Processa odds (1X2, Over/Under, etc)
        foreach ($oddsData['results'] as $market) {
            if (!isset($market['odds']) || empty($market['odds'])) {
                continue;
            }
            
            $marketName = $market['name'] ?? '';
            $marketId = $market['id'] ?? '';
            
            // 1X2 - Resultado Final
            if ($marketId == '1') {
                foreach ($market['odds'] as $odd) {
                    $oddValue = floatval($odd['odds'] ?? 0);
                    if ($oddValue > 1) {
                        if ($odd['name'] == '1') $odds['90']['casa'] = $oddValue;
                        if ($odd['name'] == 'X') $odds['90']['empate'] = $oddValue;
                        if ($odd['name'] == '2') $odds['90']['fora'] = $oddValue;
                    }
                }
            }
            
            // Over/Under 2.5
            if ($marketId == '18') {
                foreach ($market['odds'] as $odd) {
                    $oddValue = floatval($odd['odds'] ?? 0);
                    if ($oddValue > 1) {
                        if (strpos($odd['name'], 'Over') !== false) {
                            $odds['90']['mais_2_5'] = $oddValue;
                        }
                        if (strpos($odd['name'], 'Under') !== false) {
                            $odds['90']['menos_2_5'] = $oddValue;
                        }
                    }
                }
            }
            
            // Ambas Marcam
            if ($marketId == '29') {
                foreach ($market['odds'] as $odd) {
                    $oddValue = floatval($odd['odds'] ?? 0);
                    if ($oddValue > 1) {
                        if (strpos($odd['name'], 'Yes') !== false) {
                            $odds['90']['ambas_marcam_sim'] = $oddValue;
                        }
                        if (strpos($odd['name'], 'No') !== false) {
                            $odds['90']['ambas_marcam_nao'] = $oddValue;
                        }
                    }
                }
            }
        }
        
        return $odds;
    }
    
    /**
     * Retorna nome do país pelo código
     * 
     * @param string $code
     * @return string
     */
    private function getCountryName($code) {
        $countries = [
            'BR' => 'Brasil',
            'GB-ENG' => 'Inglaterra',
            'ES' => 'Espanha',
            'IT' => 'Itália',
            'DE' => 'Alemanha',
            'FR' => 'França',
            'AR' => 'Argentina',
            'CL' => 'Chile',
            'CO' => 'Colômbia',
            'PE' => 'Peru',
            'UY' => 'Uruguai',
            'MX' => 'México',
            'US' => 'Estados Unidos',
            'PT' => 'Portugal',
            'NL' => 'Holanda',
            'BE' => 'Bélgica',
            'TR' => 'Turquia',
            'RU' => 'Rússia',
            'INT' => 'Internacional'
        ];
        
        return $countries[$code] ?? $code;
    }
    
    /**
     * Testa conexão com a API
     * 
     * @return bool
     */
    public function testConnection() {
        // Tenta buscar jogos futuros (endpoint mais confiável)
        $events = $this->getUpcomingEvents('1', 1);
        if ($events !== false) {
            return true;
        }
        
        // Fallback: tenta buscar esportes
        $sports = $this->getSports();
        return $sports !== false;
    }
}

