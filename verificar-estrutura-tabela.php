<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>";
echo "<html><head><meta charset='UTF-8'>";
echo "<title>Verificar Estrutura - LeagueBet</title>";
echo "<style>
body { font-family: monospace; background: #1a1a1a; color: #fff; padding: 20px; }
h1 { color: #ff6600; }
.section { background: rgba(255,102,0,0.1); border: 2px solid #ff6600; padding: 20px; margin: 20px 0; border-radius: 10px; }
table { width: 100%; border-collapse: collapse; margin: 10px 0; }
th, td { border: 1px solid #ff6600; padding: 10px; text-align: left; }
th { background: #ff6600; color: #000; }
pre { background: #000; padding: 15px; border-radius: 5px; overflow-x: auto; }
.success { color: #00ff00; }
.error { color: #ff0000; }
.warning { color: #ffa500; }
</style></head><body>";

echo "<h1>🔍 VERIFICAR ESTRUTURA DA TABELA sis_jogos</h1>";

try {
    require_once 'conexao.php';
    
    echo "<div class='section'>";
    echo "<h2>📊 Estrutura Atual da Tabela sis_jogos:</h2>";
    
    // Mostrar estrutura da tabela
    $stmt = $conexao->query("DESCRIBE sis_jogos");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
    foreach ($colunas as $coluna) {
        echo "<tr>";
        echo "<td><strong>{$coluna['Field']}</strong></td>";
        echo "<td>{$coluna['Type']}</td>";
        echo "<td>{$coluna['Null']}</td>";
        echo "<td>{$coluna['Key']}</td>";
        echo "<td>{$coluna['Default']}</td>";
        echo "<td>{$coluna['Extra']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar se existem as colunas necessárias
    $colunasNecessarias = ['ativo', 'status', 'data', 'hora', 'time1', 'time2', 'campeonato'];
    $colunasFaltantes = [];
    $colunasExistentes = array_column($colunas, 'Field');
    
    echo "<div class='section'>";
    echo "<h2>🔍 Verificação de Colunas Necessárias:</h2>";
    
    foreach ($colunasNecessarias as $col) {
        if (in_array($col, $colunasExistentes)) {
            echo "<div class='success'>✅ Coluna '{$col}' existe!</div>";
        } else {
            echo "<div class='error'>❌ Coluna '{$col}' NÃO EXISTE!</div>";
            $colunasFaltantes[] = $col;
        }
    }
    echo "</div>";
    
    // Mostrar exemplo de dados
    echo "<div class='section'>";
    echo "<h2>📋 Primeiros 5 Registros da Tabela:</h2>";
    $stmt = $conexao->query("SELECT * FROM sis_jogos LIMIT 5");
    $jogos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($jogos) > 0) {
        echo "<pre>" . print_r($jogos[0], true) . "</pre>";
    } else {
        echo "<div class='warning'>⚠️ Nenhum jogo encontrado na tabela!</div>";
    }
    echo "</div>";
    
    // Sugestão de SQL para adicionar colunas faltantes
    if (count($colunasFaltantes) > 0) {
        echo "<div class='section'>";
        echo "<h2>🛠️ SQL para Corrigir Colunas Faltantes:</h2>";
        echo "<pre>";
        foreach ($colunasFaltantes as $col) {
            if ($col == 'ativo') {
                echo "ALTER TABLE sis_jogos ADD COLUMN ativo VARCHAR(1) DEFAULT '1';\n";
            } elseif ($col == 'status') {
                echo "ALTER TABLE sis_jogos ADD COLUMN status VARCHAR(1) DEFAULT 'A';\n";
            } else {
                echo "ALTER TABLE sis_jogos ADD COLUMN {$col} VARCHAR(255);\n";
            }
        }
        echo "</pre>";
        echo "</div>";
    }
    
    // Contar total de jogos
    $total = $conexao->query("SELECT COUNT(*) FROM sis_jogos")->fetchColumn();
    echo "<div class='section'>";
    echo "<h2>📊 Estatísticas:</h2>";
    echo "<div class='success'>🎮 Total de jogos na tabela: <strong>{$total}</strong></div>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Erro: " . $e->getMessage() . "</div>";
}

echo "</body></html>";
?>

