<?php
// Script para popular a tabela sis_cotacoes com os tipos de apostas

require_once 'conexao.php';

echo "<h1>Populando Tabela sis_cotacoes</h1>";
echo "<hr>";

// Verificar se já existem cotações
$sql_check = "SELECT COUNT(*) as total FROM sis_cotacoes WHERE status = 1";
$result_check = $conexao->query($sql_check)->fetch(PDO::FETCH_ASSOC);

echo "<p><strong>Cotações ativas atualmente:</strong> {$result_check['total']}</p>";

if ($result_check['total'] > 0) {
    echo "<div style='background: #ffffcc; padding: 20px; border: 2px solid #ff9900; border-radius: 5px;'>";
    echo "<h3>⚠️ AVISO</h3>";
    echo "<p>Já existem {$result_check['total']} cotações ativas no banco.</p>";
    echo "<p>Este script irá adicionar NOVAS cotações sem apagar as existentes.</p>";
    echo "</div>";
}

echo "<hr>";
echo "<h3>Inserindo tipos de cotações...</h3>";

// SQL para inserir as cotações principais
$sql = <<<SQL
INSERT INTO `sis_cotacoes` (`id`, `titulo`, `query`, `descricao`, `status`, `ordem`, `cor`, `campo`, `sigla`, `grupo`, `principal`, `taxa`) VALUES
(1, 'Casa', '(jogo.{timecasaplacar} > jogo.{timeforaplacar})', '<p>Quando o time da casa ganha.</p>', 1, 1, '#008040', 'casa', 'CASA', 1, 1, 0.00),
(2, 'Fora', '(jogo.{timecasaplacar} < jogo.{timeforaplacar})', '<p>Quando o time de fora (visitante) ganha.</p>', 1, 3, '#000080', 'fora', 'FORA', 1, 1, 0.00),
(3, 'Empate', '(jogo.{timecasaplacar} = jogo.{timeforaplacar})', '<p>Quando o jogo termina empatado.</p>', 1, 2, '#ff8000', 'empate', 'EMP', 1, 1, 0.00),
(4, 'Duplas Casa', '(jogo.{timecasaplacar} >= jogo.{timeforaplacar})', '<p>Quando o time da casa ganha ou empata.</p>', 1, 4, '#000000', 'dplcasa', 'DPL.C', 2, 0, 0.00),
(5, 'Duplas Fora', '(jogo.{timecasaplacar} <= jogo.{timeforaplacar})', '<p>Quando o time de fora (visitante) ganha ou empata.</p>', 1, 5, '#000000', 'dplfora', 'DPL.F', 2, 0, 0.00),
(6, 'Casa Vence e Fora Marca', 'jogo.{ganhador} = \\'casa\\' AND jogo.{timeforaplacar} > 0', '<p>Quando o time da casa vence, mas o time de fora faz pelo menos 1 gol.</p>', 1, 45, '#000000', 'cvfm', 'CV.FM', 8, 0, 0.00),
(7, 'Ambas Sim', '(jogo.{timecasaplacar} > 0 AND jogo.{timeforaplacar} > 0)', '<p>Quando os dois times marcam no mínimo 1 gol.</p>', 1, 7, '#000000', 'amb', 'AMB', 5, 0, 0.00),
(8, 'Fora Vence e Casa Marca', 'jogo.{ganhador} = \\'fora\\' AND jogo.{timecasaplacar} > 0', '<p>Quando o time de fora vence, mas o time da casa faz pelo menos 1 gol.</p>', 1, 46, '#000000', 'fvcm', 'FV.CM', 8, 0, 0.00),
(9, '1 Gol e Meio Casa', '((jogo.{timecasaplacar} - jogo.{timeforaplacar}) >= 2)', '<p>Time da casa ganha com no mínimo 2 gols de diferença (ex: 2x0, 3x1, 4x2, 4x1).</p>', 1, 27, '#000000', 'casa1gm', '1GM.C', 3, 0, 0.00),
(10, '1 Gol e Meio Fora', '((jogo.{timeforaplacar} - jogo.{timecasaplacar}) >= 2)', '<p>Time de fora ganha com no mínimo 2 gols de diferença (ex: 0x2, 1x3, 2x4, 1x4).</p>', 1, 28, '#000000', 'fora1gm', '1GM.F', 3, 0, 0.00),
(11, '2 Gols e Meio Casa', '((jogo.{timecasaplacar} - jogo.{timeforaplacar}) >= 3)', '<p>Time da casa ganha com no mínimo 3 gols de diferença (ex: 3x0, 4x1, 5x2, 5x1).</p>', 1, 29, '#000000', 'casa2gm', '2GM.C', 3, 0, 0.00),
(12, '2 Gols e Meio Fora', '((jogo.{timeforaplacar} - jogo.{timecasaplacar}) >= 3)', '<p>Time de fora ganha com no mínimo 3 gols de diferença (ex: 0x3, 1x4, 2x5, 1x5).</p>', 1, 30, '#000000', 'fora2gm', '2GM.F', 3, 0, 0.00),
(13, 'Ambas Sim, Sem Empate', '(jogo.{timecasaplacar} > 0 AND jogo.{timeforaplacar} > 0 AND jogo.{timecasaplacar} != jogo.{timeforaplacar})', '<p>Quando os dois times marcam no mínimo 1 gol e a partida não termina empate.</p>', 1, 15, '#000000', 'ambse', 'AMB.SE', 5, 0, 0.00),
(14, 'Ambas Sim, Com Empate', '(jogo.{timecasaplacar} > 0 AND jogo.{timeforaplacar} > 0 AND jogo.{timecasaplacar} = jogo.{timeforaplacar})', '<p>Quando os dois times marcam e o resultado final da partida termina empatado.</p>', 1, 16, '#000000', 'ambnsg', 'AMB.EMP', 5, 0, 0.00),
(15, 'Empate 1x1', '(jogo.{timecasaplacar} = 1 AND jogo.{timeforaplacar} = 1)', '<p>Empate 1x1</p>', 1, 89, '#000000', 'pc1x1', '1x1', 4, 0, 0.00),
(16, 'Casa Vence de Zero', 'jogo.{ganhador} = \\'casa\\' AND jogo.{timeforaplacar} = 0', '<p>Quando o time da casa vence e o time de fora não marca gol.</p>', 1, 43, '#000000', 'cv0', 'CV.0', 8, 0, 0.00),
(17, 'Casa ou Fora', 'jogo.{ganhador} != \\'empate\\'', '<p>Vitória de uma das equipes, ou seja, uma ou outra equipe tem que ganhar, só não pode haver empate.</p>', 1, 6, '#000000', 'cof', 'C.F', 2, 0, 0.00),
(18, 'Fora Vence de Zero', 'jogo.{ganhador} = \\'fora\\' AND jogo.{timecasaplacar} = 0', '<p>Quando o time de fora vence e o time de casa não marca gol.</p>', 1, 44, '#000000', 'fv0', 'FV.0', 8, 0, 0.00),
(19, 'Empate Sem Gol', 'jogo.{totalgols} = 0', '<p>Quando o jogo termina empatado em 0x0.</p>', 1, 48, '#000000', 'empsg', 'EMP.SG', 8, 0, 0.00),
(20, 'Empate Com Gol', 'jogo.{totalgols} > 0 AND jogo.{timecasaplacar} = jogo.{timeforaplacar}', '<p>Quando o jogo é empate com gols (1x1, 2x2, 3x3, 4x4, 5x5, etc).</p>', 1, 47, '#000000', 'empcg', 'EMP.CG', 8, 0, 0.00),
(21, '3 Gols e Meio Fora', '(jogo.{timeforaplacar} - jogo.{timecasaplacar}) >= 4', '<p>Time de fora ganha com no mínimo 4 gols de diferença (ex: 0x4, 1x5, 2x6, 1x6).</p>', 1, 32, '#000000', 'fora3gm', '3GM.F', 3, 0, 0.00),
(22, 'Ambas Não', 'jogo.{timecasaplacar} > 0 XOR jogo.{timeforaplacar} > 0', '<p>Quando somente um dos times marca no mínimo 1 gol.</p>', 1, 8, '#000000', 'ambn', 'AMB.N', 5, 0, 0.00),
(23, 'Empate 2x2', '(jogo.{timecasaplacar} = 2 AND jogo.{timeforaplacar} = 2)', '<p>Quando o jogo termina com o placar exato em 2x2.</p>', 1, 90, '#000000', 'pc2x2', '2x2', 4, 0, 0.00),
(24, 'Placar Ímpar', '(jogo.{totalgols} > 0 AND jogo.{totalgols}%2=1)', '<p>Quando o placar é ímpar (1x0, 2x1, 3x2, 4x3, etc).</p>', 1, 38, '#000000', 'placarimpar', 'P.IMP', 7, 0, 0.00),
(25, 'Placar Par', '(jogo.{totalgols} > 0 AND jogo.{totalgols}%2=0)', '<p>Quando o placar é par (1x1, 2x2, 2x0, etc).</p>', 1, 37, '#000000', 'placarpar', 'P.PAR', 7, 0, 0.00),
(26, '3 Gols e Meio Casa', '((jogo.{timecasaplacar} - jogo.{timeforaplacar}) >= 4)', '<p>Time da casa ganha com no mínimo 4 gols de diferença (ex: 4x0, 5x1, 6x2, 6x1).</p>', 1, 31, '#000000', 'casa3gm', '3GM.C', 3, 0, 0.00),
(27, '+1.5 (2 gols ou mais)', 'jogo.{totalgols} > 1', '<p>2 ou mais gols na partida.</p>', 1, 19, '#000000', 'gmais2', '+1.5', 6, 0, 0.00),
(28, '+2.5 (3 gols ou mais)', 'jogo.{totalgols} > 2', '<p>3 ou mais gols na partida.</p>', 1, 20, '#000000', 'gmais3', '+2.5', 6, 0, 0.00),
(29, '+3.5 (4 gols ou mais)', 'jogo.{totalgols} > 3', '<p>4 ou mais gols na partida.</p>', 1, 21, '#000000', 'gmais4', '+3.5', 6, 0, 0.00),
(30, '+4.5 (5 gols ou mais)', 'jogo.{totalgols} > 4', '<p>5 ou mais gols na partida.</p>', 1, 22, '#000000', 'gmais5', '+4.5', 6, 0, 0.00),
(31, '-1.5 (1 gol ou menos)', 'jogo.{totalgols} <= 1', '<p>1 gol ou menos na partida.</p>', 1, 24, '#000000', 'gmenos2', '-1.5', 6, 0, 0.00),
(32, '-2.5 (2 gols ou menos)', 'jogo.{totalgols} <= 2', '<p>2 gols ou menos na partida.</p>', 1, 25, '#000000', 'gmenos3', '-2.5', 6, 0, 0.00),
(33, '-3.5 (3 gols ou menos)', 'jogo.{totalgols} <= 3', '<p>3 gols ou menos na partida.</p>', 1, 26, '#000000', 'gmenos4', '-3.5', 6, 0, 0.00)
ON DUPLICATE KEY UPDATE
    titulo = VALUES(titulo),
    descricao = VALUES(descricao),
    status = VALUES(status),
    ordem = VALUES(ordem),
    cor = VALUES(cor),
    campo = VALUES(campo),
    sigla = VALUES(sigla),
    grupo = VALUES(grupo),
    principal = VALUES(principal),
    taxa = VALUES(taxa)
