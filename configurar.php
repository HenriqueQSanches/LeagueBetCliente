<?php
/**
 * SCRIPT DE CONFIGURAÇÃO AUTOMÁTICA
 * Banca Esportiva - Sistema de Apostas
 * 
 * Este script ajuda a configurar rapidamente o sistema
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cores para terminal/output
$cor_verde = "\033[32m";
$cor_vermelho = "\033[31m";
$cor_amarelo = "\033[33m";
$cor_azul = "\033[34m";
$cor_reset = "\033[0m";

echo "\n";
echo "╔═══════════════════════════════════════════════════╗\n";
echo "║   CONFIGURADOR AUTOMÁTICO - BANCA ESPORTIVA       ║\n";
echo "╚═══════════════════════════════════════════════════╝\n\n";

// Verificar se está rodando via navegador ou CLI
$is_cli = php_sapi_name() === 'cli';

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

// Verificar arquivos necessários
echo "📋 Verificando arquivos...\n";

$arquivos_necessarios = [
    'inc.config.php' => file_exists('inc.config.php'),
    'conexao.php' => file_exists('conexao.php'),
    'index.php' => file_exists('index.php'),
    'vendor/autoload.php' => file_exists('vendor/autoload.php'),
];

foreach ($arquivos_necessarios as $arquivo => $existe) {
    $status = $existe ? "✅ OK" : "❌ FALTANDO";
    echo "  $arquivo ... $status\n";
}

echo "\n";

// Verificar permissões de pastas
echo "📁 Verificando permissões de pastas...\n";

$pastas = ['_temp', '_temp/cache', '_temp/session', 'imagens', 'arquivos'];
foreach ($pastas as $pasta) {
    if (!file_exists($pasta)) {
        mkdir($pasta, 0777, true);
        echo "  ✅ Pasta criada: $pasta\n";
    } else {
        echo "  ✅ Pasta existe: $pasta\n";
    }
    @chmod($pasta, 0777);
}

echo "\n";

// Verificar extensões PHP
echo "🔧 Verificando extensões PHP...\n";

$extensoes = ['pdo', 'pdo_mysql', 'curl', 'gd', 'mbstring', 'openssl', 'json'];
foreach ($extensoes as $ext) {
    $loaded = extension_loaded($ext);
    $status = $loaded ? "✅ OK" : "❌ FALTANDO";
    echo "  $ext ... $status\n";
}

echo "\n";

// Se não for CLI, mostrar informações e parar aqui
if (!$is_cli) {
    echo "<h3>⚠️ Para configuração completa, execute via terminal:</h3>";
    echo "<pre>php configurar.php</pre>";
    echo "<hr>";
    echo "<h3>📊 Informações do Sistema:</h3>";
    echo "<ul>";
    echo "<li>PHP Version: " . phpversion() . "</li>";
    echo "<li>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</li>";
    echo "<li>Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "</li>";
    echo "</ul>";
    exit;
}

// Configuração interativa
echo "╔═══════════════════════════════════════════════════╗\n";
echo "║        CONFIGURAÇÃO INTERATIVA                    ║\n";
echo "╚═══════════════════════════════════════════════════╝\n\n";

echo "Vamos configurar seu sistema! Pressione ENTER para usar o valor padrão.\n\n";

// Coletar informações
$config = [];

echo "═══ CONFIGURAÇÕES DO SITE ═══\n";
$config['title'] = ler_input("Título do site", "Minha Banca Esportiva");
$config['dominio'] = ler_input("Domínio (ex: minhabanca.com.br)", "localhost");
$config['email'] = ler_input("Email de contato", "contato@{$config['dominio']}");

// Adicionar http:// ou https:// se não tiver
if (!preg_match('/^https?:\/\//', $config['dominio'])) {
    $protocolo = ler_input("Usar HTTPS? (s/n)", "n");
    $config['uri'] = ($protocolo === 's' ? 'https://' : 'http://') . $config['dominio'];
} else {
    $config['uri'] = $config['dominio'];
}

echo "\n═══ CONFIGURAÇÕES DO BANCO DE DADOS ═══\n";
$config['db_host'] = ler_input("Host do banco", "localhost");
$config['db_name'] = ler_input("Nome do banco", "banca_esportiva");
$config['db_user'] = ler_input("Usuário do banco", "root");
$config['db_pass'] = ler_input("Senha do banco", "");

echo "\n═══ LAYOUT DO SITE ═══\n";
echo "Escolha o layout:\n";
echo "  1 - Site Layout 1 (Padrão)\n";
echo "  2 - Site Layout 2\n";
echo "  3 - Site Layout 3\n";
$layout = ler_input("Digite o número do layout", "1");
$config['layout'] = "site" . $layout;

echo "\n\n";
echo "═══════════════════════════════════════════════════\n";
echo "📋 RESUMO DA CONFIGURAÇÃO\n";
echo "═══════════════════════════════════════════════════\n";
echo "Site: {$config['title']}\n";
echo "URL: {$config['uri']}\n";
echo "Email: {$config['email']}\n";
echo "Banco: {$config['db_name']}@{$config['db_host']}\n";
echo "Usuário DB: {$config['db_user']}\n";
echo "Layout: {$config['layout']}\n";
echo "═══════════════════════════════════════════════════\n\n";

$confirmar = ler_input("Confirmar e aplicar configurações? (s/n)", "s");

if (strtolower($confirmar) !== 's') {
    echo "\n❌ Configuração cancelada.\n";
    exit;
}

echo "\n⚙️ Aplicando configurações...\n\n";

// Backup dos arquivos originais
if (!file_exists('inc.config.php.backup')) {
    copy('inc.config.php', 'inc.config.php.backup');
    echo "✅ Backup criado: inc.config.php.backup\n";
}

if (!file_exists('conexao.php.backup')) {
    copy('conexao.php', 'conexao.php.backup');
    echo "✅ Backup criado: conexao.php.backup\n";
}

// Atualizar inc.config.php
$conteudo_inc = file_get_contents('inc.config.php');

// Linhas 27-31
$conteudo_inc = preg_replace(
    "/'title' => '.*?'/",
    "'title' => '{$config['title']}'",
    $conteudo_inc
);

$conteudo_inc = preg_replace(
    "/'dominio' => '.*?'/",
    "'dominio' => '{$config['uri']}'",
    $conteudo_inc
);

$conteudo_inc = preg_replace(
    "/'email' => '.*?'/",
    "'email' => '{$config['email']}'",
    $conteudo_inc
);

$conteudo_inc = preg_replace(
    "/'uri' => '.*?'/",
    "'uri' => '{$config['uri']}'",
    $conteudo_inc
);

// Linhas 58-71 - Banco de dados
$conteudo_inc = preg_replace(
    "/('production' => \[.*?'host' => ').*?(')/s",
    "$1{$config['db_host']}$2",
    $conteudo_inc
);

$conteudo_inc = preg_replace(
    "/('production' => \[.*?'username' => ').*?(')/s",
    "$1{$config['db_user']}$2",
    $conteudo_inc
);

$conteudo_inc = preg_replace(
    "/('production' => \[.*?'password' => ').*?(')/s",
    "$1{$config['db_pass']}$2",
    $conteudo_inc
);

$conteudo_inc = preg_replace(
    "/('production' => \[.*?'database' => ').*?(')/s",
    "$1{$config['db_name']}$2",
    $conteudo_inc
);

// Localhost também
$conteudo_inc = preg_replace(
    "/('localhost' => \[.*?'host' => ').*?(')/s",
    "$1{$config['db_host']}$2",
    $conteudo_inc
);

$conteudo_inc = preg_replace(
    "/('localhost' => \[.*?'username' => ').*?(')/s",
    "$1{$config['db_user']}$2",
    $conteudo_inc
);

$conteudo_inc = preg_replace(
    "/('localhost' => \[.*?'password' => ').*?(')/s",
    "$1{$config['db_pass']}$2",
    $conteudo_inc
);

$conteudo_inc = preg_replace(
    "/('localhost' => \[.*?'database' => ').*?(')/s",
    "$1{$config['db_name']}$2",
    $conteudo_inc
);

// Linha 99 - Layout
$conteudo_inc = preg_replace(
    "/\\\$config\['modules'\]\['site'\] = \\\$config\['modules'\]\['site\d'\];/",
    "\$config['modules']['site'] = \$config['modules']['{$config['layout']}'];",
    $conteudo_inc
);

file_put_contents('inc.config.php', $conteudo_inc);
echo "✅ Arquivo inc.config.php atualizado\n";

// Atualizar conexao.php
$conteudo_conexao = file_get_contents('conexao.php');
$conteudo_conexao = preg_replace(
    "/new PDO\('mysql:host=.*?;dbname=.*?', '.*?', '.*?'\)/",
    "new PDO('mysql:host={$config['db_host']};dbname={$config['db_name']}', '{$config['db_user']}', '{$config['db_pass']}')",
    $conteudo_conexao
);

file_put_contents('conexao.php', $conteudo_conexao);
echo "✅ Arquivo conexao.php atualizado\n";

echo "\n";
echo "╔═══════════════════════════════════════════════════╗\n";
echo "║           ✅ CONFIGURAÇÃO CONCLUÍDA!              ║\n";
echo "╚═══════════════════════════════════════════════════╝\n\n";

echo "📋 PRÓXIMOS PASSOS:\n";
echo "  1. Criar o banco de dados '{$config['db_name']}' no MySQL\n";
echo "  2. Importar o arquivo SQL (se houver)\n";
echo "  3. Configurar os CRON jobs no cPanel:\n";
echo "     - Diário: curl -s {$config['uri']}/jogos.php\n";
echo "     - Minuto: curl -s {$config['uri']}/cron/jogos/resultados\n";
echo "  4. Acessar: {$config['uri']}\n";
echo "  5. Admin: {$config['uri']}/admin/\n";
echo "     Login: admin / Senha: 123456\n\n";

echo "💾 Backups criados:\n";
echo "  - inc.config.php.backup\n";
echo "  - conexao.php.backup\n\n";

echo "📖 Para mais informações, consulte: CONFIGURACAO.md\n\n";

echo "🎉 Boa sorte com sua banca esportiva!\n\n";

