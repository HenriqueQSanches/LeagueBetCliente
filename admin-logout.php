<?php
session_start();
session_destroy();
// Redirecionar para a raiz do projeto (evita cair no /dashboard do XAMPP)
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
header('Location: ' . ($basePath === '' ? '/' : $basePath . '/'));
exit;