SQL;

try {
    $conexao->exec($sql);

    // Novos mercados alinhados ao parser da BetsAPI (dupla chance e over/under genérico)
    $sql2 = <<<SQL
INSERT INTO `sis_cotacoes` (`titulo`, `query`, `descricao`, `status`, `ordem`, `cor`, `campo`, `sigla`, `grupo`, `principal`, `taxa`) VALUES
('Dupla 1X', '', 'Casa ou Empate', 1, 60, '#000000', 'dupla_1x', '1X', 2, 0, 0.00),
('Dupla 12', '', 'Casa ou Fora', 1, 61, '#000000', 'dupla_12', '12', 2, 0, 0.00),
('Dupla X2', '', 'Empate ou Fora', 1, 62, '#000000', 'dupla_x2', 'X2', 2, 0, 0.00),
('Mais 1.5', '', 'Over 1.5 gols', 1, 63, '#000000', 'mais_1_5', '+1.5', 6, 0, 0.00),
('Menos 1.5', '', 'Under 1.5 gols', 1, 64, '#000000', 'menos_1_5', '-1.5', 6, 0, 0.00),
('Mais 2.5', '', 'Over 2.5 gols', 1, 65, '#000000', 'mais_2_5', '+2.5', 6, 0, 0.00),
('Menos 2.5', '', 'Under 2.5 gols', 1, 66, '#000000', 'menos_2_5', '-2.5', 6, 0, 0.00),
('Mais 3.5', '', 'Over 3.5 gols', 1, 67, '#000000', 'mais_3_5', '+3.5', 6, 0, 0.00),
('Menos 3.5', '', 'Under 3.5 gols', 1, 68, '#000000', 'menos_3_5', '-3.5', 6, 0, 0.00),
('Ambas marcam - Sim', '', 'Both teams to score - Yes', 1, 69, '#000000', 'ambas_marcam_sim', 'AMB.S', 5, 0, 0.00),
('Ambas marcam - Não', '', 'Both teams to score - No', 1, 70, '#000000', 'ambas_marcam_nao', 'AMB.N', 5, 0, 0.00)
ON DUPLICATE KEY UPDATE
    status = VALUES(status),
    ordem = VALUES(ordem),
    cor = VALUES(cor),
    grupo = VALUES(grupo),
    principal = VALUES(principal);
SQL;
    $conexao->exec($sql2);
    
    // Contar novamente
    $sql_check2 = "SELECT COUNT(*) as total FROM sis_cotacoes WHERE status = 1";
    $result_check2 = $conexao->query($sql_check2)->fetch(PDO::FETCH_ASSOC);
    
    echo "<div style='background: #ccffcc; padding: 20px; border: 2px solid #00cc00; border-radius: 5px;'>";
    echo "<h3 style='color: #008000;'>✅ SUCESSO!</h3>";
    echo "<p><strong>Cotações inseridas/atualizadas com sucesso!</strong></p>";
    echo "<p>Total de cotações ativas agora: <strong>{$result_check2['total']}</strong></p>";
    echo "</div>";
    
    echo "<hr>";
    echo "<h3>Lista de Cotações Cadastradas:</h3>";
    
    $sql_list = "SELECT id, titulo, sigla, campo, grupo, principal, status FROM sis_cotacoes ORDER BY ordem ASC, titulo ASC";
    $cotacoes = $conexao->query($sql_list)->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr style='background: #333; color: white;'><th>ID</th><th>Título</th><th>Sigla</th><th>Campo</th><th>Grupo</th><th>Principal</th><th>Status</th></tr>";
    
    foreach ($cotacoes as $cot) {
        $statusText = $cot['status'] == 1 ? '<span style="color: green;">✅ Ativo</span>' : '<span style="color: red;">❌ Inativo</span>';
        $principalText = $cot['principal'] == 1 ? '<span style="color: orange;">⭐ Sim</span>' : 'Não';
        
        echo "<tr>";
        echo "<td>{$cot['id']}</td>";
        echo "<td><strong>{$cot['titulo']}</strong></td>";
        echo "<td>{$cot['sigla']}</td>";
        echo "<td><code>{$cot['campo']}</code></td>";
        echo "<td>{$cot['grupo']}</td>";
        echo "<td>{$principalText}</td>";
        echo "<td>{$statusText}</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    
    echo "<hr>";
    echo "<div style='background: #e6f3ff; padding: 20px; border: 2px solid #0066cc; border-radius: 5px;'>";
    echo "<h3 style='color: #0066cc;'>🎉 Próximo Passo</h3>";
    echo "<p>Agora que as cotações foram cadastradas, <strong>os jogos devem aparecer no site!</strong></p>";
    echo "<p>Acesse: <a href='http://localhost:8000' target='_blank' style='color: #0066cc; font-weight: bold;'>http://localhost:8000</a></p>";
    echo "<p>Para verificar o status: <a href='http://localhost:8000/teste-jogos-direto.php' target='_blank' style='color: #0066cc;'>teste-jogos-direto.php</a></p>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='background: #ffcccc; padding: 20px; border: 2px solid #cc0000; border-radius: 5px;'>";
    echo "<h3 style='color: #cc0000;'>❌ ERRO</h3>";
    echo "<p><strong>Erro ao inserir cotações:</strong></p>";
    echo "<p>{$e->getMessage()}</p>";
    echo "</div>";
}
?>

