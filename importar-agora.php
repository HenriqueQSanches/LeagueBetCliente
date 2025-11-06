<?php
/**
 * Script de Importação Direta - Sem Necessidade de Login
 * Acesse: http://localhost:8000/importar-agora.php
 */

// Configurar ambiente
ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir autoloader e configurações
require_once 'vendor/autoload.php';
require_once 'inc.config.php';

use app\helpers\APIMarjo;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Jogos - LeagueBet</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: #00ff00;
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: #000;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0, 255, 0, 0.2);
        }
        
        h1 {
            text-align: center;
            color: #00ff00;
            margin-bottom: 20px;
            text-shadow: 0 0 10px #00ff00;
        }
        
        .log-box {
            background: #111;
            padding: 20px;
            border-radius: 5px;
            border: 2px solid #00ff00;
            margin: 20px 0;
            white-space: pre-wrap;
            font-size: 14px;
            max-height: 500px;
            overflow-y: auto;
        }
        
        .success { color: #00ff00; font-weight: bold; }
        .error { color: #ff0000; font-weight: bold; }
        .info { color: #00aaff; font-weight: bold; }
        .warning { color: #ffaa00; font-weight: bold; }
        
        .btn-container {
            text-align: center;
            margin-top: 20px;
        }
        
        .btn {
            padding: 15px 30px;
            margin: 10px;
            background: #00ff00;
            color: #000;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: #00cc00;
            transform: scale(1.05);
        }
        
        .btn-blue {
            background: #0088ff;
            color: #fff;
        }
        
        .btn-orange {
            background: #ff9800;
            color: #000;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .stat-box {
            background: #1a1a1a;
            padding: 15px;
            border-radius: 5px;
            border: 1px solid #00ff00;
            text-align: center;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .stat-label {
            font-size: 12px;
            opacity: 0.8;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎮 IMPORTAÇÃO DE JOGOS - LEAGUEBET</h1>
        
        <div class="log-box">
<?php

echo "╔═══════════════════════════════════════════════════════╗\n";
echo "║  INICIANDO IMPORTAÇÃO DE JOGOS DA API                 ║\n";
echo "╚═══════════════════════════════════════════════════════╝\n\n";

echo "<span class='info'>⏰ Início: " . date('d/m/Y H:i:s') . "</span>\n\n";

try {
    
    echo "<span class='info'>📡 Conectando à API MarjoSports...</span>\n";
    
    $api = new APIMarjo();
    
    echo "<span class='success'>✅ Conexão estabelecida!</span>\n\n";
    
    echo "<span class='info'>🔄 Importando jogos, times e campeonatos...</span>\n";
    echo "<span class='warning'>⏳ Aguarde, isso pode demorar 30-60 segundos...</span>\n\n";
    
    $resultado = $api->importarJogos();
    
    if (is_array($resultado) && isset($resultado['result']) && $resultado['result'] == 1) {
        
        echo "╔═══════════════════════════════════════════════════════╗\n";
        echo "║           <span class='success'>✅ IMPORTAÇÃO CONCLUÍDA COM SUCESSO!</span>       ║\n";
        echo "╚═══════════════════════════════════════════════════════╝\n\n";
        
        $novos = $resultado['novos'] ?? 0;
        $antigos = $resultado['antigos'] ?? 0;
        $erros = $resultado['erros'] ?? 0;
        $message = $resultado['message'] ?? 'Importação realizada';
        
        echo "<div class='stats'>";
        echo "<div class='stat-box'>";
        echo "<div class='stat-label'>Jogos Novos</div>";
        echo "<div class='stat-number success'>$novos</div>";
        echo "</div>";
        
        echo "<div class='stat-box'>";
        echo "<div class='stat-label'>Jogos Atualizados</div>";
        echo "<div class='stat-number info'>$antigos</div>";
        echo "</div>";
        
        echo "<div class='stat-box'>";
        echo "<div class='stat-label'>Erros</div>";
        echo "<div class='stat-number " . ($erros > 0 ? 'warning' : 'success') . "'>$erros</div>";
        echo "</div>";
        echo "</div>";
        
        echo "\n<span class='success'>📝 Mensagem: $message</span>\n";
        echo "<span class='info'>⏰ Concluído em: " . date('d/m/Y H:i:s') . "</span>\n\n";
        
        if ($novos > 0) {
            echo "<span class='success'>🎉 $novos novos jogos foram adicionados ao sistema!</span>\n";
        }
        
        if ($antigos > 0) {
            echo "<span class='info'>🔄 $antigos jogos existentes foram atualizados!</span>\n";
        }
        
        if ($erros > 0) {
            echo "<span class='warning'>⚠️ $erros jogos tiveram problemas na importação (normal).</span>\n";
        }
        
        echo "\n<span class='success'>✅ Sistema pronto para uso!</span>\n";
        
    } else {
        throw new Exception("Falha na importação: " . print_r($resultado, true));
    }
    
} catch (Exception $e) {
    echo "╔═══════════════════════════════════════════════════════╗\n";
    echo "║              <span class='error'>❌ ERRO NA IMPORTAÇÃO!</span>                  ║\n";
    echo "╚═══════════════════════════════════════════════════════╝\n\n";
    
    echo "<span class='error'>Erro: " . $e->getMessage() . "</span>\n";
    echo "<span class='error'>Arquivo: " . $e->getFile() . "</span>\n";
    echo "<span class='error'>Linha: " . $e->getLine() . "</span>\n\n";
    
    echo "<span class='warning'>💡 SOLUÇÕES:</span>\n";
    echo "1. Verifique se o Apache e MySQL estão rodando\n";
    echo "2. Confirme se tem conexão com a internet\n";
    echo "3. Teste a API: https://apijogos.com/betsports3.php\n";
    echo "4. Verifique os logs do Apache em C:\\xampp\\apache\\logs\\error.log\n";
    echo "5. Execute via terminal: php jogos.php\n\n";
}

?>
        </div>
        
        <div class="btn-container">
            <a href="http://localhost:8000" class="btn">🏠 Ir para o Site</a>
            <a href="status-api.php" class="btn btn-blue">📊 Ver Status</a>
            <a href="importar-agora.php" class="btn btn-orange">🔄 Importar Novamente</a>
        </div>
        
        <div style="text-align: center; margin-top: 30px; opacity: 0.7; font-size: 12px;">
            <p>🚀 LeagueBet - Sistema de Apostas Esportivas</p>
            <p>Desenvolvido por <strong>Henrique Sanches</strong></p>
        </div>
    </div>
</body>
</html>

