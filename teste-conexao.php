<?php
/**
 * TESTE DE CONEXÃO - DIAGNÓSTICO
 */

echo "<h1>🔍 Teste de Conexão ao Banco de Dados</h1>";
echo "<hr>";

// Teste 1: Verificar se MySQL está disponível
echo "<h2>Teste 1: Extensão PDO MySQL</h2>";
if (extension_loaded('pdo_mysql')) {
    echo "✅ Extensão PDO MySQL está carregada<br>";
} else {
    echo "❌ Extensão PDO MySQL NÃO está carregada<br>";
    echo "⚠️ Você precisa ativar a extensão pdo_mysql no php.ini<br>";
}
echo "<hr>";

// Teste 2: Tentar conectar ao MySQL
echo "<h2>Teste 2: Conexão ao MySQL (sem banco)</h2>";
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    echo "✅ Conectado ao MySQL com sucesso!<br>";
    echo "📊 Versão do MySQL: " . $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) . "<br>";
    $pdo = null;
} catch (PDOException $e) {
    echo "❌ ERRO ao conectar ao MySQL<br>";
    echo "📝 Mensagem: " . $e->getMessage() . "<br>";
    echo "<br>";
    echo "<strong>Possíveis causas:</strong><br>";
    echo "- MySQL não está rodando no XAMPP<br>";
    echo "- Usuário ou senha incorretos<br>";
    echo "- Porta 3306 bloqueada ou em uso<br>";
    die();
}
echo "<hr>";

// Teste 3: Verificar se o banco existe
echo "<h2>Teste 3: Verificar Banco 'banca_esportiva'</h2>";
$database = 'banca_esportiva';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $stmt = $pdo->query("SHOW DATABASES LIKE '$database'");
    $result = $stmt->fetch();
    
    if ($result) {
        echo "✅ Banco de dados '$database' existe!<br>";
    } else {
        echo "❌ Banco de dados '$database' NÃO existe!<br>";
        echo "⚠️ Execute novamente o script IMPORTAR_BANCO.bat<br>";
        die();
    }
    $pdo = null;
} catch (PDOException $e) {
    echo "❌ ERRO ao verificar banco: " . $e->getMessage() . "<br>";
    die();
}
echo "<hr>";

// Teste 4: Conectar ao banco específico
echo "<h2>Teste 4: Conexão ao Banco 'banca_esportiva'</h2>";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conectado ao banco '$database' com sucesso!<br>";
} catch (PDOException $e) {
    echo "❌ ERRO ao conectar ao banco '$database'<br>";
    echo "📝 Mensagem: " . $e->getMessage() . "<br>";
    die();
}
echo "<hr>";

// Teste 5: Verificar tabelas
echo "<h2>Teste 5: Verificar Tabelas no Banco</h2>";

try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $count = count($tables);
    
    echo "✅ Total de tabelas: <strong>$count</strong><br>";
    
    if ($count >= 40) {
        echo "✅ Banco parece estar completo (esperado: 43 tabelas)<br>";
        echo "<br><strong>Primeiras 10 tabelas:</strong><br>";
        echo "<ul>";
        foreach (array_slice($tables, 0, 10) as $table) {
            echo "<li>$table</li>";
        }
        echo "</ul>";
    } else {
        echo "⚠️ Banco parece incompleto (esperado: 43, encontrado: $count)<br>";
        echo "⚠️ Reimporte o banco usando IMPORTAR_BANCO.bat<br>";
    }
} catch (PDOException $e) {
    echo "❌ ERRO ao listar tabelas: " . $e->getMessage() . "<br>";
    die();
}
echo "<hr>";

// Teste 6: Verificar usuário admin
echo "<h2>Teste 6: Verificar Usuário Admin</h2>";

try {
    $stmt = $pdo->query("SELECT login, nome, email FROM sis_users WHERE login = 'admin' LIMIT 1");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "✅ Usuário admin encontrado!<br>";
        echo "📝 Login: <strong>{$admin['login']}</strong><br>";
        echo "📝 Nome: <strong>{$admin['nome']}</strong><br>";
        echo "📝 Email: <strong>{$admin['email']}</strong><br>";
    } else {
        echo "❌ Usuário admin NÃO encontrado!<br>";
        echo "⚠️ Reimporte o banco usando IMPORTAR_BANCO.bat<br>";
    }
} catch (PDOException $e) {
    echo "❌ ERRO ao verificar admin: " . $e->getMessage() . "<br>";
}
echo "<hr>";

// Teste 7: Testar arquivo conexao.php
echo "<h2>Teste 7: Verificar arquivo conexao.php</h2>";

if (file_exists('conexao.php')) {
    echo "✅ Arquivo conexao.php existe<br>";
    echo "<br><strong>Conteúdo:</strong><br>";
    echo "<pre>" . htmlspecialchars(file_get_contents('conexao.php')) . "</pre>";
} else {
    echo "❌ Arquivo conexao.php NÃO existe!<br>";
}
echo "<hr>";

// Teste 8: Testar configuração inc.config.php
echo "<h2>Teste 8: Verificar inc.config.php</h2>";

if (file_exists('inc.config.php')) {
    echo "✅ Arquivo inc.config.php existe<br>";
    
    // Carregar o arquivo e verificar configurações
    ob_start();
    include 'inc.config.php';
    ob_end_clean();
    
    if (isset($config['db-local'])) {
        echo "✅ Configuração 'db-local' encontrada<br>";
        echo "<pre>";
        echo "Host: " . $config['db-local']['host'] . "\n";
        echo "Username: " . $config['db-local']['username'] . "\n";
        echo "Password: " . ($config['db-local']['password'] ? '***' : '(vazio)') . "\n";
        echo "Database: " . $config['db-local']['database'] . "\n";
        echo "</pre>";
    } else {
        echo "❌ Configuração 'db-local' NÃO encontrada!<br>";
    }
    
    if (isset($config['db'])) {
        echo "✅ Configuração 'db' encontrada<br>";
        echo "<pre>";
        echo "Host: " . $config['db']['host'] . "\n";
        echo "Username: " . $config['db']['username'] . "\n";
        echo "Password: " . ($config['db']['password'] ? '***' : '(vazio)') . "\n";
        echo "Database: " . $config['db']['database'] . "\n";
        echo "</pre>";
    } else {
        echo "❌ Configuração 'db' NÃO encontrada!<br>";
    }
} else {
    echo "❌ Arquivo inc.config.php NÃO existe!<br>";
}
echo "<hr>";

echo "<h2>✅ DIAGNÓSTICO COMPLETO!</h2>";
echo "<p><strong>Se todos os testes passaram, a conexão está funcionando.</strong></p>";
echo "<p>O problema pode estar no código do sistema. Verifique os arquivos de conexão.</p>";

$pdo = null;
?>

