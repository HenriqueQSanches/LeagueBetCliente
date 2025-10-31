<?php

echo "<h1>🔍 DEBUG - Carregando Sistema</h1>";
echo "<hr>";

echo "<h2>Passo 1: Incluindo inc.config.php</h2>";
ob_start();
include 'inc.config.php';
$output = ob_get_clean();

if ($output) {
    echo "<pre>Saída do inc.config.php:\n$output</pre>";
}
echo "✅ inc.config.php carregado<br>";
echo "<hr>";

echo "<h2>Passo 2: Verificando Configurações</h2>";
echo "<pre>";
echo "URI configurada: " . (isset($config['config']['uri']) ? $config['config']['uri'] : 'NÃO DEFINIDA') . "\n";
echo "Título: " . (isset($config['config']['title']) ? $config['config']['title'] : 'NÃO DEFINIDO') . "\n";
echo "DB Host: " . (isset($config['db']['localhost']['host']) ? $config['db']['localhost']['host'] : 'NÃO DEFINIDO') . "\n";
echo "DB Name: " . (isset($config['db']['localhost']['database']) ? $config['db']['localhost']['database'] : 'NÃO DEFINIDO') . "\n";
echo "</pre>";
echo "<hr>";

echo "<h2>Passo 3: Informações do Servidor</h2>";
echo "<pre>";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'NÃO DEFINIDA') . "\n";
echo "SERVER_NAME: " . ($_SERVER['SERVER_NAME'] ?? 'NÃO DEFINIDO') . "\n";
echo "IS_LOCAL: " . (defined('IS_LOCAL') ? (IS_LOCAL ? 'true' : 'false') : 'NÃO DEFINIDO') . "\n";
echo "</pre>";
echo "<hr>";

echo "<h2>Passo 4: Inicializar APP (comentado para debug)</h2>";
echo "<p>Vou comentar a inicialização para evitar o redirect...</p>";

// Comentado para debug
// \app\APP::Initialize();

echo "<hr>";
echo "<h2>✅ Debug Completo!</h2>";
echo "<p>O problema está na inicialização do APP. Vou investigar mais...</p>";
echo "<p><a href='debug-redirect.php'>Ver Debug de Redirect</a></p>";
?>

