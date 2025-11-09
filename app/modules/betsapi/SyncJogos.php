<?php

/**
 * Script de Sincronização de Jogos
 * Busca jogos da BetsAPI e salva no banco de dados
 * 
 * Executar via CRON a cada 5-10 minutos
 */

// Não precisa incluir inc.config.php aqui, pois já é incluído pelo arquivo que chama esta classe
// require_once __DIR__ . '/../../../inc.config.php';
require_once __DIR__ . '/BetsAPIClient.php';

class SyncJogos {
    
    private $api;
    private $log = [];
    
    public function __construct() {
        $this->api = new BetsAPIClient();
    }
    
    /**
     * Executa sincronização completa
     */
    public function sync() {
        $this->log("🚀 Iniciando sincronização com BetsAPI...");
        
        // Testa conexão
        if (!$this->api->testConnection()) {
            $this->log("❌ ERRO: Não foi possível conectar à BetsAPI!");
            return false;
        }
        
        $this->log("✅ Conexão com BetsAPI estabelecida!");
        
        // Sincroniza jogos futuros (próximos 3 dias)
        $this->syncUpcomingEvents();
        
        // Sincroniza jogos ao vivo
        $this->syncInPlayEvents();
        
        // Remove jogos antigos (já finalizados há mais de 24h)
        $this->cleanOldEvents();
        
        $this->log("✅ Sincronização concluída!");
        
        return true;
    }
    
    /**
     * Sincroniza jogos futuros
     */
    private function syncUpcomingEvents() {
        $this->log("\n📅 Buscando jogos futuros...");
        
        // Busca jogos de futebol (sport_id = 1)
        $events = $this->api->getUpcomingEvents('1', 3);
        
        if (!$events || !isset($events['results'])) {
            $this->log("⚠️ Nenhum jogo futuro encontrado.");
            return;
        }
        
        $total = count($events['results']);
        $inserted = 0;
        $updated = 0;
        
        $this->log("📊 {$total} jogos encontrados. Processando...");
        
        foreach ($events['results'] as $event) {
            $result = $this->saveEvent($event);
            if ($result === 'inserted') $inserted++;
            if ($result === 'updated') $updated++;
        }
        
        $this->log("✅ Jogos futuros: {$inserted} novos, {$updated} atualizados");
    }
    
    /**
     * Sincroniza jogos ao vivo
     */
    private function syncInPlayEvents() {
        $this->log("\n🔴 Buscando jogos AO VIVO...");
        
        $events = $this->api->getInPlayEvents('1');
        
        if (!$events || !isset($events['results'])) {
            $this->log("⚠️ Nenhum jogo ao vivo no momento.");
            return;
        }
        
        $total = count($events['results']);
        $updated = 0;
        
        $this->log("📊 {$total} jogos ao vivo encontrados. Atualizando...");
        
        foreach ($events['results'] as $event) {
            $result = $this->saveEvent($event, true);
            if ($result === 'updated' || $result === 'inserted') $updated++;
        }
        
        $this->log("✅ Jogos ao vivo: {$updated} atualizados");
    }
    
