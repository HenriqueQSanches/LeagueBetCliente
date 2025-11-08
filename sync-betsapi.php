<?php
/**
 * Script de Sincronização Manual - BetsAPI
 * 
 * Execute este arquivo para sincronizar jogos manualmente
 * URL: http://localhost/Cliente/LeagueBetCliente-main/sync-betsapi.php
 */

require_once __DIR__ . '/inc.config.php';
require_once __DIR__ . '/app/modules/betsapi/BetsAPIClient.php';
require_once __DIR__ . '/app/modules/betsapi/SyncJogos.php';

use app\core\crud\Conn;

// Aumenta tempo de execução
set_time_limit(300);
ini_set('max_execution_time', 300);

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeagueBet - Sincronização BetsAPI</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            background: #1a1a1a;
            color: #4caf50;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #000;
            border: 2px solid #4caf50;
            border-radius: 10px;
            padding: 30px;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #4caf50;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #ff9800;
            font-size: 2em;
            margin-bottom: 10px;
        }
        
        .terminal {
            background: #0a0a0a;
            border: 1px solid #333;
            border-radius: 5px;
            padding: 20px;
            min-height: 400px;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .log-line {
            margin: 5px 0;
        }
        
        .log-success {
            color: #4caf50;
        }
        
        .log-error {
            color: #f44336;
        }
        
        .log-warning {
            color: #ff9800;
        }
        
        .log-info {
            color: #2196f3;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 30px;
        }
        
        .stat-box {
            background: #0a0a0a;
            border: 1px solid #333;
            border-radius: 5px;
            padding: 15px;
            text-align: center;
        }
        
        .stat-box h3 {
            color: #ff9800;
            font-size: 2em;
            margin-bottom: 5px;
        }
        
        .stat-box p {
            color: #ccc;
            font-size: 0.9em;
        }
        
        .btn {
            display: inline-block;
            background: #ff9800;
            color: #000;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin: 20px 5px 0;
            cursor: pointer;
            border: none;
            font-size: 1em;
        }
        
        .btn:hover {
            background: #f57c00;
        }
        
        .loading {
            text-align: center;
            padding: 40px;
        }
        
        .loading::after {
            content: '...';
            animation: dots 1.5s steps(4, end) infinite;
        }
        
        @keyframes dots {
            0%, 20% { content: '.'; }
            40% { content: '..'; }
            60%, 100% { content: '...'; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔄 SINCRONIZAÇÃO BETSAPI</h1>
            <p style="color: #ccc;">LeagueBet - Atualização de Jogos em Tempo Real</p>
        </div>

        <div class="terminal">
            <?php
            $startTime = microtime(true);
            
            echo '<div class="log-line log-info">╔════════════════════════════════════════════════════════════╗</div>';
            echo '<div class="log-line log-info">║   LEAGUEBET - SINCRONIZAÇÃO BETSAPI                       ║</div>';
            echo '<div class="log-line log-info">╚════════════════════════════════════════════════════════════╝</div>';
            echo '<div class="log-line">&nbsp;</div>';
            
            try {
                $sync = new SyncJogos();
                
                // Captura output
                ob_start();
                $result = $sync->sync();
                $output = ob_get_clean();
                
                // Processa e exibe log com cores
                $lines = explode("\n", $output);
                foreach ($lines as $line) {
                    if (empty(trim($line))) {
                        echo '<div class="log-line">&nbsp;</div>';
                        continue;
                    }
                    
                    $class = 'log-line';
                    if (strpos($line, '✅') !== false || strpos($line, 'sucesso') !== false) {
                        $class .= ' log-success';
                    } elseif (strpos($line, '❌') !== false || strpos($line, 'ERRO') !== false) {
                        $class .= ' log-error';
                    } elseif (strpos($line, '⚠️') !== false || strpos($line, 'Aviso') !== false) {
                        $class .= ' log-warning';
                    } elseif (strpos($line, '🔴') !== false || strpos($line, 'AO VIVO') !== false) {
                        $class .= ' log-error';
                    } else {
                        $class .= ' log-info';
                    }
                    
                    echo '<div class="' . $class . '">' . htmlspecialchars($line) . '</div>';
                }
                
                $endTime = microtime(true);
                $executionTime = round($endTime - $startTime, 2);
                
                echo '<div class="log-line">&nbsp;</div>';
                echo '<div class="log-line log-success">╔════════════════════════════════════════════════════════════╗</div>';
                echo '<div class="log-line log-success">║   SINCRONIZAÇÃO CONCLUÍDA COM SUCESSO!                    ║</div>';
                echo '<div class="log-line log-success">╚════════════════════════════════════════════════════════════╝</div>';
                echo '<div class="log-line log-info">⏱️  Tempo de execução: ' . $executionTime . ' segundos</div>';
                
                // Busca estatísticas do banco
                $pdo = Conn::getConn();
                
                $totalJogos = 0;
                $jogosHoje = 0;
                $jogosAoVivo = 0;
                $totalCampeonatos = 0;
                
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM sis_jogos WHERE ativo = '1'");
                if ($stmt) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $totalJogos = $row['total'];
                }
                
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM sis_jogos WHERE data = CURDATE() AND ativo = '1'");
                if ($stmt) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $jogosHoje = $row['total'];
                }
                
                $stmt = $pdo->query("SELECT COUNT(*) as total FROM sis_jogos WHERE ao_vivo = 1 AND ativo = '1'");
                if ($stmt) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $jogosAoVivo = $row['total'];
                }
                
                $stmt = $pdo->query("SELECT COUNT(DISTINCT campeonato) as total FROM sis_jogos WHERE ativo = '1'");
                if ($stmt) {
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $totalCampeonatos = $row['total'];
                }
                
                ?>
                
                <div class="stats">
                    <div class="stat-box">
                        <h3><?php echo $totalJogos; ?></h3>
                        <p>Total de Jogos</p>
                    </div>
                    <div class="stat-box">
                        <h3><?php echo $jogosHoje; ?></h3>
                        <p>Jogos Hoje</p>
                    </div>
                    <div class="stat-box">
                        <h3 style="color: #f44336;"><?php echo $jogosAoVivo; ?></h3>
                        <p>Jogos Ao Vivo</p>
                    </div>
                    <div class="stat-box">
                        <h3><?php echo $totalCampeonatos; ?></h3>
                        <p>Campeonatos</p>
                    </div>
                </div>
                
                <?php
                
            } catch (Exception $e) {
                echo '<div class="log-line log-error">❌ ERRO FATAL: ' . htmlspecialchars($e->getMessage()) . '</div>';
                echo '<div class="log-line log-error">Stack Trace:</div>';
                echo '<div class="log-line log-error">' . htmlspecialchars($e->getTraceAsString()) . '</div>';
            }
            ?>
        </div>

        <div style="text-align: center;">
            <a href="test-betsapi.php" class="btn">🧪 Voltar para Testes</a>
            <a href="./" class="btn">🏠 Ir para o Site</a>
            <a href="javascript:location.reload()" class="btn">🔄 Sincronizar Novamente</a>
        </div>

        <div style="text-align: center; padding: 30px 0; color: #666; font-size: 0.9em;">
            <p>LeagueBet © 2025 - Sincronização automática via BetsAPI</p>
            <p style="margin-top: 10px;">
                <strong>Dica:</strong> Configure um CRON para executar automaticamente:<br>
                <code style="background: #0a0a0a; padding: 5px 10px; border-radius: 3px; color: #4caf50;">
                    */5 * * * * cd /caminho/para/projeto && php app/modules/betsapi/SyncJogos.php
                </code>
            </p>
        </div>
    </div>
</body>
</html>

