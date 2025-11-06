<?php
/**
 * Script para importar resultados dos jogos
 * Pode ser executado via:
 * 1. Linha de comando: php resultados.php
 * 2. Navegador: http://localhost:8000/resultados.php
 * 3. Task Scheduler / Cron
 */

// Configurar ambiente
ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar output buffer
ob_start();

echo "╔═══════════════════════════════════════════════════════╗\n";
echo "║  LEAGUEBET - IMPORTAÇÃO DE RESULTADOS                 ║\n";
echo "╚═══════════════════════════════════════════════════════╝\n\n";

// Verificar se está sendo executado via CLI ou navegador
$is_cli = (php_sapi_name() === 'cli');

if (!$is_cli) {
    // Se for navegador, usar HTML
    ob_clean();
    echo "<!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>LeagueBet - Importar Resultados</title>
        <style>
            body { font-family: 'Courier New', monospace; background: #1a1a1a; color: #00ff00; padding: 20px; }
            .container { max-width: 1200px; margin: 0 auto; }
            h1 { color: #00ff00; text-align: center; }
            .log { background: #000; padding: 15px; border-radius: 5px; white-space: pre-wrap; }
            .success { color: #00ff00; }
            .error { color: #ff0000; }
            .info { color: #00aaff; }
            .warning { color: #ffaa00; }
        </style>
    </head>
    <body>
    <div class='container'>
        <h1>🔄 Importando Resultados dos Jogos</h1>
        <div class='log'>";
}

try {
    // Opção 1: Usar a URL do sistema (recomendado)
    $url = "http://localhost:8000/cron/jogos/resultados";
    
    echo "📡 Conectando à API de Resultados...\n";
    echo "🔗 URL: $url\n\n";
    
    // Tentar CURL primeiro
    if (function_exists('curl_init')) {
        echo "✅ Usando CURL...\n";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("Erro CURL: $error");
        }
        
        if ($http_code != 200) {
            throw new Exception("Código HTTP: $http_code");
        }
        
    } else {
        // Fallback para file_get_contents
        echo "⚠️ CURL não disponível, usando file_get_contents...\n";
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 300,
                'ignore_errors' => true
            ]
        ]);
        
        $response = file_get_contents($url, false, $context);
        
        if ($response === false) {
            throw new Exception("Não foi possível conectar à URL");
        }
    }
    
    echo "✅ Resposta recebida!\n\n";
    
    // Tentar decodificar JSON
    $data = json_decode($response, true);
    
    if (json_last_error() === JSON_ERROR_NONE) {
        // Resposta JSON válida
        echo "📊 RESULTADO DA IMPORTAÇÃO:\n";
        echo "══════════════════════════════════════════════════════\n\n";
        
        if (isset($data['result']) && $data['result'] == 1) {
            echo "✅ SUCESSO!\n\n";
            
            if (isset($data['message'])) {
                echo "📝 Mensagem:\n";
                echo $data['message'] . "\n\n";
            }
            
            // Exibir estatísticas se disponíveis
            if (isset($data['totalDefinidos'])) {
                echo "📈 Estatísticas:\n";
                echo "   • Jogos processados: {$data['totalDefinidos']}\n";
                if (isset($data['totalJogos'])) {
                    echo "   • Total de jogos: {$data['totalJogos']}\n";
                }
                echo "\n";
            }
        } else {
            echo "⚠️ ATENÇÃO!\n\n";
            echo "Mensagem: " . ($data['message'] ?? 'Sem mensagem') . "\n\n";
        }
        
    } else {
        // Não é JSON, exibir resposta direta
        echo "📄 RESPOSTA:\n";
        echo "══════════════════════════════════════════════════════\n\n";
        echo $response . "\n\n";
    }
    
    echo "══════════════════════════════════════════════════════\n";
    echo "✅ Importação concluída!\n";
    echo "⏰ " . date('d/m/Y H:i:s') . "\n";
    
} catch (Exception $e) {
    echo "══════════════════════════════════════════════════════\n";
    echo "❌ ERRO DURANTE A IMPORTAÇÃO!\n";
    echo "══════════════════════════════════════════════════════\n\n";
    echo "Erro: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n\n";
    
    echo "💡 SOLUÇÕES:\n";
    echo "1. Verifique se o servidor está rodando (Apache/PHP)\n";
    echo "2. Confirme se a URL está correta: $url\n";
    echo "3. Teste a URL diretamente no navegador\n";
    echo "4. Verifique os logs do Apache/PHP\n";
    echo "5. Confirme se as extensões CURL/JSON estão ativas\n\n";
}

if (!$is_cli) {
    echo "</div>
        <div style='text-align: center; margin-top: 20px;'>
            <a href='status-api.php' style='padding: 10px 20px; background: #00ff00; color: #000; text-decoration: none; border-radius: 5px; font-weight: bold;'>Ver Status da API</a>
            <a href='http://localhost:8000' style='padding: 10px 20px; background: #0088ff; color: #fff; text-decoration: none; border-radius: 5px; font-weight: bold; margin-left: 10px;'>Ir para o Site</a>
        </div>
    </div>
    </body>
    </html>";
}

// Finalizar output buffer
ob_end_flush();
?>

