<?php
// Script para verificar as cotações dos jogos

require_once 'conexao.php';

echo "<h1>Verificação de Cotações</h1>";
echo "<hr>";

// Total de cotações
$sql1 = "SELECT COUNT(*) as total FROM sis_cotacoes";
$result1 = $conexao->query($sql1)->fetch(PDO::FETCH_ASSOC);
echo "<h3>✅ Total de Cotações no Sistema: " . $result1['total'] . "</h3>";

// Verificar estrutura da tabela sis_cotacoes PRIMEIRO
echo "<hr>";
echo "<h3>Estrutura da Tabela sis_cotacoes:</h3>";
$sql3 = "DESCRIBE sis_cotacoes";
$result3 = $conexao->query($sql3)->fetchAll(PDO::FETCH_ASSOC);

echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr style='background: #333; color: white;'><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";

foreach ($result3 as $row) {
    echo "<tr>";
    echo "<td>{$row['Field']}</td>";
    echo "<td>{$row['Type']}</td>";
    echo "<td>{$row['Null']}</td>";
    echo "<td>{$row['Key']}</td>";
    echo "<td>{$row['Default']}</td>";
    echo "</tr>";
}

echo "</table>";

// Analisar resultado
if ($result1['total'] == 0) {
    echo "<hr>";
    echo "<div style='background: #ffcccc; padding: 20px; border: 2px solid #cc0000; border-radius: 5px;'>";
    echo "<h3 style='color: #cc0000;'>⚠️ PROBLEMA IDENTIFICADO</h3>";
    echo "<p><strong>Não há NENHUMA cotação no banco de dados!</strong></p>";
    echo "<p>Os jogos foram importados, mas as <strong>cotações (odds)</strong> estão faltando.</p>";
    echo "<p><strong>Por isso os jogos não aparecem no frontend!</strong></p>";
    echo "<hr>";
    echo "<h4>Possíveis Causas:</h4>";
    echo "<ol>";
    echo "<li>O script <code>jogos.php</code> pode não estar importando as cotações</li>";
    echo "<li>A API pode ter mudado o formato de resposta</li>";
    echo "<li>As cotações podem estar em outra tabela ou com outro nome</li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<hr>";
    echo "<h3>Exemplo de Cotações:</h3>";
    $sql5 = "SELECT * FROM sis_cotacoes LIMIT 5";
    $result5 = $conexao->query($sql5)->fetchAll(PDO::FETCH_ASSOC);
    echo "<pre>";
    print_r($result5);
    echo "</pre>";
}
?>

