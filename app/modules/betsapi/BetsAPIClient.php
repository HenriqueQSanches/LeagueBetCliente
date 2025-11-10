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
        $this->apiToken = '237782-BXpZQecPXZnfW9';
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
     * Busca odds completas do evento e extrai mercados adicionais (fallback)
     * - Double Chance (1X, 12, X2)
     * - Both Teams To Score (Yes/No)
     * - Over/Under genérico (1.5, 2.5, 3.5...)
     * 
     * @param string $eventId
     * @return array
     */
    public function getExtendedOdds($eventId) {
        $response = $this->request('event/odds', ['event_id' => $eventId]);
        $result = [];
        
        if (!$response || !isset($response['results']) || empty($response['results'])) {
            return $result;
        }
        
        foreach ($response['results'] as $market) {
            // Normalizar lista de odds (algumas respostas vêm como 'odds', outras 'values'
            // e em certos casos ficam dentro de 'bookmakers' => [ { odds: [...] } ])
            $oddsList = [];
            if (!empty($market['odds']) && is_array($market['odds'])) {
                $oddsList = $market['odds'];
            } elseif (!empty($market['values']) && is_array($market['values'])) {
                $oddsList = $market['values'];
            } elseif (!empty($market['bookmakers']) && is_array($market['bookmakers'])) {
                // Pega o primeiro bookmaker com odds disponíveis
                foreach ($market['bookmakers'] as $bm) {
                    if (!empty($bm['odds']) && is_array($bm['odds'])) {
                        $oddsList = $bm['odds'];
                        break;
                    }
                    if (!empty($bm['values']) && is_array($bm['values'])) {
                        $oddsList = $bm['values'];
                        break;
                    }
                }
            }
            if (empty($oddsList)) continue;
            
            $marketName = strtolower($market['name'] ?? '');
            // Normalizações para facilitar matching
            $marketName = str_replace(['  ', '–', '—'], [' ', '-', '-'], $marketName);
            
            // Determinar tempo pela descrição do mercado
            $tempo = '90';
            if (strpos($marketName, '1st') !== false || strpos($marketName, '1st half') !== false) {
                $tempo = 'pt';
            } else if (strpos($marketName, '2nd') !== false || strpos($marketName, '2nd half') !== false) {
                $tempo = 'st';
            }
            
            foreach ($oddsList as $odd) {
                $name = (string)($odd['name'] ?? ($odd['label'] ?? ''));
                // Alguns retornos usam 'odds', outros 'price', 'value' etc.
                $raw = $odd['odds'] ?? ($odd['price'] ?? ($odd['value'] ?? ($odd['decimal'] ?? null)));
                $oddValue = is_numeric($raw) ? floatval($raw) : 0.0;
                if ($oddValue <= 1) {
                    continue;
                }
                
                // Double chance -> mapear para campos usados no admin e aliases usados no site
                if (stripos($marketName, 'double chance') !== false
                    || stripos($marketName, 'double-chance') !== false
                    || stripos($marketName, 'dupla') !== false
                    || in_array($name, ['1X','X2','12'], true)) {
                    if ($name === '1X') {
                        // Aliases: dplcasa (legado) e dupla_1x (novo)
                        $result[$tempo]['dplcasa']  = $oddValue;
                        $result[$tempo]['dupla_1x'] = $oddValue;
                    } elseif ($name === 'X2') {
                        $result[$tempo]['dplfora']  = $oddValue;
                        $result[$tempo]['dupla_x2'] = $oddValue;
                    } elseif ($name === '12') {
                        $result[$tempo]['cof']      = $oddValue;   // casa ou fora (legado)
                        $result[$tempo]['dupla_12'] = $oddValue;
                    }
                }
                
                // Both Teams To Score -> mapear para 'amb' e 'ambn'
                if (stripos($marketName, 'both teams to score') !== false
                    || stripos($marketName, 'both team to score') !== false
                    || stripos($marketName, 'btts') !== false
                    || stripos($marketName, 'gg/ng') !== false
                    || stripos($marketName, 'ambas') !== false) {
                    if (stripos($name, 'yes') !== false)  $result[$tempo]['amb']  = $oddValue;
                    if (stripos($name, 'no') !== false)   $result[$tempo]['ambn'] = $oddValue;
                }
                
                // Over/Under - nomes diversos (Over 2.5 / Under 1.5...) ou Total Goals
                if (stripos($marketName, 'over/under') !== false || stripos($marketName, 'total') !== false || stripos($marketName, 'goals') !== false) {
                    // Alguns odds trazem handicap separado (e.g., handicap: "2.5")
                    $handicap = null;
                    if (!empty($odd['handicap']) && is_string($odd['handicap'])) {
                        $handicap = $odd['handicap'];
                    } elseif (!empty($odd['line'])) {
                        $handicap = (string)$odd['line'];
                    }
                    
                    $tipo = null;
                    $numero = null;
                    
                    if (preg_match('/(Over|Under)\s*([0-9]+(?:\.[0-9])?)/i', $name, $m)) {
                        $tipo = strtolower($m[1]) === 'over' ? 'mais' : 'menos';
                        $numero = str_replace('.', '_', $m[2]);
                    } elseif ($handicap && preg_match('/^([0-9]+(?:\.[0-9])?)$/', $handicap)) {
                        // Quando o nome é apenas "Over" / "Under" e a linha vem em outro campo
                        if (stripos($name, 'over') !== false)  $tipo = 'mais';
                        if (stripos($name, 'under') !== false) $tipo = 'menos';
                        $numero = str_replace('.', '_', $handicap);
                    }
                    
                    if ($tipo && $numero) {
                        $campo = "{$tipo}_{$numero}";
                        // apenas grava se odds > 1
                        if ($oddValue > 1) {
                            $result[$tempo][$campo] = $oddValue;
                        }
                    }
                }

                // Escanteios (Corners) Over/Under
                if (stripos($marketName, 'corner') !== false || stripos($marketName, 'escanteio') !== false) {
                    $handicap = null;
                    if (!empty($odd['handicap']) && is_string($odd['handicap'])) {
                        $handicap = $odd['handicap'];
                    } elseif (!empty($odd['line'])) {
                        $handicap = (string)$odd['line'];
                    }
                    $tipo = null;
                    $numero = null;
                    if (preg_match('/(Over|Under)\s*([0-9]+(?:\.[0-9])?)/i', $name, $m)) {
                        $tipo = strtolower($m[1]) === 'over' ? 'mais' : 'menos';
                        $numero = str_replace('.', '_', $m[2]);
                    } elseif ($handicap && preg_match('/^([0-9]+(?:\.[0-9])?)$/', $handicap)) {
                        if (stripos($name, 'over') !== false)  $tipo = 'mais';
                        if (stripos($name, 'under') !== false) $tipo = 'menos';
                        $numero = str_replace('.', '_', $handicap);
                    }
                    if ($tipo && $numero && $oddValue > 1) {
                        // padrão: esc_{linha}_{tipo}  ex: esc_9_5_mais
                        $campo = "esc_{$numero}_{$tipo}";
                        $result[$tempo][$campo] = $oddValue;
                    }
                }

                // Cartões (Cards/Bookings) Over/Under
                if (stripos($marketName, 'card') !== false || stripos($marketName, 'booking') !== false || stripos($marketName, 'cart') !== false) {
                    $handicap = null;
                    if (!empty($odd['handicap']) && is_string($odd['handicap'])) {
                        $handicap = $odd['handicap'];
                    } elseif (!empty($odd['line'])) {
                        $handicap = (string)$odd['line'];
                    }
                    $tipo = null;
                    $numero = null;
                    if (preg_match('/(Over|Under)\s*([0-9]+(?:\.[0-9])?)/i', $name, $m)) {
                        $tipo = strtolower($m[1]) === 'over' ? 'mais' : 'menos';
                        $numero = str_replace('.', '_', $m[2]);
                    } elseif ($handicap && preg_match('/^([0-9]+(?:\.[0-9])?)$/', $handicap)) {
                        if (stripos($name, 'over') !== false)  $tipo = 'mais';
                        if (stripos($name, 'under') !== false) $tipo = 'menos';
                        $numero = str_replace('.', '_', $handicap);
                    }
                    if ($tipo && $numero && $oddValue > 1) {
                        // padrão: cart_{linha}_{tipo}  ex: cart_4_5_menos
                        $campo = "cart_{$numero}_{$tipo}";
                        $result[$tempo][$campo] = $oddValue;
                    }
                }
            }
        }
        
        return $result;
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
            
            // Over/Under (genérico: tenta capturar 1.5, 2.5, 3.5, etc) no tempo de jogo correspondente
            // Alguns provedores usam marketId diferentes; por isso fazemos parsing por nome
            foreach ($market['odds'] as $odd) {
                $oddValue = floatval($odd['odds'] ?? 0);
                if ($oddValue <= 1) continue;

                $name = (string)($odd['name'] ?? '');
                $marketLabel = strtolower((string)$marketName);

                // Define o "tempo" alvo: 90 (jogo), pt (1º tempo) ou st (2º tempo)
                $tempo = '90';
                if (strpos($marketLabel, '1st') !== false || strpos($marketLabel, '1st half') !== false || strpos($marketLabel, '1st Half') !== false) {
                    $tempo = 'pt';
                } else if (strpos($marketLabel, '2nd') !== false || strpos($marketLabel, '2nd half') !== false || strpos($marketLabel, '2nd Half') !== false) {
                    $tempo = 'st';
                }

                // Ambas marcam (sim/não)
                if ($marketId == '29' || stripos($marketLabel, 'both teams to score') !== false) {
                    if (stripos($name, 'Yes') !== false)  {
                        $odds[$tempo]['ambas_marcam_sim'] = $oddValue;
                        // Alias utilizado no site
                        $odds[$tempo]['amb'] = $oddValue;
                    }
                    if (stripos($name, 'No') !== false)   {
                        $odds[$tempo]['ambas_marcam_nao'] = $oddValue;
                        $odds[$tempo]['ambn'] = $oddValue;
                    }
                }

                // Dupla chance (1X, 12, X2) - alguns providers usam market id próprio
                if (in_array($name, ['1X','X2','12'], true)) {
                    if ($name === '1X') {
                        $odds[$tempo]['dupla_1x'] = $oddValue;
                        // Aliases legados
                        $odds[$tempo]['casa_empate'] = $oddValue;
                        $odds[$tempo]['dplcasa'] = $oddValue;
                    } elseif ($name === 'X2') {
                        $odds[$tempo]['dupla_x2'] = $oddValue;
                        $odds[$tempo]['empate_fora'] = $oddValue;
                        $odds[$tempo]['dplfora'] = $oddValue;
                    } elseif ($name === '12') {
                        $odds[$tempo]['dupla_12'] = $oddValue;
                        $odds[$tempo]['casa_fora'] = $oddValue;
                        $odds[$tempo]['cof'] = $oddValue;
                    }
                }

                // Over/Under com valor variável (ex.: Over 1.5, Under 3.5)
                if (stripos($name, 'Over') === 0 || stripos($name, 'Under') === 0) {
                    if (preg_match('/(Over|Under)\s*([0-9]+(?:\.[0-9])?)/i', $name, $m)) {
                        $tipo = strtolower($m[1]) === 'over' ? 'mais' : 'menos';
                        $numero = str_replace('.', '_', $m[2]);
                        $campo = "{$tipo}_{$numero}";
                        $odds[$tempo][$campo] = $oddValue;
                    }
                }
            }
        }
        
        // Normalização final de aliases para compatibilidade com a tela e com cadastros antigos
        foreach (['90','pt','st'] as $t) {
            if (!isset($odds[$t])) continue;
            // Ambas marcam
            if (!isset($odds[$t]['amb']) && isset($odds[$t]['ambas_marcam_sim']) && $odds[$t]['ambas_marcam_sim'] > 1) {
                $odds[$t]['amb'] = $odds[$t]['ambas_marcam_sim'];
            }
            if (!isset($odds[$t]['ambn']) && isset($odds[$t]['ambas_marcam_nao']) && $odds[$t]['ambas_marcam_nao'] > 1) {
                $odds[$t]['ambn'] = $odds[$t]['ambas_marcam_nao'];
            }
            // Dupla chance: criar aliases quando possível
            if (!isset($odds[$t]['dupla_1x']) && isset($odds[$t]['casa_empate']) && $odds[$t]['casa_empate'] > 1) {
                $odds[$t]['dupla_1x'] = $odds[$t]['casa_empate'];
            }
            if (!isset($odds[$t]['dupla_x2']) && isset($odds[$t]['empate_fora']) && $odds[$t]['empate_fora'] > 1) {
                $odds[$t]['dupla_x2'] = $odds[$t]['empate_fora'];
            }
            if (!isset($odds[$t]['dupla_12']) && isset($odds[$t]['casa_fora']) && $odds[$t]['casa_fora'] > 1) {
                $odds[$t]['dupla_12'] = $odds[$t]['casa_fora'];
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

