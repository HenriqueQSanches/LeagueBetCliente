<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'>";
echo "<title>🔧 Corrigir Tabela - LeagueBet</title>";
echo "<style>
body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 100%); color: #fff; padding: 20px; line-height: 1.6; }
h1 { color: #ff6600; text-align: center; font-size: 2.5em; }
.section { background: rgba(255,102,0,0.1); border: 2px solid #ff6600; padding: 20px; margin: 20px 0; border-radius: 10px; }
.success { background: rgba(0,255,0,0.1); border-left: 4px solid #00ff00; padding: 10px; margin: 10px 0; border-radius: 5px; }
.error { background: rgba(255,0,0,0.1); border-left: 4px solid #ff0000; padding: 10px; margin: 10px 0; border-radius: 5px; }
.info { background: rgba(0,123,255,0.1); border-left: 4px solid #007bff; padding: 10px; margin: 10px 0; border-radius: 5px; }
pre { background: #000; padding: 15px; border-radius: 5px; overflow-x: auto; border: 1px solid #ff6600; }
</style></head><body>";

echo "<h1>🔧 CORRIGIR ESTRUTURA DA TABELA sis_jogos</h1>";

try {
    require_once 'conexao.php';
    
    echo "<div class='section'>";
    echo "<h2>🛠️ Etapa 1: Adicionar Coluna 'ativo'</h2>";
    
    try {
        $conexao->exec("ALTER TABLE sis_jogos ADD COLUMN ativo VARCHAR(1) DEFAULT '1'");
        echo "<div class='success'>✅ Coluna 'ativo' adicionada com sucesso!</div>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "<div class='info'>ℹ️ Coluna 'ativo' já existe!</div>";
        } else {
            throw $e;
        }
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>🛠️ Etapa 2: Adicionar Coluna 'time1'</h2>";
    
    try {
        $conexao->exec("ALTER TABLE sis_jogos ADD COLUMN time1 VARCHAR(255)");
        echo "<div class='success'>✅ Coluna 'time1' adicionada com sucesso!</div>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "<div class='info'>ℹ️ Coluna 'time1' já existe!</div>";
        } else {
            throw $e;
        }
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>🛠️ Etapa 3: Adicionar Coluna 'time2'</h2>";
    
    try {
        $conexao->exec("ALTER TABLE sis_jogos ADD COLUMN time2 VARCHAR(255)");
        echo "<div class='success'>✅ Coluna 'time2' adicionada com sucesso!</div>";
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate column') !== false) {
            echo "<div class='info'>ℹ️ Coluna 'time2' já existe!</div>";
        } else {
            throw $e;
        }
    }
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>🔄 Etapa 4: Migrar Dados de timecasa para time1</h2>";
    
    $result = $conexao->exec("UPDATE sis_jogos SET time1 = timecasa WHERE time1 IS NULL OR time1 = ''");
    echo "<div class='success'>✅ {$result} registros atualizados em 'time1'!</div>";
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>🔄 Etapa 5: Migrar Dados de timefora para time2</h2>";
    
    $result = $conexao->exec("UPDATE sis_jogos SET time2 = timefora WHERE time2 IS NULL OR time2 = ''");
    echo "<div class='success'>✅ {$result} registros atualizados em 'time2'!</div>";
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>🔄 Etapa 6: Ativar Todos os Jogos</h2>";
    
    $result = $conexao->exec("UPDATE sis_jogos SET ativo = '1' WHERE ativo IS NULL OR ativo = ''");
    echo "<div class='success'>✅ {$result} registros ativados!</div>";
    echo "</div>";
    
    echo "<div class='section'>";
    echo "<h2>✅ Etapa 7: Verificação Final</h2>";
    
    // Contar jogos disponíveis
    $total = $conexao->query("SELECT COUNT(*) FROM sis_jogos")->fetchColumn();
    echo "<div class='info'>🎮 Total de jogos: <strong>{$total}</strong></div>";
    
    // Jogos ativos
    $ativos = $conexao->query("SELECT COUNT(*) FROM sis_jogos WHERE ativo = '1'")->fetchColumn();
    echo "<div class='info'>✅ Jogos ativos: <strong>{$ativos}</strong></div>";
    
    // Jogos futuros
    $futuros = $conexao->query("
        SELECT COUNT(*) 
        FROM sis_jogos 
        WHERE ativo = '1' 
        AND status = 1
        AND data >= CURDATE()
    ")->fetchColumn();
    echo "<div class='info'>🔮 Jogos futuros disponíveis: <strong>{$futuros}</strong></div>";
    
    // Mostrar um exemplo
    echo "<h3 style='color: #ff6600;'>📋 Exemplo de Jogo Corrigido:</h3>";
    $stmt = $conexao->query("
        SELECT id, data, hora, time1, time2, timecasa, timefora, ativo, status, campeonato
        FROM sis_jogos 
        WHERE data >= CURDATE()
        LIMIT 1
    ");
    $jogo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($jogo) {
        echo "<pre>" . print_r($jogo, true) . "</pre>";
    }
    
    echo "</div>";
    
    echo "<div class='section' style='text-align: center; background: rgba(0,255,0,0.2); border-color: #00ff00;'>";
    echo "<h2 style='color: #00ff00;'>🎉 CORREÇÕES APLICADAS COM SUCESSO!</h2>";
    echo "<div class='success' style='font-size: 1.2em; margin: 20px 0;'>";
    echo "✅ A tabela sis_jogos foi corrigida!<br>";
    echo "✅ As colunas necessárias foram adicionadas!<br>";
    echo "✅ Os dados foram migrados corretamente!<br><br>";
    echo "🚀 <strong>Agora os jogos devem aparecer no site!</strong>";
    echo "</div>";
    echo "<div class='info'>";
    echo "📝 <strong>Próximos Passos:</strong><br><br>";
    echo "1. Acesse: <a href='http://localhost:8000' style='color: #ff6600; font-weight: bold;'>http://localhost:8000</a><br>";
    echo "2. Limpe o cache do navegador (Ctrl + F5)<br>";
    echo "3. Verifique se os jogos aparecem!<br><br>";
    echo "Se ainda não aparecerem, execute: <a href='teste-jogos-completo.php' style='color: #ff6600;'>teste-jogos-completo.php</a>";
    echo "</div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Erro Durante a Correção:</h2>";
    echo "<strong>Mensagem:</strong> " . $e->getMessage() . "<br>";
    echo "<strong>Arquivo:</strong> " . $e->getFile() . "<br>";
    echo "<strong>Linha:</strong> " . $e->getLine();
    echo "</div>";
}

echo "</body></html>";
?>

