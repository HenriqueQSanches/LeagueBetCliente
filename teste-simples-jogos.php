<?php
// Mostrar TODOS os erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>TESTE SIMPLES - JOGOS</h1>";
echo "<hr>";

echo "<h2>1. Testando Conexão com Banco</h2>";
try {
    require_once 'conexao.php';
    echo "✅ Conexão OK<br>";
    
    // Contar jogos
    $total = $conexao->query("SELECT COUNT(*) FROM sis_jogos")->fetchColumn();
    echo "✅ Total de jogos: {$total}<br>";
    
    // Buscar jogos futuros COM AS NOVAS COLUNAS
    echo "<h2>2. Buscando Jogos Futuros</h2>";
    $sql = "
        SELECT 
            j.id,
            j.data,
            j.hora,
            j.time1,
            j.time2,
            j.ativo,
            j.status,
            j.campeonato
        FROM sis_jogos j
        WHERE j.ativo = '1' 
        AND j.status = 1
        AND j.data >= CURDATE()
        LIMIT 5
    ";
    
    echo "Query: <pre>{$sql}</pre>";
    
    $stmt = $conexao->query($sql);
    $jogos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✅ Jogos encontrados: " . count($jogos) . "<br><br>";
    
    if (count($jogos) > 0) {
        echo "<h3>Primeiros 5 jogos:</h3>";
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Data</th><th>Hora</th><th>Time1 ID</th><th>Time2 ID</th><th>Ativo</th><th>Status</th></tr>";
        foreach ($jogos as $jogo) {
            echo "<tr>";
            echo "<td>{$jogo['id']}</td>";
            echo "<td>{$jogo['data']}</td>";
            echo "<td>{$jogo['hora']}</td>";
            echo "<td>{$jogo['time1']}</td>";
            echo "<td>{$jogo['time2']}</td>";
            echo "<td>{$jogo['ativo']}</td>";
            echo "<td>{$jogo['status']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Agora buscar os NOMES dos times
        echo "<h2>3. Buscando Nomes dos Times</h2>";
        $sqlComNomes = "
            SELECT 
                j.id,
                j.data,
                j.hora,
                t1.nome as time1_nome,
                t2.nome as time2_nome,
                c.nome as campeonato_nome
            FROM sis_jogos j
            LEFT JOIN sis_times t1 ON j.time1 = t1.id
            LEFT JOIN sis_times t2 ON j.time2 = t2.id
            LEFT JOIN sis_campeonatos c ON j.campeonato = c.id
            WHERE j.ativo = '1' 
            AND j.status = 1
            AND j.data >= CURDATE()
            LIMIT 5
        ";
        
        $stmt2 = $conexao->query($sqlComNomes);
        $jogosComNomes = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>Data/Hora</th><th>Jogo</th><th>Campeonato</th></tr>";
        foreach ($jogosComNomes as $jogo) {
            echo "<tr>";
            echo "<td>{$jogo['data']} {$jogo['hora']}</td>";
            echo "<td><strong>{$jogo['time1_nome']}</strong> x <strong>{$jogo['time2_nome']}</strong></td>";
            echo "<td>{$jogo['campeonato_nome']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h2 style='color: green;'>✅ SUCESSO! Jogos estão sendo encontrados!</h2>";
        
    } else {
        echo "<h2 style='color: red;'>❌ Nenhum jogo futuro encontrado!</h2>";
    }
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ ERRO:</h2>";
    echo "<p><strong>Mensagem:</strong> " . $e->getMessage() . "</p>";
    echo "<p><strong>Arquivo:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Linha:</strong> " . $e->getLine() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p>Teste concluído em: " . date('d/m/Y H:i:s') . "</p>";
?>

