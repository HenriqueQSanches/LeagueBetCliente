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
    private $baseUrlV4 = 'https://api.b365api.com/v4/';
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
     * Faz requisição V4 (bet365/*)
     */
    private function requestV4($endpoint, $params = []) {
        $params['token'] = $this->apiToken;
        $url = $this->baseUrlV4 . ltrim($endpoint, '/') . '?' . http_build_query($params);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            error_log("BetsAPI V4 Error {$httpCode} {$curlError} {$response}");
            return false;
        }
        $data = json_decode($response, true);
        if (!$data || (isset($data['success']) && (int)$data['success'] !== 1)) {
            error_log("BetsAPI V4 decode error: " . substr($response ?? '', 0, 200));
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
     * Mapeia mercados úteis a partir do summary v1, sem valores padrão.
     * - 1X2 (marketId == '1')
     * - Ambas marcam (marketId == '29' ou nome contém 'Both Teams to Score')
     * - Dupla chance (pelas seleções '1X','X2','12')
     * - Over/Under por nome (se presente no summary)
     */
    public function getSummaryMappedOdds($eventId) {
        $out = [];
        $summary = $this->getEventOddsSummary($eventId);
        if (!$summary || empty($summary['results'])) {
            return $out;
        }
        foreach ($summary['results'] as $market) {
            if (empty($market['odds']) || !is_array($market['odds'])) {
                continue;
            }
            $marketName = (string)($market['name'] ?? '');
            $marketId = (string)($market['id'] ?? '');
            $tempo = '90';
            $marketLabel = strtolower($marketName);
            if (strpos($marketLabel, '1st') !== false || strpos($marketLabel, '1st half') !== false) {
                $tempo = 'pt';
            } elseif (strpos($marketLabel, '2nd') !== false || strpos($marketLabel, '2nd half') !== false) {
                $tempo = 'st';
            }
            foreach ($market['odds'] as $odd) {
                $name = (string)($odd['name'] ?? '');
                $oddValue = isset($odd['odds']) ? floatval($odd['odds']) : 0.0;
                if ($oddValue <= 1) continue;
                
                // 1X2
                if ($marketId === '1') {
                    if ($name === '1') $out[$tempo]['casa'] = $oddValue;
                    if ($name === 'X') $out[$tempo]['empate'] = $oddValue;
                    if ($name === '2') $out[$tempo]['fora'] = $oddValue;
                }
                // Ambas marcam
                if ($marketId === '29' || stripos($marketLabel, 'both teams to score') !== false) {
                    if (stripos($name, 'Yes') !== false || stripos($name, 'yes') !== false) {
                        $out[$tempo]['amb'] = $oddValue;
                        $out[$tempo]['ambas_marcam_sim'] = $oddValue;
                    }
                    if (stripos($name, 'No') !== false || stripos($name, 'no') !== false) {
                        $out[$tempo]['ambn'] = $oddValue;
                        $out[$tempo]['ambas_marcam_nao'] = $oddValue;
                    }
                }
                // Dupla chance
                if (in_array($name, ['1X','X2','12'], true)) {
                    if ($name === '1X') { $out[$tempo]['dupla_1x'] = $oddValue; $out[$tempo]['dplcasa'] = $oddValue; $out[$tempo]['casa_empate'] = $oddValue; }
                    if ($name === 'X2') { $out[$tempo]['dupla_x2'] = $oddValue; $out[$tempo]['dplfora'] = $oddValue; $out[$tempo]['empate_fora'] = $oddValue; }
                    if ($name === '12') { $out[$tempo]['dupla_12'] = $oddValue; $out[$tempo]['cof'] = $oddValue; $out[$tempo]['casa_fora'] = $oddValue; }
                }
                // Over/Under por nome (se vier no summary)
                if (stripos($name, 'Over') === 0 || stripos($name, 'Under') === 0) {
                    if (preg_match('/(Over|Under)\s*([0-9]+(?:\.[0-9])?)/i', $name, $m)) {
                        $tipo = strtolower($m[1]) === 'over' ? 'mais' : 'menos';
                        $numero = str_replace('.', '_', $m[2]);
                        $campo = "{$tipo}_{$numero}";
                        $out[$tempo][$campo] = $oddValue;
                    }
                }
            }
        }
        return $out;
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

        // A API pode retornar em 2 formatos:
        // A) Lista de mercados [{ id, name, odds:[...] }, ...]
        // B) Objeto indexado por market_ids (ex.: "1_1", "1_3") => [ { ... }, ... ]
        $resultsNode = $response['results'];

        // Formato B) – quando a chave é um market code (ex.: "1_1")
        if (is_array($resultsNode) && isset($resultsNode['1_1']) || array_keys($resultsNode) !== range(0, count($resultsNode) - 1)) {
            // 1_1: Full Time Result (1X2)
            if (!empty($resultsNode['1_1']) && is_array($resultsNode['1_1'])) {
                // Usa o último registro (odds mais recentes)
                $last = end($resultsNode['1_1']);
                $home = isset($last['home_od']) ? floatval($last['home_od']) : 0;
                $draw = isset($last['draw_od']) ? floatval($last['draw_od']) : 0;
                $away = isset($last['away_od']) ? floatval($last['away_od']) : 0;
                if ($home > 1) $result['90']['casa'] = $home;
                if ($draw > 1) $result['90']['empate'] = $draw;
                if ($away > 1) $result['90']['fora'] = $away;
            }

            // 1_3: Total Goals Over/Under
            if (!empty($resultsNode['1_3']) && is_array($resultsNode['1_3'])) {
                foreach ($resultsNode['1_3'] as $row) {
                    $line = (string)($row['handicap'] ?? '');
                    // Normaliza linhas "3.0,3.5" => usa a maior (3.5)
                    if (strpos($line, ',') !== false) {
                        $parts = array_map('trim', explode(',', $line));
                        $line = end($parts);
                    }
                    if ($line !== '' && preg_match('/^\d+(?:\.\d+)?$/', $line)) {
                        $key = str_replace('.', '_', $line);
                        $over = isset($row['over_od']) ? floatval($row['over_od']) : 0;
                        $under = isset($row['under_od']) ? floatval($row['under_od']) : 0;
                        if ($over > 1)  $result['90']["mais_{$key}"]  = $over;
                        if ($under > 1) $result['90']["menos_{$key}"] = $under;
                    }
                }
            }

            // 1_8: Half Time Result (assumido) -> mapeia para 'pt'
            if (!empty($resultsNode['1_8']) && is_array($resultsNode['1_8'])) {
                $last = end($resultsNode['1_8']);
                $home = isset($last['home_od']) ? floatval($last['home_od']) : 0;
                $draw = isset($last['draw_od']) ? floatval($last['draw_od']) : 0;
                $away = isset($last['away_od']) ? floatval($last['away_od']) : 0;
                if ($home > 1) $result['pt']['casa'] = $home;
                if ($draw > 1) $result['pt']['empate'] = $draw;
                if ($away > 1) $result['pt']['fora'] = $away;
            }

            // 1_6: Over/Under 1st Half (assumido) -> mapeia para 'pt'
            if (!empty($resultsNode['1_6']) && is_array($resultsNode['1_6'])) {
                foreach ($resultsNode['1_6'] as $row) {
                    $line = (string)($row['handicap'] ?? '');
                    if ($line !== '' && preg_match('/^\d+(?:\.\d+)?$/', $line)) {
                        $key = str_replace('.', '_', $line);
                        $over = isset($row['over_od']) ? floatval($row['over_od']) : 0;
                        $under = isset($row['under_od']) ? floatval($row['under_od']) : 0;
                        if ($over > 1)  $result['pt']["mais_{$key}"]  = $over;
                        if ($under > 1) $result['pt']["menos_{$key}"] = $under;
                    }
                }
            }

            // 1_2: Asian Handicap FT (assumido) – ainda não mapeamos para campos AH; por ora, ignoramos

            return $result;
        }

        // Formato A) – lista de mercados com 'name'/'odds'
        foreach ($resultsNode as $market) {
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
                
                // Draw No Bet (Empate Anula Aposta)
                if (stripos($marketName, 'draw no bet') !== false
                    || stripos($marketName, 'dnb') !== false
                    || stripos($marketName, 'empate anula') !== false
                    || stripos($marketName, 'no draw') !== false) {
                    // Nomes mais comuns para as seleções: "1" / "2", "home" / "away"
                    $nameLower = strtolower($name);
                    if ($name === '1' || $nameLower === 'home' || $nameLower === 'team1' || $nameLower === 'casa') {
                        $result[$tempo]['empateanulacasa'] = $oddValue;
                    } elseif ($name === '2' || $nameLower === 'away' || $nameLower === 'team2' || $nameLower === 'fora') {
                        $result[$tempo]['empateanulafora'] = $oddValue;
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
     * Busca e mapeia odds pré-jogo via Bet365 Prematch (V4).
     * Tenta descobrir o FI (Bet365 Event ID) a partir de event/view.
     */
    public function getBet365PrematchMappedOdds($eventId, $fiOverride = null) {
        // Descobre o FI via event/view (a menos que seja fornecido)
        $fi = $fiOverride;
        $homeNameLower = null;
        $awayNameLower = null;
        if (!$fi) {
            $view = $this->getEvent($eventId);
            if ($view && isset($view['results'][0])) {
                $r = $view['results'][0];
                // Tentativas comuns de onde o FI aparece
                $fi = $r['bet365_id'] ?? ($r['FI'] ?? null);
                if (!$fi && isset($r['bet365']) && is_array($r['bet365'])) {
                    $fi = $r['bet365']['id'] ?? null;
                }
                // captura nomes dos times para mapear mercados com nomes (ex.: HT/FT)
                if (isset($r['home']['name'])) $homeNameLower = strtolower($r['home']['name']);
                if (isset($r['away']['name'])) $awayNameLower = strtolower($r['away']['name']);
            }
        }
        if (!$fi) {
            // Sem FI não dá para consumir v4 prematch
            return [];
        }
        $data = $this->requestV4('bet365/prematch', ['FI' => $fi]);
        if (!$data || empty($data['results'])) {
            return [];
        }

        // A resposta v4 pode trazer estrutura com 'main' e 'others'.
        // Vamos normalizar para uma lista de mercados com 'name' e 'odds'/‘values’ similar ao parser anterior.
        $markets = [];
        // 1) Caso comum: results[0] é um objeto com seções (main, goals, half, asian_lines, minutes, specials, others)
        if (count($data['results']) === 1 && is_array($data['results'][0])) {
            $root = $data['results'][0];

            // Função para coletar mercados a partir de um nó com 'sp' (onde ficam os mercados)
            $collectFromSp = function($node) use (&$markets) {
                if (!is_array($node)) return;
                // Alguns nós têm 'sp' => { market_key: { id, name, odds: [] }, ... }
                if (isset($node['sp']) && is_array($node['sp'])) {
                    foreach ($node['sp'] as $m) {
                        if (is_array($m) && (isset($m['odds']) || isset($m['values']))) {
                            $markets[] = $m;
                        }
                    }
                } else {
                    // Em casos raros pode vir como lista direta
                    if (isset($node['name']) || isset($node['odds']) || isset($node['values'])) {
                        $markets[] = $node;
                    }
                }
            };

            // Seções principais
            foreach (['main','goals','half','asian_lines','minutes','specials','schedule'] as $sec) {
                if (isset($root[$sec])) {
                    $collectFromSp($root[$sec]);
                }
            }
            // 'others' é uma lista de blocos, cada um com 'sp'
            if (isset($root['others']) && is_array($root['others'])) {
                foreach ($root['others'] as $other) {
                    $collectFromSp($other);
                }
            }
        } else {
            // 2) Alternativo: results é uma lista de mercados já "achatados"
            foreach ($data['results'] as $block) {
                if (isset($block['name']) || isset($block['odds']) || isset($block['values'])) {
                    $markets[] = $block;
                    continue;
                }
                if (isset($block['main']) && is_array($block['main'])) {
                    foreach ($block['main'] as $m) $markets[] = $m;
                }
                if (isset($block['others']) && is_array($block['others'])) {
                    foreach ($block['others'] as $m) $markets[] = $m;
                }
            }
        }

        // Reutiliza lógica do getExtendedOdds: mesmo mapeamento por nome.
        $result = [];
        foreach ($markets as $market) {
            $oddsList = [];
            if (!empty($market['odds']) && is_array($market['odds'])) {
                $oddsList = $market['odds'];
            } elseif (!empty($market['values']) && is_array($market['values'])) {
                $oddsList = $market['values'];
            }
            if (empty($oddsList)) continue;
            $marketName = strtolower($market['name'] ?? '');
            $marketName = str_replace(['  ', '–', '—'], [' ', '-', '-'], $marketName);
            $tempo = '90'; // prematch padrão tempo de jogo inteiro
            if (strpos($marketName, '1st') !== false || strpos($marketName, '1st half') !== false) {
                $tempo = 'pt';
            } elseif (strpos($marketName, '2nd') !== false || strpos($marketName, '2nd half') !== false) {
                $tempo = 'st';
            }

            foreach ($oddsList as $odd) {
                $name = (string)($odd['name'] ?? ($odd['label'] ?? ''));
                $raw = $odd['odds'] ?? ($odd['price'] ?? ($odd['value'] ?? ($odd['decimal'] ?? null)));
                $oddValue = is_numeric($raw) ? floatval($raw) : 0.0;
                if ($oddValue <= 1) continue;

                // Double Chance
                if (stripos($marketName, 'double chance') !== false || in_array($name, ['1X','X2','12'], true)) {
                    if ($name === '1X') { $result[$tempo]['dplcasa'] = $oddValue; $result[$tempo]['dupla_1x'] = $oddValue; }
                    if ($name === 'X2') { $result[$tempo]['dplfora'] = $oddValue; $result[$tempo]['dupla_x2'] = $oddValue; }
                    if ($name === '12') { $result[$tempo]['cof'] = $oddValue; $result[$tempo]['dupla_12'] = $oddValue; }
                }

                // Draw No Bet (Empate Anula Aposta) - presente em 'main.sp.draw_no_bet' etc.
                if (stripos($marketName, 'draw no bet') !== false
                    || stripos($marketName, 'dnb') !== false
                    || stripos($marketName, 'empate anula') !== false
                    || stripos($marketName, 'no draw') !== false) {
                    $nameLower = strtolower($name);
                    if ($name === '1' || $nameLower === 'home' || $nameLower === 'team1' || $nameLower === 'casa') {
                        $result[$tempo]['empateanulacasa'] = $oddValue;
                    } elseif ($name === '2' || $nameLower === 'away' || $nameLower === 'team2' || $nameLower === 'fora') {
                        $result[$tempo]['empateanulafora'] = $oddValue;
                    }
                }

                // BTTS
                if (stripos($marketName, 'both teams to score') !== false || stripos($marketName, 'ambas') !== false) {
                    if (stripos($name, 'yes') !== false)  $result[$tempo]['amb']  = $oddValue;
                    if (stripos($name, 'no') !== false)   $result[$tempo]['ambn'] = $oddValue;
                }

                // Over/Under (inclui Corners/Cards com nomes distintos)
                $handicap = null;
                if (!empty($odd['handicap']) && is_string($odd['handicap'])) $handicap = $odd['handicap'];
                if (!empty($odd['line'])) $handicap = (string)$odd['line'];
                $headerLower = strtolower($odd['header'] ?? '');

                $isCorners = (stripos($marketName, 'corner') !== false || stripos($marketName, 'escanteio') !== false);
                $isCards   = (stripos($marketName, 'card') !== false   || stripos($marketName, 'booking') !== false || stripos($marketName, 'cart') !== false);

                $tipo = null; $numero = null;
                if (preg_match('/(over|under)\s*([0-9]+(?:\.[0-9])?)/i', $name, $m)) {
                    $tipo = strtolower($m[1]) === 'over' ? 'mais' : 'menos';
                    $numero = str_replace('.', '_', $m[2]);
                } elseif ($handicap && preg_match('/^([0-9]+(?:\.[0-9])?)$/', $handicap)) {
                    if (stripos($name, 'over') !== false)  $tipo = 'mais';
                    if (stripos($name, 'under') !== false) $tipo = 'menos';
                    if (!$tipo && $headerLower) {
                        if (strpos($headerLower, 'over') !== false) $tipo = 'mais';
                        if (strpos($headerLower, 'under') !== false) $tipo = 'menos';
                    }
                    $numero = str_replace('.', '_', $handicap);
                }

                if ($tipo && $numero) {
                    if ($isCorners) {
                        $campo = "esc_{$numero}_{$tipo}";
                    } elseif ($isCards) {
                        $campo = "cart_{$numero}_{$tipo}";
                    } else {
                        $campo = "{$tipo}_{$numero}";
                    }
                    $result[$tempo][$campo] = $oddValue;
                }

                // Goals Odd/Even (paridade)
                if (strpos($marketName, 'goals odd/even') !== false || strpos($marketName, 'goals odd even') !== false) {
                    $n = strtolower($name);
                    if (strpos($n, 'odd') !== false || strpos($n, 'impar') !== false || strpos($n, 'ímpar') !== false) {
                        $key = ($tempo === '90') ? 'impar' : "impar_{$tempo}";
                        $result[$tempo][$key] = $oddValue;
                    }
                    if (strpos($n, 'even') !== false || strpos($n, 'par') !== false) {
                        $key = ($tempo === '90') ? 'par' : "par_{$tempo}";
                        $result[$tempo][$key] = $oddValue;
                    }
                }

                // First Team to Score
                if (strpos($marketName, 'first team to score') !== false) {
                    $n = strtolower($name);
                    if ($name === '1' || strpos($n, 'home') !== false || strpos($n, 'team 1') !== false) {
                        $result[$tempo]['fts_casa'] = $oddValue;
                    } elseif ($name === '2' || strpos($n, 'away') !== false || strpos($n, 'team 2') !== false) {
                        $result[$tempo]['fts_fora'] = $oddValue;
                    } elseif (strpos($n, 'no goals') !== false || strpos($n, 'sem gols') !== false) {
                        $result[$tempo]['fts_ng'] = $oddValue;
                    }
                }

                // Half Time / Full Time (HT/FT)
                if (strpos($marketName, 'half time/full time') !== false || strpos($marketName, 'half time - full time') !== false) {
                    $n = strtolower($name);
                    // tenta mapear 'home/draw/away' com base nos nomes e 'draw'
                    $parts = array_map('trim', explode('-', $n));
                    if (count($parts) === 2) {
                        $mapSide = function($side) use ($homeNameLower, $awayNameLower) {
                            if (strpos($side, 'draw') !== false) return 'x';
                            if ($homeNameLower && strpos($side, $homeNameLower) !== false) return '1';
                            if ($awayNameLower && strpos($side, $awayNameLower) !== false) return '2';
                            // fallback por palavras-chave
                            if (strpos($side, 'home') !== false || strpos($side, 'team 1') !== false) return '1';
                            if (strpos($side, 'away') !== false || strpos($side, 'team 2') !== false) return '2';
                            return null;
                        };
                        $left = $mapSide($parts[0]);
                        $right = $mapSide($parts[1]);
                        if ($left && $right) {
                            $key = "htft_{$left}_{$right}";
                            $result['90'][$key] = $oddValue;
                        }
                    }
                }

                // Asian Handicap (90, 1st half, 2nd half)
                if (strpos($marketName, 'asian handicap') !== false) {
                    // linha pode vir em 'handicap' ou 'line' (ex.: "-2.0, -2.5")
                    $lineRaw = '';
                    if (!empty($odd['handicap']) && is_string($odd['handicap'])) $lineRaw = $odd['handicap'];
                    if (!$lineRaw && !empty($odd['line'])) $lineRaw = (string)$odd['line'];
                    if ($lineRaw !== '') {
                        $line = str_replace(['.', ', ', ',', ' '], ['_', '_', '_', ''], $lineRaw);
                        $sel = strtolower((string)($odd['header'] ?? $name ?? ''));
                        $isHome = ($sel === '1' || strpos($sel, 'home') !== false || strpos($sel, 'team 1') !== false);
                        $isAway = ($sel === '2' || strpos($sel, 'away') !== false || strpos($sel, 'team 2') !== false);
                        $prefix = 'ah';
                        if ($tempo === 'pt') $prefix = 'ah_pt';
                        if ($tempo === 'st') $prefix = 'ah_st';
                        if ($isHome) {
                            $key = "{$prefix}_home_{$line}";
                            $result[$tempo][$key] = $oddValue;
                        } elseif ($isAway) {
                            $key = "{$prefix}_away_{$line}";
                            $result[$tempo][$key] = $oddValue;
                        }
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

