<?php
include('conexao.php');

echo "<h1>📊 Status da API - LeagueBet</h1>";
echo "<style>
    body { font-family: Arial; padding: 20px; background: #f5f5f5; }
    .box { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .success { color: #4CAF50; font-weight: bold; }
    .error { color: #f44336; font-weight: bold; }
    .info { color: #2196F3; font-weight: bold; }
    .warning { color: #ff9800; font-weight: bold; }
    h1 { color: #333; }
    h3 { color: #666; margin: 0 0 10px 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table th { background: #333; color: white; padding: 10px; text-align: left; }
    table td { padding: 10px; border-bottom: 1px solid #ddd; }
    table tr:hover { background: #f5f5f5; }
    .btn { padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; font-weight: bold; transition: all 0.3s; }
    .btn:hover { opacity: 0.8; transform: translateY(-2px); }
    .btn-blue { background: #2196F3; }
    .btn-orange { background: #ff9800; }
</style>";

try {
    // Total de jogos
    $total_jogos = $conexao->query("SELECT COUNT(*) FROM sis_jogos")->fetchColumn();
    echo "<div class='box'>";
    echo "<h3>🎮 Total de Jogos no Sistema: <span class='info'>$total_jogos</span></h3>";
    echo "</div>";

    // Jogos importados hoje
    $hoje = $conexao->query("SELECT COUNT(*) FROM sis_jogos WHERE DATE(`insert`) = CURDATE()")->fetchColumn();
    echo "<div class='box'>";
    echo "<h3>📅 Jogos Importados Hoje: <span class='success'>$hoje</span></h3>";
    echo "</div>";

    // Jogos disponíveis (futuros)
    $disponiveis = $conexao->query("
        SELECT COUNT(*) FROM sis_jogos 
        WHERE status = 1 
        AND (data > CURDATE() OR (data = CURDATE() AND hora > CURTIME()))
    ")->fetchColumn();
    echo "<div class='box'>";
    echo "<h3>✅ Jogos Disponíveis para Apostar: <span class='success'>$disponiveis</span></h3>";
    echo "</div>";

    // Última importação
    $ultima = $conexao->query("SELECT MAX(`insert`) FROM sis_jogos")->fetchColumn();
    $tempo_desde = '';
    if ($ultima) {
        $diff = time() - strtotime($ultima);
        $horas = floor($diff / 3600);
        $minutos = floor(($diff % 3600) / 60);
        $tempo_desde = " (há {$horas}h {$minutos}min)";
    }
    echo "<div class='box'>";
    echo "<h3>⏰ Última Importação: <span class='info'>$ultima</span> <span class='warning'>$tempo_desde</span></h3>";
    echo "</div>";

    // Total de times
    $times = $conexao->query("SELECT COUNT(*) FROM sis_times")->fetchColumn();
    echo "<div class='box'>";
    echo "<h3>👥 Total de Times Cadastrados: <span class='info'>$times</span></h3>";
    echo "</div>";

    // Total de campeonatos
    $campeonatos = $conexao->query("SELECT COUNT(*) FROM sis_campeonatos")->fetchColumn();
    echo "<div class='box'>";
    echo "<h3>🏆 Total de Campeonatos: <span class='info'>$campeonatos</span></h3>";
    echo "</div>";

    // Total de apostas
    $apostas = $conexao->query("SELECT COUNT(*) FROM sis_apostas")->fetchColumn();
    echo "<div class='box'>";
    echo "<h3>🎫 Total de Apostas Realizadas: <span class='info'>$apostas</span></h3>";
    echo "</div>";

    // Testar API
    echo "<div class='box'>";
    echo "<h3>🔗 Executar Importações Manualmente:</h3>";
    echo "<a href='http://localhost:8000/cron/jogos' target='_blank' class='btn'>🎮 Importar Jogos Agora</a>";
    echo "<a href='http://localhost:8000/cron/jogos/resultados' target='_blank' class='btn btn-blue'>📊 Importar Resultados Agora</a>";
    echo "<a href='status-api.php' class='btn btn-orange'>🔄 Atualizar Página</a>";
    echo "</div>";

    // Últimos 10 jogos importados
    echo "<div class='box'>";
    echo "<h3>📈 Últimos 10 Jogos Importados:</h3>";
    $ultimos = $conexao->query("
        SELECT 
            j.data, 
            j.hora,
            tc.title as casa,
            tf.title as fora,
            c.title as campeonato,
            j.`insert` as importado_em
        FROM sis_jogos j
        LEFT JOIN sis_times tc ON j.timecasa = tc.id
        LEFT JOIN sis_times tf ON j.timefora = tf.id
        LEFT JOIN sis_campeonatos c ON j.campeonato = c.id
        ORDER BY j.`insert` DESC
        LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);

    if ($ultimos) {
        echo "<table>";
        echo "<tr><th>Data</th><th>Hora</th><th>Jogo</th><th>Campeonato</th><th>Importado em</th></tr>";
        foreach ($ultimos as $jogo) {
            $data_formatada = date('d/m/Y', strtotime($jogo['data']));
            $hora_formatada = substr($jogo['hora'], 0, 5);
            echo "<tr>";
            echo "<td>$data_formatada</td>";
            echo "<td>$hora_formatada</td>";
            echo "<td><strong>{$jogo['casa']}</strong> x <strong>{$jogo['fora']}</strong></td>";
            echo "<td>{$jogo['campeonato']}</td>";
            echo "<td>{$jogo['importado_em']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ Nenhum jogo encontrado no banco de dados!</p>";
    }
    echo "</div>";

    // Próximos jogos
    echo "<div class='box'>";
    echo "<h3>🔮 Próximos 5 Jogos Disponíveis:</h3>";
    $proximos = $conexao->query("
        SELECT 
            j.data, 
            j.hora,
            tc.title as casa,
            tf.title as fora,
            c.title as campeonato
        FROM sis_jogos j
        LEFT JOIN sis_times tc ON j.timecasa = tc.id
        LEFT JOIN sis_times tf ON j.timefora = tf.id
        LEFT JOIN sis_campeonatos c ON j.campeonato = c.id
        WHERE j.status = 1 
        AND (j.data > CURDATE() OR (j.data = CURDATE() AND j.hora > CURTIME()))
        ORDER BY j.data ASC, j.hora ASC
        LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    if ($proximos) {
        echo "<table>";
        echo "<tr><th>Data</th><th>Hora</th><th>Jogo</th><th>Campeonato</th></tr>";
        foreach ($proximos as $jogo) {
            $data_formatada = date('d/m/Y', strtotime($jogo['data']));
            $hora_formatada = substr($jogo['hora'], 0, 5);
            echo "<tr>";
            echo "<td>$data_formatada</td>";
            echo "<td>$hora_formatada</td>";
            echo "<td><strong>{$jogo['casa']}</strong> x <strong>{$jogo['fora']}</strong></td>";
            echo "<td>{$jogo['campeonato']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ Nenhum jogo futuro disponível! Execute a importação.</p>";
    }
    echo "</div>";

    // Estatísticas de importação por dia
    echo "<div class='box'>";
    echo "<h3>📊 Estatísticas de Importação (Últimos 7 dias):</h3>";
    $stats = $conexao->query("
        SELECT 
            DATE(`insert`) as data,
            COUNT(*) as total
        FROM sis_jogos
        WHERE DATE(`insert`) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(`insert`)
        ORDER BY data DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    if ($stats) {
        echo "<table>";
        echo "<tr><th>Data</th><th>Total Importado</th></tr>";
        foreach ($stats as $stat) {
            $data_formatada = date('d/m/Y', strtotime($stat['data']));
            echo "<tr>";
            echo "<td>$data_formatada</td>";
            echo "<td><span class='success'>{$stat['total']} jogos</span></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ Sem estatísticas disponíveis!</p>";
    }
    echo "</div>";

} catch (Exception $e) {
    echo "<div class='box'>";
    echo "<h3 class='error'>❌ Erro ao Buscar Dados:</h3>";
    echo "<p class='error'>{$e->getMessage()}</p>";
    echo "</div>";
}

// Footer
echo "<div class='box' style='text-align: center; background: #333; color: white;'>";
echo "<p>🚀 <strong>LeagueBet</strong> - Sistema de Apostas Esportivas</p>";
echo "<p>Desenvolvido por <strong>Henrique Sanches</strong></p>";
echo "<p style='font-size: 12px; opacity: 0.7;'>Última atualização: " . date('d/m/Y H:i:s') . "</p>";
echo "</div>";
?>

