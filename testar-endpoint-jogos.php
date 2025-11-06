<?php
// Script para testar o endpoint de jogos diretamente

require_once 'inc.config.php';

use app\modules\website\controllers\apostarController;

echo "<h1>Teste do Endpoint apostar/jogos</h1>";
echo "<hr>";

try {
    $controller = new apostarController();
    $resultado = $controller->jogosAction();
    
    echo "<h3>✅ Endpoint executado com sucesso!</h3>";
    
    echo "<p><strong>Cotações:</strong> " . count($resultado['cotacoes']) . "</p>";
    echo "<p><strong>Grupos:</strong> " . count($resultado['grupos']) . "</p>";
    echo "<p><strong>Países:</strong> " . count($resultado['paises']) . "</p>";
    echo "<p><strong>Datas:</strong> " . count($resultado['datas']) . "</p>";
    
    if (count($resultado['paises']) > 0) {
        echo "<hr>";
        echo "<h3>Primeiro País:</h3>";
        echo "<pre>";
        print_r($resultado['paises'][0]);
        echo "</pre>";
    }
    
    echo "<hr>";
    echo "<h3>JSON Response (primeiros 2000 caracteres):</h3>";
    echo "<pre>";
    $json = json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo substr($json, 0, 2000);
    if (strlen($json) > 2000) {
        echo "\n\n... (+" . (strlen($json) - 2000) . " caracteres)";
    }
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<div style='background: #ffcccc; padding: 20px; border: 2px solid #cc0000; border-radius: 5px;'>";
    echo "<h3 style='color: #cc0000;'>❌ ERRO</h3>";
    echo "<p><strong>Mensagem:</strong> {$e->getMessage()}</p>";
    echo "<pre>{$e->getTraceAsString()}</pre>";
    echo "</div>";
}
?>

