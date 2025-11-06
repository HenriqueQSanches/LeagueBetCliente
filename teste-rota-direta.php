<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>🔍 Teste de Rota - LeagueBet</title>
    <style>
        body { font-family: monospace; background: #1a1a1a; color: #fff; padding: 20px; }
        h1 { color: #ff6600; }
        .section { background: rgba(255,102,0,0.1); border: 2px solid #ff6600; padding: 20px; margin: 20px 0; border-radius: 10px; }
        pre { background: #000; padding: 15px; border-radius: 5px; overflow-x: auto; border: 1px solid #ff6600; }
        .success { color: #00ff00; }
        .error { color: #ff0000; }
        .info { color: #00bfff; }
    </style>
</head>
<body>
    <h1>🔍 TESTE DE ROTA DIRETA</h1>
    
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<div class='section'>";
echo "<h2>1. Carregar Configuração</h2>";

try {
    require_once 'inc.config.php';
    echo "<p class='success'>✅ inc.config.php carregado!</p>";
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
    exit;
}

echo "</div>";

echo "<div class='section'>";
echo "<h2>2. Carregar Controller Manualmente</h2>";

try {
    require_once 'app/modules/website/controllers/apostarController.php';
    echo "<p class='success'>✅ Controller carregado!</p>";
    
    // Instanciar
    $controller = new \app\modules\website\controllers\apostarController();
    echo "<p class='success'>✅ Controller instanciado!</p>";
    
    // Executar
    echo "<h3>3. Executar jogosAction()</h3>";
    ob_start();
    $resultado = $controller->jogosAction();
    $output = ob_get_clean();
    
    if ($output) {
        echo "<p class='error'>⚠️ Controller produziu output HTML:</p>";
        echo "<pre>" . htmlspecialchars($output) . "</pre>";
    }
    
    if ($resultado) {
        echo "<p class='success'>✅ Resultado obtido!</p>";
        
        // Contar jogos
        $totalJogos = 0;
        if (isset($resultado['paises'])) {
            foreach ($resultado['paises'] as $pais) {
                if (isset($pais['campeonatos'])) {
                    foreach ($pais['campeonatos'] as $camp) {
                        if (isset($camp['jogos'])) {
                            $totalJogos += count($camp['jogos']);
                        }
                    }
                }
            }
        }
        
        echo "<div style='background: rgba(0,255,0,0.2); padding: 15px; border-radius: 5px; margin: 15px 0;'>";
        echo "<h3 class='success'>🎉 SUCESSO!</h3>";
        echo "<p><strong>Total de Jogos Encontrados:</strong> {$totalJogos}</p>";
        echo "<p><strong>Cotações:</strong> " . (isset($resultado['cotacoes']) ? count($resultado['cotacoes']) : 0) . "</p>";
        echo "<p><strong>Grupos:</strong> " . (isset($resultado['grupos']) ? count($resultado['grupos']) : 0) . "</p>";
        echo "<p><strong>Países:</strong> " . (isset($resultado['paises']) ? count($resultado['paises']) : 0) . "</p>";
        echo "<p><strong>Datas:</strong> " . (isset($resultado['datas']) ? count($resultado['datas']) : 0) . "</p>";
        echo "</div>";
        
        // Mostrar JSON
        echo "<h3>4. JSON Gerado (primeiros 5 jogos)</h3>";
        
        $primeirosCincoJogos = [];
        $contador = 0;
        if (isset($resultado['paises'])) {
            foreach ($resultado['paises'] as $pais) {
                if ($contador >= 5) break;
                if (isset($pais['campeonatos'])) {
                    foreach ($pais['campeonatos'] as $camp) {
                        if ($contador >= 5) break;
                        if (isset($camp['jogos'])) {
                            foreach ($camp['jogos'] as $jogo) {
                                if ($contador >= 5) break;
                                $primeirosCincoJogos[] = [
                                    'id' => $jogo['id'],
                                    'casa' => $jogo['casa'],
                                    'fora' => $jogo['fora'],
                                    'campeonato' => $camp['title'],
                                    'data' => $jogo['data'],
                                    'hora' => $jogo['hora'],
                                ];
                                $contador++;
                            }
                        }
                    }
                }
            }
        }
        
        if (count($primeirosCincoJogos) > 0) {
            echo "<pre>" . json_encode($primeirosCincoJogos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        } else {
            echo "<p class='error'>❌ Nenhum jogo encontrado no resultado!</p>";
        }
        
        // Verificar o que está sendo retornado para o frontend
        echo "<h3>5. Teste de Conversão para JSON (como seria enviado ao frontend)</h3>";
        $json = json_encode($resultado);
        if ($json === false) {
            echo "<p class='error'>❌ Erro ao converter para JSON: " . json_last_error_msg() . "</p>";
        } else {
            echo "<p class='success'>✅ JSON válido gerado!</p>";
            echo "<p class='info'>Tamanho: " . strlen($json) . " bytes</p>";
            echo "<p class='info'>Primeira linha do JSON:</p>";
            echo "<pre>" . htmlspecialchars(substr($json, 0, 500)) . "...</pre>";
        }
        
    } else {
        echo "<p class='error'>❌ jogosAction() não retornou dados!</p>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "</div>";

echo "<div class='section'>";
echo "<h2>6. Conclusão</h2>";
echo "<p class='info'>Se você viu jogos acima, o backend está funcionando!</p>";
echo "<p class='info'>O problema está na rota <code>/apostar/jogos</code> não estar retornando JSON.</p>";
echo "<p class='info'>Vamos corrigir o sistema de rotas na próxima etapa.</p>";
echo "</div>";

?>

</body>
</html>

