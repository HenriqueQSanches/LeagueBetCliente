<?php
// Script para verificar as cotações na tabela sis_jogos

require_once 'conexao.php';

echo "<h1>Verificação de Cotações na Tabela sis_jogos</h1>";
echo "<hr>";

// Verificar estrutura da tabela sis_jogos
echo "<h3>Estrutura da Tabela sis_jogos:</h3>";
$sql1 = "DESCRIBE sis_jogos";
$result1 = $conexao->query($sql1)->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr style='background: #333; color: white;'><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";

foreach ($result1 as $row) {
    $highlight = '';
    if ($row['Field'] == 'cotacoes') {
        $highlight = " style='background: #ffff99;'";
    }
    echo "<tr{$highlight}>";
    echo "<td>{$row['Field']}</td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>{$row['Key']}</td>";
    echo "<td>{$row['Default']}</td>";
    echo "</tr>";
}

echo "</table>";

// Verificar jogos com cotações
echo "<hr>";
echo "<h3>Análise de Cotações nos Jogos:</h3>";

$sql2 = "SELECT COUNT(*) as total FROM sis_jogos";
$result2 = $conexao->query($sql2)->fetch(PDO::FETCH_ASSOC);
echo "<p><strong>Total de jogos:</strong> {$result2['total']}</p>";

$sql3 = "SELECT COUNT(*) as total FROM sis_jogos WHERE cotacoes IS NOT NULL AND cotacoes != ''";
$result3 = $conexao->query($sql3)->fetch(PDO::FETCH_ASSOC);
echo "<p><strong>Jogos COM cotações:</strong> {$result3['total']}</p>";

$sql4 = "SELECT COUNT(*) as total FROM sis_jogos WHERE cotacoes IS NULL OR cotacoes = ''";
$result4 = $conexao->query($sql4)->fetch(PDO::FETCH_ASSOC);
echo "<p><strong>Jogos SEM cotações:</strong> {$result4['total']}</p>";

// Verificar jogos futuros com cotações
echo "<hr>";
echo "<h3>Jogos Futuros (Disponíveis para Apostar):</h3>";

$sql5 = "SELECT COUNT(*) as total FROM sis_jogos WHERE status = 1 AND data >= CURDATE()";
$result5 = $conexao->query($sql5)->fetch(PDO::FETCH_ASSOC);
echo "<p><strong>Total de jogos futuros:</strong> {$result5['total']}</p>";

$sql6 = "SELECT COUNT(*) as total FROM sis_jogos WHERE status = 1 AND data >= CURDATE() AND (cotacoes IS NOT NULL AND cotacoes != '')";
$result6 = $conexao->query($sql6)->fetch(PDO::FETCH_ASSOC);
echo "<p><strong>Jogos futuros COM cotações:</strong> {$result6['total']}</p>";

$sql7 = "SELECT COUNT(*) as total FROM sis_jogos WHERE status = 1 AND data >= CURDATE() AND (cotacoes IS NULL OR cotacoes = '')";
$result7 = $conexao->query($sql7)->fetch(PDO::FETCH_ASSOC);
echo "<p><strong>Jogos futuros SEM cotações:</strong> {$result7['total']}</p>";

// Mostrar exemplo de jogo com cotações (se existir)
echo "<hr>";
echo "<h3>Exemplo de Jogo COM Cotações:</h3>";

$sql8 = "SELECT id, data, hora, cotacoes FROM sis_jogos WHERE cotacoes IS NOT NULL AND cotacoes != '' LIMIT 1";
$result8 = $conexao->query($sql8)->fetch(PDO::FETCH_ASSOC);

if ($result8) {
    echo "<p><strong>ID:</strong> {$result8['id']}</p>";
    echo "<p><strong>Data:</strong> {$result8['data']}</p>";
    echo "<p><strong>Hora:</strong> {$result8['hora']}</p>";
    echo "<p><strong>Cotações (JSON):</strong></p>";
    echo "<pre>";
    echo htmlspecialchars($result8['cotacoes']);
    echo "</pre>";
    
    // Decodificar JSON
    $cotacoes_obj = json_decode($result8['cotacoes'], true);
    if ($cotacoes_obj) {
        echo "<h4>Cotações Decodificadas:</h4>";
        echo "<pre>";
        print_r($cotacoes_obj);
        echo "</pre>";
    }
} else {
    echo "<div style='background: #ffcccc; padding: 20px; border: 2px solid #cc0000; border-radius: 5px;'>";
    echo "<h3 style='color: #cc0000;'>⚠️ PROBLEMA: Nenhum jogo possui cotações!</h3>";
    echo "<p>A coluna 'cotacoes' está vazia em todos os jogos importados.</p>";
    echo "<p><strong>Isso significa que o script jogos.php não está importando as cotações da API.</strong></p>";
    echo "</div>";
}

// Mostrar exemplo de jogos futuros sem cotações
echo "<hr>";
echo "<h3>Primeiros 5 Jogos Futuros SEM Cotações:</h3>";

$sql9 = <<<SQL
SELECT
    j.id,
    j.data,
    j.hora,
    t1.title AS casa,
    t2.title AS fora,
    c.title AS campeonato,
    j.cotacoes
FROM
    sis_jogos j
LEFT JOIN
    sis_times t1 ON t1.id = j.timecasa
LEFT JOIN
    sis_times t2 ON t2.id = j.timefora
LEFT JOIN
    sis_campeonatos c ON c.id = j.campeonato
WHERE
    j.status = 1
    AND j.data >= CURDATE()
    AND (j.cotacoes IS NULL OR j.cotacoes = '')
ORDER BY
    j.data ASC, j.hora ASC
LIMIT 5
SQL;

$result9 = $conexao->query($sql9)->fetchAll(PDO::FETCH_ASSOC);

if (count($result9) > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background: #333; color: white;'><th>ID</th><th>Data</th><th>Hora</th><th>Jogo</th><th>Campeonato</th><th>Cotações</th></tr>";
    
    foreach ($result9 as $jogo) {
        echo "<tr>";
        echo "<td>{$jogo['id']}</td>";
        echo "<td>" . date('d/m/Y', strtotime($jogo['data'])) . "</td>";
        echo "<td>{$jogo['hora']}</td>";
        echo "<td><strong>{$jogo['casa']}</strong> x <strong>{$jogo['fora']}</strong></td>";
        echo "<td>{$jogo['campeonato']}</td>";
        echo "<td style='color: red;'>" . (empty($jogo['cotacoes']) ? "VAZIO" : $jogo['cotacoes']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}
?>

