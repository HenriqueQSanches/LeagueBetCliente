<?php
// Teste direto para ver se os jogos estão no banco e sendo retornados

require_once 'inc.config.php';

use app\core\Model;
use app\models\DadosModel;

echo "<h1>Teste de Jogos Disponíveis</h1>";
echo "<hr>";

// Query direta no banco
$termos = <<<SQL
SELECT
    COUNT(*) as total
FROM
    `sis_jogos` AS a
WHERE 
    a.status = 1 
    AND a.data >= CURDATE()
SQL;

$result = \app\core\crud\Conn::getConn()->query($termos)->fetch(PDO::FETCH_ASSOC);
echo "<h3>Total de jogos disponíveis (status=1, data >= hoje): " . $result['total'] . "</h3>";

// Listar os próximos 10 jogos
$termos2 = <<<SQL
SELECT
    a.id,
    a.data,
    a.hora,
    b.title AS casa,
    c.title AS fora,
    d.title AS campeonato,
    a.status
FROM
    `sis_jogos` AS a
LEFT JOIN
    `sis_times` AS b ON b.id = a.timecasa
LEFT JOIN
    `sis_times` AS c ON c.id = a.timefora
LEFT JOIN
    `sis_campeonatos` AS d ON d.id = a.campeonato
WHERE 
    a.status = 1
    AND a.data >= CURDATE()
ORDER BY
    a.data ASC, a.hora ASC
LIMIT 10
SQL;

$jogos = \app\core\crud\Conn::getConn()->query($termos2)->fetchAll(PDO::FETCH_ASSOC);

echo "<h3>Próximos 10 Jogos:</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr style='background: #333; color: white;'><th>ID</th><th>Data</th><th>Hora</th><th>Jogo</th><th>Campeonato</th><th>Status</th></tr>";

foreach ($jogos as $jogo) {
    echo "<tr>";
    echo "<td>{$jogo['id']}</td>";
    echo "<td>" . date('d/m/Y', strtotime($jogo['data'])) . "</td>";
    echo "<td>{$jogo['hora']}</td>";
    echo "<td><strong>{$jogo['casa']}</strong> x <strong>{$jogo['fora']}</strong></td>";
    echo "<td>{$jogo['campeonato']}</td>";
    echo "<td>{$jogo['status']}</td>";
    echo "</tr>";
}

echo "</table>";

// Testar o controller jogosAction
echo "<hr>";
echo "<h3>Testando Controller apostarController::jogosAction():</h3>";

try {
    $controller = new \app\modules\website\controllers\apostarController();
    $resultado = $controller->jogosAction();
    
    echo "<p><strong>Países retornados:</strong> " . count($resultado['paises']) . "</p>";
    echo "<p><strong>Cotações retornadas:</strong> " . count($resultado['cotacoes']) . "</p>";
    echo "<p><strong>Grupos retornados:</strong> " . count($resultado['grupos']) . "</p>";
    
    if (!empty($resultado['paises'])) {
        echo "<h4>Primeiro País:</h4>";
        echo "<pre>";
        print_r($resultado['paises'][0]);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'><strong>ERRO: Nenhum país retornado!</strong></p>";
    }
    
    // Mostrar JSON completo
    echo "<hr>";
    echo "<h4>JSON Completo (primeiros 2000 caracteres):</h4>";
    echo "<pre>";
    echo substr(json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), 0, 2000);
    echo "...</pre>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>ERRO:</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>

