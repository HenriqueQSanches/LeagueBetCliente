<?php
/**
 * SCRIPT DE IMPORTAÇÃO AUTOMÁTICA DO BANCO DE DADOS
 * Banca Esportiva - Sistema de Apostas
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0); // Sem limite de tempo
ini_set('memory_limit', '512M'); // Aumentar memória

$is_cli = php_sapi_name() === 'cli';

echo "\n";
echo "╔═══════════════════════════════════════════════════╗\n";
echo "║    IMPORTADOR AUTOMÁTICO - BANCO DE DADOS         ║\n";
echo "╚═══════════════════════════════════════════════════╝\n\n";

// Verificar se o arquivo SQL existe
$sql_file = 'reidoscript bancas.sql';

if (!file_exists($sql_file)) {
    echo "❌ ERRO: Arquivo '$sql_file' não encontrado!\n";
    echo "Certifique-se de que o arquivo está na mesma pasta deste script.\n\n";
    exit(1);
}

echo "✅ Arquivo SQL encontrado: $sql_file\n";
echo "📊 Tamanho: " . number_format(filesize($sql_file) / 1024 / 1024, 2) . " MB\n\n";

// Função para ler input
function ler_input($prompt, $default = '') {
    echo $prompt;
    if ($default) {
        echo " (padrão: $default)";
    }
    echo ": ";
    
    $handle = fopen("php://stdin", "r");
    $line = trim(fgets($handle));
    fclose($handle);
    
    return $line ?: $default;
}

if (!$is_cli) {
    echo "<h3>⚠️ Execute via terminal para importação automática:</h3>";
    echo "<pre>php importar-banco.php</pre>";
    echo "<hr>";
    echo "<h3>📖 Ou consulte o arquivo IMPORTAR-BANCO.md para importação manual.</h3>";
    exit;
}

echo "═══ CONFIGURAÇÕES DO BANCO DE DADOS ═══\n\n";

$db_host = ler_input("Host do MySQL", "localhost");
$db_user = ler_input("Usuário do MySQL", "root");
$db_pass = ler_input("Senha do MySQL", "");
$db_name = ler_input("Nome do banco (será criado se não existir)", "banca_esportiva");

echo "\n═══════════════════════════════════════════════════\n";
echo "📋 RESUMO\n";
echo "═══════════════════════════════════════════════════\n";
echo "Host: $db_host\n";
echo "Usuário: $db_user\n";
echo "Banco: $db_name\n";
echo "Arquivo: $sql_file\n";
echo "═══════════════════════════════════════════════════\n\n";

$confirmar = ler_input("Continuar com a importação? (s/n)", "s");

if (strtolower($confirmar) !== 's') {
    echo "\n❌ Importação cancelada.\n";
    exit;
}

echo "\n⚙️ Iniciando importação...\n\n";

// Tentar conectar ao MySQL
try {
    echo "🔌 Conectando ao MySQL...\n";
    $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Conectado ao MySQL com sucesso!\n\n";
} catch (PDOException $e) {
    echo "❌ ERRO ao conectar ao MySQL: " . $e->getMessage() . "\n";
    echo "\nVerifique:\n";
    echo "  - O MySQL está rodando?\n";
    echo "  - Usuário e senha estão corretos?\n";
    echo "  - Host está correto?\n\n";
    exit(1);
}

// Criar banco de dados se não existir
try {
    echo "📦 Criando banco de dados '$db_name'...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✅ Banco de dados criado/verificado com sucesso!\n\n";
} catch (PDOException $e) {
    echo "❌ ERRO ao criar banco: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Selecionar o banco
try {
    $pdo->exec("USE `$db_name`");
    echo "✅ Banco '$db_name' selecionado.\n\n";
} catch (PDOException $e) {
    echo "❌ ERRO ao selecionar banco: " . $e->getMessage() . "\n\n";
    exit(1);
}

// Importar o arquivo SQL
echo "📥 Importando arquivo SQL...\n";
echo "⏳ Isso pode levar alguns minutos. Aguarde...\n\n";

$sql_content = file_get_contents($sql_file);

if ($sql_content === false) {
    echo "❌ ERRO ao ler o arquivo SQL!\n\n";
    exit(1);
}

// Remover comentários e linhas vazias
$sql_content = preg_replace('/^--.*$/m', '', $sql_content);
$sql_content = preg_replace('/^\/\*.*?\*\//ms', '', $sql_content);

// Dividir em queries
$queries = array_filter(
    array_map('trim', explode(';', $sql_content)),
    function($query) {
        return !empty($query) && strlen($query) > 3;
    }
);

$total = count($queries);
$success = 0;
$errors = 0;

echo "📊 Total de comandos SQL a executar: $total\n\n";

$start_time = time();

foreach ($queries as $index => $query) {
    $progress = ($index + 1);
    
    // Mostrar progresso a cada 100 queries
    if ($progress % 100 == 0 || $progress == $total) {
        $percent = round(($progress / $total) * 100);
        $elapsed = time() - $start_time;
        echo sprintf("\r⏳ Progresso: %d/%d (%d%%) - %ds", $progress, $total, $percent, $elapsed);
    }
    
    try {
        $pdo->exec($query);
        $success++;
    } catch (PDOException $e) {
        $errors++;
        // Ignorar erros de tabela já existe
        if (strpos($e->getMessage(), 'already exists') === false) {
            if ($errors < 10) { // Mostrar apenas os primeiros 10 erros
                echo "\n⚠️ Aviso na query " . ($index + 1) . ": " . substr($e->getMessage(), 0, 100) . "...\n";
            }
        }
    }
}

echo "\n\n";
$total_time = time() - $start_time;

// Verificar tabelas criadas
try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $table_count = count($tables);
    
    echo "╔═══════════════════════════════════════════════════╗\n";
    echo "║           ✅ IMPORTAÇÃO CONCLUÍDA!                ║\n";
    echo "╚═══════════════════════════════════════════════════╝\n\n";
    
    echo "📊 ESTATÍSTICAS:\n";
    echo "  ✅ Queries executadas: $success\n";
    if ($errors > 0) {
        echo "  ⚠️ Avisos: $errors (pode ser normal)\n";
    }
    echo "  📁 Tabelas criadas: $table_count\n";
    echo "  ⏱️ Tempo total: {$total_time}s\n\n";
    
    // Verificar usuário admin
    echo "🔍 Verificando usuário admin...\n";
    $stmt = $pdo->query("SELECT login, nome, email FROM sis_users WHERE login = 'admin' LIMIT 1");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "✅ Usuário admin encontrado!\n";
        echo "   Login: {$admin['login']}\n";
        echo "   Nome: {$admin['nome']}\n";
        echo "   Email: {$admin['email']}\n";
        echo "   Senha padrão: 123456\n\n";
    } else {
        echo "⚠️ Usuário admin não encontrado. Verifique a importação.\n\n";
    }
    
    echo "═══════════════════════════════════════════════════\n";
    echo "📋 PRÓXIMOS PASSOS:\n";
    echo "═══════════════════════════════════════════════════\n";
    echo "1. Configurar conexao.php:\n";
    echo "   \$conexao = new PDO('mysql:host=$db_host;dbname=$db_name', '$db_user', '****');\n\n";
    echo "2. Configurar inc.config.php:\n";
    echo "   - Linhas 58-71: dados do banco\n";
    echo "   - Linha 81 e 90: database = '$db_name'\n\n";
    echo "3. Acessar: http://seudominio.com/admin/\n";
    echo "   Login: admin\n";
    echo "   Senha: 123456\n\n";
    echo "4. ⚠️ ALTERAR A SENHA PADRÃO!\n\n";
    
    echo "═══════════════════════════════════════════════════\n";
    echo "📖 Para mais informações, consulte:\n";
    echo "   - IMPORTAR-BANCO.md\n";
    echo "   - CONFIGURACAO.md\n";
    echo "═══════════════════════════════════════════════════\n\n";
    
    // Oferecer para configurar automaticamente
    echo "💡 Deseja configurar os arquivos automaticamente agora? (s/n): ";
    $handle = fopen("php://stdin", "r");
    $config_now = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($config_now) === 's') {
        echo "\n🚀 Executando configurador...\n\n";
        
        // Criar arquivo com as configurações do banco
        $config_data = [
            'db_host' => $db_host,
            'db_user' => $db_user,
            'db_pass' => $db_pass,
            'db_name' => $db_name
        ];
        file_put_contents('.db_config.tmp', json_encode($config_data));
        
        // Executar o configurador
        if (file_exists('configurar.php')) {
            passthru('php configurar.php');
        } else {
            echo "⚠️ Arquivo configurar.php não encontrado.\n";
            echo "Configure manualmente seguindo CONFIGURACAO.md\n";
        }
        
        // Remover arquivo temporário
        @unlink('.db_config.tmp');
    }
    
} catch (PDOException $e) {
    echo "❌ ERRO ao verificar importação: " . $e->getMessage() . "\n\n";
}

echo "\n🎉 Processo concluído!\n\n";

