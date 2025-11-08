<?php
/**
 * Verifica estrutura do banco de dados
 */

require_once __DIR__ . '/inc.config.php';

use app\core\crud\Conn;

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Estrutura do Banco</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            background: #1a1a1a;
            color: #4caf50;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: #000;
            border: 2px solid #4caf50;
            border-radius: 10px;
            padding: 30px;
        }
        h1 {
            color: #ff9800;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            background: #0a0a0a;
        }
        th, td {
            border: 1px solid #333;
            padding: 10px;
            text-align: left;
        }
        th {
            background: #ff9800;
            color: #000;
        }
        .success {
            color: #4caf50;
        }
        .error {
            color: #f44336;
        }
        .info {
            color: #2196f3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Estrutura do Banco de Dados</h1>

        <?php
        try {
            $pdo = Conn::getConn();
            $dbName = Conn::getDataBaseName();
            
            echo "<p class='info'>📊 Banco de dados: <strong>{$dbName}</strong></p>";

            // Lista todas as tabelas
            echo "<h2>📋 Tabelas Existentes:</h2>";
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if ($tables) {
                echo "<table>";
                echo "<tr><th>#</th><th>Nome da Tabela</th><th>Registros</th><th>Ações</th></tr>";
                $count = 1;
                
                foreach ($tables as $tableName) {
                    // Conta registros
                    $countStmt = $pdo->query("SELECT COUNT(*) FROM `{$tableName}`");
                    $totalRecords = $countStmt->fetchColumn();
                    
                    echo "<tr>";
                    echo "<td>{$count}</td>";
                    echo "<td class='success'>{$tableName}</td>";
                    echo "<td>{$totalRecords}</td>";
                    echo "<td><a href='?describe={$tableName}' style='color: #ff9800;'>Ver Estrutura</a></td>";
                    echo "</tr>";
                    $count++;
                }
                echo "</table>";
                
                // Se foi solicitado ver estrutura de uma tabela
                if (isset($_GET['describe'])) {
                    $tableName = $_GET['describe'];
                    echo "<h2>📊 Estrutura da Tabela: {$tableName}</h2>";
                    
                    $stmt = $pdo->query("DESCRIBE `{$tableName}`");
                    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if ($columns) {
                        echo "<table>";
                        echo "<tr><th>Campo</th><th>Tipo</th><th>Nulo</th><th>Chave</th><th>Padrão</th><th>Extra</th></tr>";
                        foreach ($columns as $column) {
                            echo "<tr>";
                            echo "<td class='success'>{$column['Field']}</td>";
                            echo "<td>{$column['Type']}</td>";
                            echo "<td>{$column['Null']}</td>";
                            echo "<td>{$column['Key']}</td>";
                            echo "<td>" . ($column['Default'] ?? 'NULL') . "</td>";
                            echo "<td>{$column['Extra']}</td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                        
                        // Mostra alguns registros de exemplo
                        echo "<h3>📄 Primeiros 5 Registros:</h3>";
                        $stmt = $pdo->query("SELECT * FROM `{$tableName}` LIMIT 5");
                        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        
                        if ($records) {
                            echo "<div style='overflow-x: auto;'>";
                            echo "<table>";
                            echo "<tr>";
                            foreach (array_keys($records[0]) as $col) {
                                echo "<th>{$col}</th>";
                            }
                            echo "</tr>";
                            foreach ($records as $record) {
                                echo "<tr>";
                                foreach ($record as $value) {
                                    $displayValue = $value;
                                    if (strlen($value) > 50) {
                                        $displayValue = substr($value, 0, 50) . '...';
                                    }
                                    echo "<td>" . htmlspecialchars($displayValue ?? 'NULL') . "</td>";
                                }
                                echo "</tr>";
                            }
                            echo "</table>";
                            echo "</div>";
                        }
                    }
                }
                
                // Procura por tabelas relacionadas a jogos/apostas
                echo "<h2>🎯 Tabelas Relacionadas a Jogos/Apostas:</h2>";
                echo "<ul style='color: #fff; line-height: 2;'>";
                $found = false;
                foreach ($tables as $table) {
                    if (stripos($table, 'jogo') !== false || 
                        stripos($table, 'aposta') !== false || 
                        stripos($table, 'evento') !== false ||
                        stripos($table, 'partida') !== false ||
                        stripos($table, 'match') !== false ||
                        stripos($table, 'game') !== false) {
                        echo "<li class='success'>✅ {$table} <a href='?describe={$table}' style='color: #ff9800;'>[Ver Estrutura]</a></li>";
                        $found = true;
                    }
                }
                if (!$found) {
                    echo "<li class='error'>❌ Nenhuma tabela relacionada encontrada</li>";
                }
                echo "</ul>";
                
            } else {
                echo "<p class='error'>❌ Nenhuma tabela encontrada</p>";
            }
            
        } catch (Exception $e) {
            echo "<p class='error'>❌ Erro: " . $e->getMessage() . "</p>";
        }
        ?>

        <div style="margin-top: 30px; padding: 20px; background: #0a0a0a; border-radius: 5px;">
            <h3 style="color: #ff9800;">💡 Próximo Passo:</h3>
            <p style="color: #fff;">
                Identifique qual tabela armazena os jogos e me informe o nome dela.
                Depois vou criar o SQL correto para essa tabela!
            </p>
        </div>
    </div>
</body>
</html>

