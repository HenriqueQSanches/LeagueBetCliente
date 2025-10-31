<?php
echo "<h1>✅ Servidor PHP Funcionando!</h1>";
echo "<p>Se você está vendo isso, o servidor está OK.</p>";
echo "<hr>";
echo "<h2>Informações:</h2>";
echo "<ul>";
echo "<li>PHP Version: " . phpversion() . "</li>";
echo "<li>Current Time: " . date('Y-m-d H:i:s') . "</li>";
echo "<li>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</li>";
echo "<li>Document Root: " . __DIR__ . "</li>";
echo "</ul>";
echo "<hr>";
echo "<h2>Próximo passo:</h2>";
echo "<p><a href='http://localhost:8000'>Testar Site Principal</a></p>";
echo "<p><a href='http://localhost:8000/admin/'>Testar Admin</a></p>";
echo "<p><a href='http://localhost:8000/teste-conexao.php'>Testar Conexão DB</a></p>";
?>

