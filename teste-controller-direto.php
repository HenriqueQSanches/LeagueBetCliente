<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🎯 TESTE DO CONTROLLER - apostarController</h1><hr>";

try {
    require_once 'inc.config.php';
    require_once 'app/modules/website/controllers/apostarController.php';
    
    echo "<h2>1. Instanciando Controller</h2>";
    $controller = new \app\modules\website\controllers\apostarController();
    echo "✅ Controller criado!<br><br>";
    
    echo "<h2>2. Executando jogosAction()</h2>";
    
    ob_start();
    $resultado = $controller->jogosAction();
    $output = ob_get_clean();
    
    if ($output) {
        echo "<h3>⚠️ Output Capturado:</h3>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
    }
    
    echo "<h2>3. Analisando Resultado</h2>";
    
    if (!$resultado) {
        echo "<p style='color: red;'>❌ Nenhum resultado retornado!</p>";
    } else {
        echo "<p style='color: green;'>✅ Resultado retornado!</p>";
        
        echo "<h3>Chaves disponíveis:</h3>";
        echo "<ul>";
        foreach (array_keys($resultado) as $key) {
            echo "<li><strong>{$key}</strong>: " . (is_array($resultado[$key]) ? count($resultado[$key]) . " items" : gettype($resultado[$key])) . "</li>";
        }
        echo "</ul>";
        
        // Verificar cotações
        if (isset($resultado['cotacoes'])) {
            echo "<h3>✅ Cotações: " . count($resultado['cotacoes']) . " encontradas</h3>";
        }
        
        // Verificar grupos
        if (isset($resultado['grupos'])) {
            echo "<h3>✅ Grupos: " . count($resultado['grupos']) . " encontrados</h3>";
        }
        
        // Verificar paises (que na verdade contém os jogos!)
        if (isset($resultado['paises'])) {
            echo "<h3>📊 Países/Jogos: " . count($resultado['paises']) . " encontrados</h3>";
            
            if (count($resultado['paises']) > 0) {
                echo "<h4>🏆 Primeiro País:</h4>";
                echo "<pre>" . print_r($resultado['paises'][0], true) . "</pre>";
                
                // Contar total de jogos
                $totalJogos = 0;
                foreach ($resultado['paises'] as $pais) {
                    if (isset($pais['campeonatos'])) {
                        foreach ($pais['campeonatos'] as $camp) {
                            if (isset($camp['jogos'])) {
                                $totalJogos += count($camp['jogos']);
                            }
                        }
                    }
                }
                
                echo "<h3 style='color: green;'>🎮 TOTAL DE JOGOS DISPONÍVEIS: {$totalJogos}</h3>";
                
                if ($totalJogos > 0) {
                    echo "<h2 style='color: green;'>✅ SUCESSO! OS JOGOS ESTÃO SENDO RETORNADOS!</h2>";
                    echo "<p>O backend está funcionando corretamente.</p>";
                    echo "<p><strong>Se os jogos não aparecem no site, o problema é no frontend (JavaScript/Vue.js).</strong></p>";
                } else {
                    echo "<h2 style='color: red;'>❌ Estrutura OK, mas sem jogos!</h2>";
                }
            } else {
                echo "<p style='color: red;'>❌ Array de países está vazio!</p>";
            }
        }
        
        // Verificar datas
        if (isset($resultado['datas'])) {
            echo "<h3>📅 Datas: " . count($resultado['datas']) . " encontradas</h3>";
            if (count($resultado['datas']) > 0) {
                echo "<pre>" . print_r($resultado['datas'], true) . "</pre>";
            }
        }
        
        // Mostrar estrutura completa (resumida)
        echo "<h3>📋 Estrutura Completa (resumida):</h3>";
        echo "<pre>";
        $resumo = [];
        foreach ($resultado as $key => $value) {
            if (is_array($value)) {
                $resumo[$key] = count($value) . " items";
            } else {
                $resumo[$key] = gettype($value);
            }
        }
        print_r($resumo);
        echo "</pre>";
    }
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ ERRO:</h2>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p>⏰ Teste concluído em: " . date('d/m/Y H:i:s') . "</p>";
?>

