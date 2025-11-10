<?php
/**
 * Importação via BetsAPI (web)
 * Acesse: /Cliente/LeagueBetCliente-main/importar-betsapi.php
 */

ini_set('memory_limit', '-1');
ini_set('max_execution_time', 0);
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/inc.config.php';
require_once __DIR__ . '/app/modules/betsapi/SyncJogos.php';

?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar BetsAPI - LeagueBet</title>
    <style>
        body{font-family:'Courier New',monospace;background:#111;color:#0f0;margin:0;padding:20px}
        .box{max-width:1000px;margin:0 auto;background:#000;border:2px solid #0f0;border-radius:8px;padding:20px}
        h1{margin:0 0 10px 0}
        pre{white-space:pre-wrap;background:#111;border:1px solid #0f0;border-radius:6px;padding:12px;max-height:65vh;overflow:auto}
        .btns{margin-top:16px}
        a.btn{display:inline-block;margin-right:8px;padding:10px 16px;background:#0f0;color:#000;text-decoration:none;border-radius:4px}
        a.btn:hover{background:#0c0}
    </style>
    </head>
<body>
    <div class="box">
        <h1>🚀 Importar Jogos (BetsAPI)</h1>
        <pre><?php
            try {
                $sync = new SyncJogos();
                $ok = $sync->sync();
                if ($ok) {
                    echo "\n✅ Finalizado com sucesso.\n";
                } else {
                    echo "\n⚠️ Finalizado com avisos.\n";
                }
            } catch (Throwable $e) {
                echo "❌ ERRO: " . $e->getMessage() . "\nArquivo: " . $e->getFile() . "\nLinha: " . $e->getLine() . "\n";
            }
        ?></pre>
        <div class="btns">
            <a class="btn" href="importar-betsapi.php">🔄 Rodar novamente</a>
            <a class="btn" href="./">🏠 Abrir site</a>
            <a class="btn" href="apostar/jogos">🧩 Ver JSON (apostar/jogos)</a>
        </div>
    </div>
</body>
</html>


