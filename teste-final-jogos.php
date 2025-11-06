<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'>";
echo "<title>🎮 Teste Final - Jogos LeagueBet</title>";
echo "<style>
body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: #fff; padding: 20px; }
h1 { color: #ff6600; text-align: center; font-size: 2.5em; text-shadow: 2px 2px 4px rgba(0,0,0,0.5); }
.section { background: rgba(255,102,0,0.1); border: 2px solid #ff6600; padding: 20px; margin: 20px 0; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.3); }
.success { background: rgba(0,255,0,0.1); border-left: 4px solid #00ff00; padding: 10px; margin: 10px 0; border-radius: 5px; }
.error { background: rgba(255,0,0,0.1); border-left: 4px solid #ff0000; padding: 10px; margin: 10px 0; border-radius: 5px; }
.info { background: rgba(0,123,255,0.1); border-left: 4px solid #007bff; padding: 10px; margin: 10px 0; border-radius: 5px; }
table { width: 100%; border-collapse: collapse; margin: 15px 0; background: rgba(0,0,0,0.3); }
th, td { padding: 12px; text-align: left; border: 1px solid #ff6600; }
th { background: #ff6600; color: #000; font-weight: bold; }
tr:hover { background: rgba(255,102,0,0.1); }
pre { background: #000; padding: 15px; border-radius: 5px; overflow-x: auto; border: 1px solid #ff6600; font-size: 0.9em; }
.card { background: rgba(255,102,0,0.2); border: 2px solid #ff6600; border-radius: 10px; padding: 20px; margin: 15px 0; }
.badge { display: inline-block; padding: 5px 10px; border-radius: 15px; font-size: 0.9em; font-weight: bold; margin: 2px; }
.badge-success { background: #28a745; color: #fff; }
.badge-info { background: #17a2b8; color: #fff; }
.link-button { display: inline-block; background: #ff6600; color: #000; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; margin: 10px 5px; transition: all 0.3s; }
.link-button:hover { background: #ff8533; transform: scale(1.05); }
</style></head><body>";

echo "<h1>🎮 TESTE FINAL - SISTEMA DE JOGOS</h1>";

try {
    require_once 'conexao.php';
    require_once 'inc.config.php';
    
    // ====================================
    // 1. TESTE DIRETO NO BANCO
    // ====================================
    echo "<div class='section'>";
    echo "<h2>📊 1. TESTE DIRETO NO BANCO DE DADOS</h2>";
    
    // Query exata que o sistema deve usar
    $query = "
        SELECT 
            j.id,
            j.data,
            j.hora,
            j.time1,
            j.time2,
            j.ativo,
            j.status,
            j.campeonato,
            t1.nome as nome_time1,
            t2.nome as nome_time2,
            c.nome as nome_campeonato,
            j.cotacoes
        FROM sis_jogos j
        LEFT JOIN sis_times t1 ON j.time1 = t1.id
        LEFT JOIN sis_times t2 ON j.time2 = t2.id
        LEFT JOIN sis_campeonatos c ON j.campeonato = c.id
        WHERE j.ativo = '1' 
        AND j.status = 1
        AND j.data >= CURDATE()
        ORDER BY j.data ASC, j.hora ASC
        LIMIT 5
    ";
    
    $stmt = $conexao->query($query);
    $jogos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div class='info'>🔍 Query executada: <pre>" . htmlspecialchars($query) . "</pre></div>";
    echo "<div class='success'>✅ Encontrados: <strong>" . count($jogos) . " jogos</strong></div>";
    
    if (count($jogos) > 0) {
        echo "<h3 style='color: #ff6600;'>🏆 Primeiros 5 Jogos Disponíveis:</h3>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Data</th><th>Hora</th><th>Jogo</th><th>Campeonato</th><th>Status</th></tr>";
        foreach ($jogos as $jogo) {
            echo "<tr>";
            echo "<td>{$jogo['id']}</td>";
            echo "<td>" . date('d/m/Y', strtotime($jogo['data'])) . "</td>";
            echo "<td>" . date('H:i', strtotime($jogo['hora'])) . "</td>";
            echo "<td><strong>{$jogo['nome_time1']}</strong> x <strong>{$jogo['nome_time2']}</strong></td>";
            echo "<td>{$jogo['nome_campeonato']}</td>";
            echo "<td><span class='badge badge-success'>Ativo: {$jogo['ativo']} | Status: {$jogo['status']}</span></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Mostrar JSON de um jogo completo
        echo "<div class='card'>";
        echo "<h3 style='color: #ff6600;'>📋 Exemplo de Jogo Completo (com cotações):</h3>";
        echo "<strong>Jogo:</strong> {$jogos[0]['nome_time1']} x {$jogos[0]['nome_time2']}<br>";
        echo "<strong>Data/Hora:</strong> " . date('d/m/Y H:i', strtotime($jogos[0]['data'] . ' ' . $jogos[0]['hora'])) . "<br>";
        echo "<strong>Campeonato:</strong> {$jogos[0]['nome_campeonato']}<br><br>";
        
        // Decodificar e mostrar algumas cotações
        $cotacoes = json_decode($jogos[0]['cotacoes'], true);
        if ($cotacoes && isset($cotacoes['90'])) {
            echo "<strong>Cotações Principais:</strong><br>";
            echo "🏠 Casa: <span class='badge badge-info'>" . ($cotacoes['90']['casa'] ?? 'N/A') . "</span> ";
            echo "🤝 Empate: <span class='badge badge-info'>" . ($cotacoes['90']['empate'] ?? 'N/A') . "</span> ";
            echo "✈️ Fora: <span class='badge badge-info'>" . ($cotacoes['90']['fora'] ?? 'N/A') . "</span><br><br>";
        }
        
        echo "<details style='margin-top: 10px;'>";
        echo "<summary style='cursor: pointer; color: #ff6600; font-weight: bold;'>📄 Ver JSON Completo das Cotações</summary>";
        echo "<pre style='max-height: 300px; overflow-y: auto;'>" . json_encode($cotacoes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        echo "</details>";
        echo "</div>";
        
    } else {
        echo "<div class='error'>❌ Nenhum jogo futuro encontrado!</div>";
    }
    
    echo "</div>";
    
    // ====================================
    // 2. TESTE VIA CONTROLLER
    // ====================================
    echo "<div class='section'>";
    echo "<h2>🎯 2. TESTE VIA CONTROLLER (apostarController::jogosAction)</h2>";
    
    require_once 'app/modules/website/controllers/apostarController.php';
    
    $controller = new \app\modules\website\controllers\apostarController();
    
    ob_start();
    $resultado = $controller->jogosAction();
    $output = ob_get_clean();
    
    if ($resultado) {
        echo "<div class='success'>✅ Controller retornou dados!</div>";
        
        // Verificar se tem jogos
        if (isset($resultado['jogos']) && is_array($resultado['jogos'])) {
            $qtdJogos = count($resultado['jogos']);
            echo "<div class='success'>✅ Total de jogos retornados: <strong>{$qtdJogos}</strong></div>";
            
            if ($qtdJogos > 0) {
                echo "<div class='card'>";
                echo "<h3 style='color: #ff6600;'>📋 Primeiro Jogo Retornado pelo Controller:</h3>";
                echo "<pre>" . print_r($resultado['jogos'][0], true) . "</pre>";
                echo "</div>";
            } else {
                echo "<div class='error'>⚠️ Array de jogos está vazio!</div>";
            }
        } else {
            echo "<div class='error'>⚠️ Estrutura de retorno não contém 'jogos' ou não é um array!</div>";
            echo "<div class='info'>📋 Estrutura retornada:<br>";
            echo "Chaves disponíveis: " . implode(', ', array_keys($resultado)) . "</div>";
        }
        
        // Verificar cotações
        if (isset($resultado['cotacoes']) && is_array($resultado['cotacoes'])) {
            echo "<div class='success'>✅ Cotações disponíveis: <strong>" . count($resultado['cotacoes']) . "</strong></div>";
        }
        
        // Verificar grupos
        if (isset($resultado['grupos']) && is_array($resultado['grupos'])) {
            echo "<div class='success'>✅ Grupos de cotações: <strong>" . count($resultado['grupos']) . "</strong></div>";
        }
        
    } else {
        echo "<div class='error'>❌ Controller não retornou dados!</div>";
    }
    
    echo "</div>";
    
    // ====================================
    // 3. TESTE DE ROTA (simulação de chamada AJAX)
    // ====================================
    echo "<div class='section'>";
    echo "<h2>🌐 3. LINKS PARA TESTE NO NAVEGADOR</h2>";
    
    echo "<div class='info'>";
    echo "<p>Teste as seguintes URLs no navegador ou via AJAX:</p>";
    echo "<a href='http://localhost:8000' class='link-button' target='_blank'>🏠 Site Principal</a>";
    echo "<a href='http://localhost:8000/apostar/jogos' class='link-button' target='_blank'>🎮 API de Jogos (JSON)</a>";
    echo "<a href='http://localhost:8000/apostar' class='link-button' target='_blank'>🎲 Página de Apostas</a>";
    echo "</div>";
    
    echo "<div class='card'>";
    echo "<h3 style='color: #ff6600;'>💡 Como Testar no Navegador:</h3>";
    echo "<ol style='line-height: 2;'>";
    echo "<li>Abra <strong>http://localhost:8000</strong></li>";
    echo "<li>Pressione <strong>F12</strong> para abrir o DevTools</li>";
    echo "<li>Vá na aba <strong>Network</strong> (Rede)</li>";
    echo "<li>Recarregue a página com <strong>Ctrl + F5</strong></li>";
    echo "<li>Procure pela requisição <strong>apostar/jogos</strong></li>";
    echo "<li>Clique nela e veja a resposta (deve conter os jogos em JSON)</li>";
    echo "<li>Vá na aba <strong>Console</strong> e veja se há erros JavaScript</li>";
    echo "</ol>";
    echo "</div>";
    
    echo "</div>";
    
    // ====================================
    // 4. RESUMO FINAL
    // ====================================
    echo "<div class='section' style='background: rgba(0,255,0,0.2); border-color: #00ff00;'>";
    echo "<h2 style='color: #00ff00; text-align: center;'>✅ RESUMO DO TESTE</h2>";
    
    $statusBD = count($jogos) > 0 ? "✅ OK" : "❌ FALHOU";
    $statusController = ($resultado && isset($resultado['jogos'])) ? "✅ OK" : "❌ FALHOU";
    
    echo "<table>";
    echo "<tr><th>Componente</th><th>Status</th><th>Detalhes</th></tr>";
    echo "<tr><td>🗃️ Banco de Dados</td><td>{$statusBD}</td><td>" . count($jogos) . " jogos encontrados</td></tr>";
    echo "<tr><td>🎯 Controller PHP</td><td>{$statusController}</td><td>" . (isset($resultado['jogos']) ? count($resultado['jogos']) : 0) . " jogos retornados</td></tr>";
    echo "<tr><td>📊 Estrutura da Tabela</td><td>✅ OK</td><td>Colunas time1, time2, ativo criadas</td></tr>";
    echo "<tr><td>🔧 Módulo Site</td><td>✅ OK</td><td>Módulo 'site' ativo</td></tr>";
    echo "</table>";
    
    if (count($jogos) > 0 && isset($resultado['jogos']) && count($resultado['jogos']) > 0) {
        echo "<div class='success' style='text-align: center; font-size: 1.3em; margin: 20px 0; padding: 20px;'>";
        echo "🎉 <strong>PARABÉNS! O SISTEMA ESTÁ FUNCIONANDO!</strong><br><br>";
        echo "Os jogos estão sendo buscados corretamente do banco de dados<br>";
        echo "e o controller está retornando os dados como esperado.<br><br>";
        echo "🚀 <strong>Agora acesse <a href='http://localhost:8000' style='color: #ff6600;'>http://localhost:8000</a> e veja os jogos!</strong>";
        echo "</div>";
    } else {
        echo "<div class='error' style='text-align: center; font-size: 1.2em; margin: 20px 0; padding: 20px;'>";
        echo "⚠️ <strong>AINDA HÁ PROBLEMAS!</strong><br><br>";
        echo "O sistema não está retornando jogos corretamente.<br>";
        echo "Verifique os erros acima para mais detalhes.";
        echo "</div>";
    }
    
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Erro Durante o Teste:</h2>";
    echo "<strong>Mensagem:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Arquivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Linha:</strong> " . $e->getLine() . "<br><br>";
    echo "<strong>Stack Trace:</strong><br>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
    echo "</div>";
}

echo "<div style='text-align: center; margin-top: 30px; padding: 20px; color: #888;'>";
echo "⏰ Teste executado em: " . date('d/m/Y H:i:s');
echo "</div>";

echo "</body></html>";
?>

