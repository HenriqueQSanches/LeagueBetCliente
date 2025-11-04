<?php
session_start();
session_destroy();
// Redirecionar para a página inicial do site (layout padrão)
header('Location: /');
exit;

