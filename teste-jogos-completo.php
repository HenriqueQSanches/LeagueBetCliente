<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔍 Diagnóstico Completo - LeagueBet</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%);
            color: #fff;
            padding: 20px;
            line-height: 1.6;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        h1 {
            text-align: center;
            color: #ff6600;
            margin-bottom: 30px;
            font-size: 2.5em;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .section {
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid #ff6600;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
        }
        .section h2 {
            color: #ff6600;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ff6600;
            font-size: 1.5em;
        }
        .success {
            background: rgba(0, 255, 0, 0.1);
            border-left: 4px solid #00ff00;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .error {
            background: rgba(255, 0, 0, 0.1);
            border-left: 4px solid #ff0000;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .warning {
            background: rgba(255, 165, 0, 0.1);
            border-left: 4px solid #ffa500;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .info {
            background: rgba(0, 123, 255, 0.1);
            border-left: 4px solid #007bff;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        pre {
            background: #000;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            margin: 10px 0;
            border: 1px solid #ff6600;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            background: rgba(0, 0, 0, 0.3);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ff6600;
        }
        th {
            background: #ff6600;
            color: #000;
            font-weight: bold;
        }
        tr:hover {
            background: rgba(255, 102, 0, 0.1);
        }
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9em;
            font-weight: bold;
            margin: 5px;
        }
        .badge-success { background: #28a745; color: #fff; }
        .badge-danger { background: #dc3545; color: #fff; }
        .badge-warning { background: #ffc107; color: #000; }
        .badge-info { background: #17a2b8; color: #fff; }
        .code-block {
            background: #1e1e1e;
            border: 1px solid #ff6600;
            border-radius: 5px;
            padding: 15px;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
            overflow-x: auto;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin: 15px 0;
        }
        .card {
            background: rgba(255, 102, 0, 0.1);
            border: 2px solid #ff6600;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
        }
        .card h3 {
            color: #ff6600;
            margin-bottom: 10px;
        }
        .card .value {
            font-size: 2em;
            font-weight: bold;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 DIAGNÓSTICO COMPLETO - LEAGUEBET</h1>
        
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '512M');
ini_set('max_execution_time', '300');

echo "<div class='info'>⏰ Iniciado em: " . date('d/m/Y H:i:s') . "</div>";

// ========================================
// 1. VERIFICAR BANCO DE DADOS
// ========================================
echo "<div class='section'>";
echo "<h2>📊 1. VERIFICAÇÃO DO BANCO DE DADOS</h2>";

try {
    require_once 'conexao.php';
    echo "<div class='success'>✅ Conexão com banco de dados estabelecida!</div>";
    
    // Contar total de jogos
    $totalJogos = $conexao->query("SELECT COUNT(*) FROM sis_jogos")->fetchColumn();
    echo "<div class='info'>🎮 Total de jogos no banco: <strong>{$totalJogos}</strong></div>";
    
    // Jogos disponíveis para apostar (futuros)
    $jogosDisponiveis = $conexao->query("
        SELECT COUNT(*) 
        FROM sis_jogos 
        WHERE ativo = '1' 
        AND status = 'A' 
        AND STR_TO_DATE(CONCAT(data, ' ', hora), '%d/%m/%Y %H:%i') > NOW()
    ")->fetchColumn();
    echo "<div class='info'>✅ Jogos disponíveis para apostar: <strong>{$jogosDisponiveis}</strong></div>";
    
    // Jogos de hoje
    $jogosHoje = $conexao->query("
        SELECT COUNT(*) 
        FROM sis_jogos 
        WHERE ativo = '1' 
        AND status = 'A' 
        AND data = DATE_FORMAT(NOW(), '%d/%m/%Y')
    ")->fetchColumn();
    echo "<div class='info'>📅 Jogos hoje: <strong>{$jogosHoje}</strong></div>";
    
    // Estatísticas gerais
    echo "<div class='grid'>";
    
    $stats = [
        ['Total de Jogos', $totalJogos, 'info'],
        ['Disponíveis', $jogosDisponiveis, 'success'],
        ['Hoje', $jogosHoje, 'warning'],
        ['Times', $conexao->query("SELECT COUNT(*) FROM sis_times")->fetchColumn(), 'info'],
        ['Campeonatos', $conexao->query("SELECT COUNT(*) FROM sis_campeonatos")->fetchColumn(), 'info'],
    ];
    
    foreach ($stats as $stat) {
        echo "<div class='card'>";
        echo "<h3>{$stat[0]}</h3>";
        echo "<div class='value'>{$stat[1]}</div>";
        echo "</div>";
    }
    
    echo "</div>";
    
    // Listar próximos 10 jogos disponíveis
    echo "<h3 style='color: #ff6600; margin-top: 20px;'>🔮 Próximos 10 Jogos Disponíveis:</h3>";
    $stmt = $conexao->query("
        SELECT 
            j.id,
            j.data,
            j.hora,
            t1.nome as time1,
            t2.nome as time2,
            c.nome as campeonato,
            j.ativo,
            j.status,
            STR_TO_DATE(CONCAT(j.data, ' ', j.hora), '%d/%m/%Y %H:%i') as data_hora_completa
        FROM sis_jogos j
        LEFT JOIN sis_times t1 ON j.time1 = t1.id
        LEFT JOIN sis_times t2 ON j.time2 = t2.id
        LEFT JOIN sis_campeonatos c ON j.campeonato = c.id
        WHERE j.ativo = '1' 
        AND j.status = 'A'
        AND STR_TO_DATE(CONCAT(j.data, ' ', j.hora), '%d/%m/%Y %H:%i') > NOW()
        ORDER BY data_hora_completa ASC
        LIMIT 10
    ");
    
    $jogos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($jogos) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Data/Hora</th><th>Jogo</th><th>Campeonato</th><th>Status</th></tr>";
        foreach ($jogos as $jogo) {
            echo "<tr>";
            echo "<td>{$jogo['id']}</td>";
            echo "<td>{$jogo['data']} {$jogo['hora']}</td>";
            echo "<td>{$jogo['time1']} x {$jogo['time2']}</td>";
            echo "<td>{$jogo['campeonato']}</td>";
            echo "<td><span class='badge badge-success'>Ativo: {$jogo['ativo']} | Status: {$jogo['status']}</span></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='warning'>⚠️ Nenhum jogo disponível encontrado!</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro ao conectar ao banco: " . $e->getMessage() . "</div>";
}

echo "</div>";

// ========================================
// 2. VERIFICAR CONFIGURAÇÃO DO SISTEMA
// ========================================
echo "<div class='section'>";
echo "<h2>⚙️ 2. CONFIGURAÇÃO DO SISTEMA</h2>";

try {
    require_once 'inc.config.php';
    echo "<div class='success'>✅ Arquivo inc.config.php carregado!</div>";
    
    echo "<div class='info'><strong>Módulo Site Ativo:</strong> " . 
         (isset($config['modules']['site']) && $config['modules']['site'] !== null ? 
         "✅ SIM" : "❌ NÃO") . "</div>";
    
    if (isset($config['modules']['site']) && $config['modules']['site'] !== null) {
        echo "<div class='code-block'>";
        echo "<strong>Configuração do módulo 'site':</strong><br>";
        echo "Path: " . ($config['modules']['site']['path'] ?? 'N/A') . "<br>";
        echo "Class: " . ($config['modules']['site']['class'] ?? 'N/A');
        echo "</div>";
    } else {
        echo "<div class='error'>❌ PROBLEMA ENCONTRADO: Módulo 'site' está NULL!<br>";
        echo "Isso impede que o layout principal seja carregado.</div>";
    }
    
    echo "<div class='info'><strong>URL do Sistema:</strong> " . ($config['uri'] ?? 'N/A') . "</div>";
    echo "<div class='info'><strong>Título:</strong> " . ($config['title'] ?? 'N/A') . "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro ao carregar configuração: " . $e->getMessage() . "</div>";
}

echo "</div>";

// ========================================
// 3. TESTAR CONTROLLER DE APOSTAS
// ========================================
echo "<div class='section'>";
echo "<h2>🎯 3. TESTE DO CONTROLLER DE APOSTAS</h2>";

try {
    require_once 'app/modules/website/controllers/apostarController.php';
    
    echo "<div class='info'>📝 Tentando instanciar apostarController...</div>";
    
    $controller = new \app\modules\website\controllers\apostarController();
    echo "<div class='success'>✅ Controller instanciado com sucesso!</div>";
    
    echo "<div class='info'>📝 Executando jogosAction()...</div>";
    
    ob_start();
    $resultado = $controller->jogosAction();
    $output = ob_get_clean();
    
    if ($output) {
        echo "<div class='warning'>⚠️ O controller produziu output direto:<br><pre>" . htmlspecialchars($output) . "</pre></div>";
    }
    
    if ($resultado) {
        echo "<div class='success'>✅ jogosAction() retornou dados!</div>";
        
        // Se for um array, analisar
        if (is_array($resultado)) {
            echo "<div class='info'>📊 Tipo de retorno: Array</div>";
            echo "<div class='info'>🔢 Quantidade de elementos: " . count($resultado) . "</div>";
            
            if (count($resultado) > 0) {
                echo "<h3 style='color: #ff6600;'>📋 Estrutura dos Dados Retornados:</h3>";
                echo "<pre>" . print_r(array_slice($resultado, 0, 2), true) . "</pre>";
            } else {
                echo "<div class='warning'>⚠️ O array está vazio!</div>";
            }
        } 
        // Se for JSON
        elseif (is_string($resultado)) {
            echo "<div class='info'>📊 Tipo de retorno: String (possível JSON)</div>";
            $decoded = json_decode($resultado, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "<div class='success'>✅ É um JSON válido!</div>";
                echo "<div class='info'>🔢 Quantidade de jogos: " . count($decoded) . "</div>";
                
                if (count($decoded) > 0) {
                    echo "<h3 style='color: #ff6600;'>📋 Primeiros 2 Jogos:</h3>";
                    echo "<pre>" . json_encode(array_slice($decoded, 0, 2), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
                } else {
                    echo "<div class='warning'>⚠️ O JSON está vazio!</div>";
                }
            } else {
                echo "<div class='error'>❌ Não é um JSON válido!</div>";
                echo "<pre>" . htmlspecialchars(substr($resultado, 0, 500)) . "</pre>";
            }
        } else {
            echo "<div class='warning'>⚠️ Tipo de retorno desconhecido: " . gettype($resultado) . "</div>";
        }
    } else {
        echo "<div class='error'>❌ jogosAction() não retornou nada (NULL ou FALSE)!</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro ao executar controller:<br>" . $e->getMessage() . "</div>";
    echo "<div class='code-block'><strong>Stack Trace:</strong><br>" . nl2br(htmlspecialchars($e->getTraceAsString())) . "</div>";
}

echo "</div>";

// ========================================
// 4. VERIFICAR MODELS
// ========================================
echo "<div class='section'>";
echo "<h2>🏗️ 4. VERIFICAÇÃO DOS MODELS</h2>";

try {
    require_once 'app/models/DadosModel.php';
    echo "<div class='success'>✅ DadosModel carregado!</div>";
    
    echo "<div class='info'>📝 Testando DadosModel::eSporteCampeonatos()...</div>";
    $campeonatos = \app\models\DadosModel::eSporteCampeonatos();
    echo "<div class='info'>🏆 Campeonatos retornados: " . count($campeonatos) . "</div>";
    
    if (count($campeonatos) > 0) {
        echo "<div class='success'>✅ Model está retornando dados!</div>";
        echo "<h3 style='color: #ff6600;'>📋 Primeiros 5 Campeonatos:</h3>";
        echo "<pre>" . print_r(array_slice($campeonatos, 0, 5), true) . "</pre>";
    } else {
        echo "<div class='warning'>⚠️ Model não retornou campeonatos!</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro ao testar models: " . $e->getMessage() . "</div>";
}

echo "</div>";

// ========================================
// 5. VERIFICAR ROTEAMENTO
// ========================================
echo "<div class='section'>";
echo "<h2>🛣️ 5. VERIFICAÇÃO DE ROTAS</h2>";

echo "<div class='info'>🔍 Analisando sistema de rotas...</div>";

try {
    require_once 'app/APP.php';
    echo "<div class='success'>✅ APP.php carregado!</div>";
    
    echo "<div class='code-block'>";
    echo "<strong>URLs para Testar:</strong><br><br>";
    echo "🏠 Site Principal: <a href='http://localhost:8000' target='_blank' style='color: #ff6600;'>http://localhost:8000</a><br>";
    echo "🎮 API de Jogos: <a href='http://localhost:8000/apostar/jogos' target='_blank' style='color: #ff6600;'>http://localhost:8000/apostar/jogos</a><br>";
    echo "📊 Status API: <a href='http://localhost:8000/status-api.php' target='_blank' style='color: #ff6600;'>http://localhost:8000/status-api.php</a><br>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro ao verificar rotas: " . $e->getMessage() . "</div>";
}

echo "</div>";

// ========================================
// 6. VERIFICAR CACHE
// ========================================
echo "<div class='section'>";
echo "<h2>🗑️ 6. VERIFICAÇÃO DE CACHE</h2>";

$cacheDir = __DIR__ . '/_temp/cache';
if (is_dir($cacheDir)) {
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($cacheDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    
    $count = 0;
    foreach ($files as $file) {
        if ($file->isFile()) {
            $count++;
        }
    }
    
    echo "<div class='info'>📁 Diretório de cache: {$cacheDir}</div>";
    echo "<div class='info'>📄 Arquivos em cache: {$count}</div>";
    
    if ($count > 0) {
        echo "<div class='warning'>⚠️ Existem {$count} arquivos em cache.<br>";
        echo "Limpe o cache com: <code>Remove-Item -Path \"{$cacheDir}\\*\" -Recurse -Force</code></div>";
    } else {
        echo "<div class='success'>✅ Cache limpo!</div>";
    }
} else {
    echo "<div class='warning'>⚠️ Diretório de cache não encontrado!</div>";
}

echo "</div>";

// ========================================
// 7. RECOMENDAÇÕES
// ========================================
echo "<div class='section'>";
echo "<h2>💡 7. RECOMENDAÇÕES E PRÓXIMOS PASSOS</h2>";

$recomendacoes = [];

if ($jogosDisponiveis == 0) {
    $recomendacoes[] = ['danger', '❌ CRÍTICO', 'Não há jogos disponíveis! Execute: <code>php jogos.php</code> para importar novos jogos.'];
}

if (!isset($config['modules']['site']) || $config['modules']['site'] === null) {
    $recomendacoes[] = ['danger', '❌ CRÍTICO', 'Módulo site está NULL! Configure em inc.config.php'];
}

if ($count > 50) {
    $recomendacoes[] = ['warning', '⚠️ ATENÇÃO', 'Cache Twig muito cheio. Recomendado limpar.'];
}

if (count($recomendacoes) == 0) {
    echo "<div class='success'>✅ Sistema aparentemente configurado corretamente!</div>";
    echo "<div class='info'>🔍 Se ainda assim os jogos não aparecem, o problema pode estar no frontend (JavaScript/Vue.js).</div>";
    echo "<div class='code-block'>";
    echo "<strong>Próximos passos sugeridos:</strong><br><br>";
    echo "1. Abra o DevTools do navegador (F12)<br>";
    echo "2. Vá na aba 'Network' (Rede)<br>";
    echo "3. Acesse http://localhost:8000<br>";
    echo "4. Procure pela requisição para '/apostar/jogos'<br>";
    echo "5. Verifique se a resposta contém os dados dos jogos<br>";
    echo "6. Vá na aba 'Console' e veja se há erros JavaScript<br>";
    echo "</div>";
} else {
    echo "<div class='error'><strong>❌ PROBLEMAS ENCONTRADOS:</strong></div>";
    foreach ($recomendacoes as $rec) {
        echo "<div class='" . ($rec[0] == 'danger' ? 'error' : 'warning') . "'>";
        echo "<strong>{$rec[1]}:</strong> {$rec[2]}";
        echo "</div>";
    }
}

echo "</div>";

echo "<div class='info' style='text-align: center; margin-top: 20px;'>";
echo "⏰ Diagnóstico concluído em: " . date('d/m/Y H:i:s');
echo "</div>";

?>

    </div>
</body>
</html>

