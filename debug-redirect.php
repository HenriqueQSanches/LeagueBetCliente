<?php
// Debug do redirect

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Debug de Redirect</h1>";
echo "<hr>";

// Verificar configurações
echo "<h2>1. Verificar Tabela sis_dados</h2>";

try {
    $pdo = new PDO('mysql:host=localhost;dbname=banca_esportiva', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT * FROM sis_dados LIMIT 1");
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($dados) {
        echo "<pre>";
        print_r($dados);
        echo "</pre>";
        
        echo "<h3>Campos importantes:</h3>";
        echo "<ul>";
        if (isset($dados['status'])) echo "<li><strong>status:</strong> " . $dados['status'] . "</li>";
        if (isset($dados['manutencao'])) echo "<li><strong>manutencao:</strong> " . $dados['manutencao'] . "</li>";
        if (isset($dados['sistema_aberto'])) echo "<li><strong>sistema_aberto:</strong> " . $dados['sistema_aberto'] . "</li>";
        echo "</ul>";
    } else {
        echo "❌ Nenhum dado encontrado na tabela sis_dados<br>";
    }
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage();
}

echo "<hr>";

// Testar acesso direto
echo "<h2>2. Testar URLs</h2>";
echo "<ul>";
echo "<li><a href='http://localhost:8000/teste-simples.php'>Teste Simples</a> - Deve funcionar</li>";
echo "<li><a href='http://localhost:8000/teste-conexao.php'>Teste Conexão</a> - Deve funcionar</li>";
echo "<li><a href='http://localhost:8000/admin/'>Admin</a> - Vamos testar</li>";
echo "</ul>";

?>

