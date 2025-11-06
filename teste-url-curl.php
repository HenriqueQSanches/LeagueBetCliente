<?php
echo "<h1>TESTE DE URL COM cURL</h1><hr>";

$url = "http://localhost:8000/apostar/jogos";

echo "<h2>Testando: {$url}</h2>";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $headerSize);
$body = substr($response, $headerSize);

curl_close($ch);

echo "<h3>HTTP Code: {$httpCode}</h3>";
echo "<h3>Headers:</h3>";
echo "<pre>" . htmlspecialchars($headers) . "</pre>";

echo "<h3>Body (primeiros 2000 caracteres):</h3>";
echo "<pre>" . htmlspecialchars(substr($body, 0, 2000)) . "</pre>";

// Tentar decode JSON
$json = json_decode($body, true);
if ($json) {
    echo "<h3 style='color: green;'>✅ É UM JSON VÁLIDO!</h3>";
    
    if (isset($json['paises'])) {
        $totalJogos = 0;
        foreach ($json['paises'] as $pais) {
            if (isset($pais['campeonatos'])) {
                foreach ($pais['campeonatos'] as $camp) {
                    if (isset($camp['jogos'])) {
                        $totalJogos += count($camp['jogos']);
                    }
                }
            }
        }
        echo "<h2 style='color: green;'>🎉 SUCESSO! {$totalJogos} JOGOS ENCONTRADOS!</h2>";
    }
} else {
    echo "<h3 style='color: red;'>❌ NÃO É UM JSON VÁLIDO!</h3>";
    echo "<p>Erro: " . json_last_error_msg() . "</p>";
}
?>

