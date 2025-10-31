<?php
/**
 * Router para Servidor PHP Embutido
 * Simula o comportamento do .htaccess
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Se é um arquivo real (CSS, JS, imagens, etc), serve diretamente
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false; // Serve o arquivo
}

// Todas as outras requisições vão para index.php
require_once __DIR__ . '/index.php';

