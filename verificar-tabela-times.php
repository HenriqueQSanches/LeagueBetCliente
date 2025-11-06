<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>🔍 VERIFICAR ESTRUTURA DAS TABELAS</h1><hr>";

try {
    require_once 'conexao.php';
    
    // ========================================
    // TABELA sis_times
    // ========================================
    echo "<h2>📊 Estrutura da Tabela: sis_times</h2>";
    
    $stmt = $conexao->query("DESCRIBE sis_times");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background: #ff6600;'><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td><strong>{$coluna['Field']}</strong></td>";
        echo "<td>{$coluna['Type']}</td>";
        echo "<td>{$coluna['Null']}</td>";
        echo "<td>{$coluna['Key']}</td>";
        echo "<td>{$coluna['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Mostrar exemplo de dados
    echo "<h3>📋 Exemplo de Registro:</h3>";
    $exemplo = $conexao->query("SELECT * FROM sis_times LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if ($exemplo) {
        echo "<pre>" . print_r($exemplo, true) . "</pre>";
    }
    
    echo "<hr>";
    
    // ========================================
    // TABELA sis_campeonatos
    // ========================================
    echo "<h2>📊 Estrutura da Tabela: sis_campeonatos</h2>";
    
    $stmt = $conexao->query("DESCRIBE sis_campeonatos");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background: #ff6600;'><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th></tr>";
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td><strong>{$coluna['Field']}</strong></td>";
        echo "<td>{$coluna['Type']}</td>";
        echo "<td>{$coluna['Null']}</td>";
        echo "<td>{$coluna['Key']}</td>";
        echo "<td>{$coluna['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Mostrar exemplo de dados
    echo "<h3>📋 Exemplo de Registro:</h3>";
    $exemplo = $conexao->query("SELECT * FROM sis_campeonatos LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    if ($exemplo) {
        echo "<pre>" . print_r($exemplo, true) . "</pre>";
    }
    
    echo "<hr>";
    
    // ========================================
    // BUSCAR UM JOGO COMPLETO
    // ========================================
    echo "<h2>🎮 Teste: Buscar Jogo com Times e Campeonato</h2>";
    
    // Primeiro, pegar um jogo
    $jogo = $conexao->query("SELECT * FROM sis_jogos WHERE ativo = '1' AND status = 1 LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    
    if ($jogo) {
        echo "<h3>Jogo encontrado (ID: {$jogo['id']}):</h3>";
        echo "Time1 ID: {$jogo['time1']}<br>";
        echo "Time2 ID: {$jogo['time2']}<br>";
        echo "Campeonato ID: {$jogo['campeonato']}<br><br>";
        
        // Buscar time1
        echo "<h4>Buscando Time 1 (ID: {$jogo['time1']}):</h4>";
        $time1 = $conexao->query("SELECT * FROM sis_times WHERE id = {$jogo['time1']}")->fetch(PDO::FETCH_ASSOC);
        if ($time1) {
            echo "<pre>" . print_r($time1, true) . "</pre>";
        } else {
            echo "❌ Time 1 não encontrado!<br>";
        }
        
        // Buscar time2
        echo "<h4>Buscando Time 2 (ID: {$jogo['time2']}):</h4>";
        $time2 = $conexao->query("SELECT * FROM sis_times WHERE id = {$jogo['time2']}")->fetch(PDO::FETCH_ASSOC);
        if ($time2) {
            echo "<pre>" . print_r($time2, true) . "</pre>";
        } else {
            echo "❌ Time 2 não encontrado!<br>";
        }
        
        // Buscar campeonato
        echo "<h4>Buscando Campeonato (ID: {$jogo['campeonato']}):</h4>";
        $camp = $conexao->query("SELECT * FROM sis_campeonatos WHERE id = {$jogo['campeonato']}")->fetch(PDO::FETCH_ASSOC);
        if ($camp) {
            echo "<pre>" . print_r($camp, true) . "</pre>";
        } else {
            echo "❌ Campeonato não encontrado!<br>";
        }
    }
    
    echo "<hr>";
    echo "<h2 style='color: green;'>✅ Verificação Concluída!</h2>";
    echo "<p>Agora sabemos quais colunas existem nas tabelas.</p>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ ERRO:</h2>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
}
?>