    /**
     * Salva ou atualiza um evento no banco
     * 
     * @param array $event
     * @param bool $isLive
     * @return string 'inserted', 'updated' ou 'error'
     */
    private function saveEvent($event, $isLive = false) {
        try {
            $pdo = \app\core\crud\Conn::getConn();
            
            $apiId = $event['id'];
            $timestamp = $event['time'] ?? time();
            $dateTime = new DateTime();
            $dateTime->setTimestamp($timestamp);
            
            // Dados básicos
            $timeCasa = $event['home']['name'] ?? '';
            $timeFora = $event['away']['name'] ?? '';
            $data = $dateTime->format('Y-m-d');
            $hora = $dateTime->format('H:i:s');
            
            // Campeonato
            $campeonatoId = $event['league']['id'] ?? 0;
            $campeonatoNome = $event['league']['name'] ?? '';
            
            // Status ao vivo
            $aoVivo = $isLive ? 1 : 0;
            $placarCasa = null;
            $placarFora = null;
            
            if (isset($event['ss'])) {
                $placar = explode('-', $event['ss']);
                $placarCasa = isset($placar[0]) ? (int)$placar[0] : null;
                $placarFora = isset($placar[1]) ? (int)$placar[1] : null;
            }
            
            // Busca ou cria campeonato
            $campeonatoDbId = $this->getOrCreateCampeonato($campeonatoId, $campeonatoNome);
            
            // Busca odds principais e complementares
            $odds = $this->api->getMainOdds($apiId);
            $extraOdds = $this->api->getExtendedOdds($apiId);
            // Mescla preservando o que já existe
            foreach ($extraOdds as $tempo => $campos) {
                if (!isset($odds[$tempo]) || !is_array($odds[$tempo])) {
                    $odds[$tempo] = [];
                }
                foreach ($campos as $campo => $valor) {
                    if ($valor > 1) {
                        $odds[$tempo][$campo] = $valor;
                    }
                }
            }
            $cotacoesJson = json_encode($odds, JSON_UNESCAPED_UNICODE);
            
            // Verifica se jogo já existe
            $stmt = $pdo->prepare("SELECT id FROM sis_jogos WHERE api_id = :api_id");
            $stmt->execute(['api_id' => $apiId]);
            $existing = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            if ($existing) {
                // Atualiza
                $sql = "UPDATE sis_jogos SET 
                    timecasa = :timecasa,
                    timefora = :timefora,
                    data = :data,
                    hora = :hora,
                    campeonato = :campeonato,
                    ao_vivo = :ao_vivo,
                    placar_casa = :placar_casa,
                    placar_fora = :placar_fora,
                    timecasaplacar = :placar_casa2,
                    timeforaplacar = :placar_fora2,
                    cotacoes = :cotacoes,
                    updated_at = NOW(),
                    ativo = '1',
                    status = 1
                WHERE id = :id";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'timecasa' => $timeCasa,
                    'timefora' => $timeFora,
                    'data' => $data,
                    'hora' => $hora,
                    'campeonato' => $campeonatoDbId,
                    'ao_vivo' => $aoVivo,
                    'placar_casa' => $placarCasa,
                    'placar_fora' => $placarFora,
                    'placar_casa2' => $placarCasa ?? 0,
                    'placar_fora2' => $placarFora ?? 0,
                    'cotacoes' => $cotacoesJson,
                    'id' => $existing['id']
                ]);
                
                return 'updated';
                
            } else {
                // Insere novo
                $sql = "INSERT INTO sis_jogos (
                    api_id, timecasa, timefora, data, hora, campeonato,
                    ao_vivo, placar_casa, placar_fora, timecasaplacar, timeforaplacar,
                    cotacoes, created_at, updated_at, ativo, status,
                    apostas, maxapostas, valorapostas, limite1, limite2, limite3,
                    timecasaplacarprimeiro, timeforaplacarprimeiro,
                    timecasaplacarsegundo, timeforaplacarsegundo,
                    totalgols, totalgolsprimeiro, totalgolssegundo,
                    ganhadorprimeiro, ganhadorsegundo, zebra, alteroucotacoes
                ) VALUES (
                    :api_id, :timecasa, :timefora, :data, :hora, :campeonato,
                    :ao_vivo, :placar_casa, :placar_fora, :placar_casa2, :placar_fora2,
                    :cotacoes, NOW(), NOW(), '1', 1,
                    0, 0, 0.00, 0.00, 0.00, 0.00,
                    0, 0, 0, 0, 0, 0, 0, '', '', 0, 0
                )";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    'api_id' => $apiId,
                    'timecasa' => $timeCasa,
                    'timefora' => $timeFora,
                    'data' => $data,
                    'hora' => $hora,
                    'campeonato' => $campeonatoDbId,
                    'ao_vivo' => $aoVivo,
                    'placar_casa' => $placarCasa,
                    'placar_fora' => $placarFora,
                    'placar_casa2' => $placarCasa ?? 0,
                    'placar_fora2' => $placarFora ?? 0,
                    'cotacoes' => $cotacoesJson
                ]);
                
                return 'inserted';
            }
            
        } catch (Exception $e) {
            $this->log("❌ Erro ao salvar evento {$event['id']}: " . $e->getMessage());
            return 'error';
        }
    }
    
    /**
     * Busca ou cria campeonato
     */
    private function getOrCreateCampeonato($apiId, $nome) {
        $pdo = \app\core\crud\Conn::getConn();
        
        // Busca por api_id
        $stmt = $pdo->prepare("SELECT id FROM sis_campeonatos WHERE api_id = :api_id LIMIT 1");
        $stmt->execute(['api_id' => $apiId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($result) {
            return $result['id'];
        }
        
        // Busca por nome (caso já exista sem api_id)
        $stmt = $pdo->prepare("SELECT id FROM sis_campeonatos WHERE title = :nome LIMIT 1");
        $stmt->execute(['nome' => $nome]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($result) {
            // Atualiza com o api_id
            $stmt = $pdo->prepare("UPDATE sis_campeonatos SET api_id = :api_id WHERE id = :id");
            $stmt->execute(['api_id' => $apiId, 'id' => $result['id']]);
            return $result['id'];
        }
        
        // Cria novo campeonato
        $stmt = $pdo->prepare("INSERT INTO sis_campeonatos (api_id, title) VALUES (:api_id, :nome)");
        $stmt->execute(['api_id' => $apiId, 'nome' => $nome]);
        return $pdo->lastInsertId();
    }
    
    /**
     * Remove jogos antigos
     */
    private function cleanOldEvents() {
        $this->log("\n🧹 Limpando jogos antigos...");
        
        $pdo = \app\core\crud\Conn::getConn();
        $dataLimite = date('Y-m-d H:i:s', strtotime('-24 hours'));
        
        $stmt = $pdo->prepare("DELETE FROM sis_jogos WHERE 
            CONCAT(data, ' ', hora) < :data_limite 
            AND ao_vivo = 0
            AND api_id IS NOT NULL
        ");
        $stmt->execute(['data_limite' => $dataLimite]);
        
        $deleted = $stmt->rowCount();
        $this->log("✅ {$deleted} jogos antigos removidos");
    }
    
    /**
     * Adiciona mensagem ao log
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}";
        $this->log[] = $logMessage;
        echo $logMessage . "\n";
    }
    
    /**
     * Retorna log completo
     */
    public function getLog() {
        return $this->log;
    }
}

// Execução direta via CLI ou CRON
if (php_sapi_name() === 'cli') {
    echo "\n";
    echo "╔════════════════════════════════════════╗\n";
    echo "║   LEAGUEBET - SYNC BETSAPI v1.0       ║\n";
    echo "╚════════════════════════════════════════╝\n";
    echo "\n";
    
    $sync = new SyncJogos();
    $sync->sync();
    
    echo "\n";
    echo "✅ Processo finalizado!\n";
    echo "\n";
}

