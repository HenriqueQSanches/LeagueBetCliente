<?php

namespace app\helpers;

use app\core\crud\Conn;
use app\models\DadosModel;
use app\models\JogosModel;
use app\vo\JogoVO;

class APIMarjo
{

    /** @var string */
    private $baseUrl = 'https://apijogos.com/betsports3.php';

    public function __construct()
    {
        // Sem dependência de Guzzle: usaremos curl nativo (curl_get)
    }

    public function importarJogos()
    {
        try {

            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', 0.5 * 60 * 60);

            $response = $this->getJogos();

            if (!is_array($response) || ($response['result'] ?? 0) != 1) {
                $msg = $response['message'] ?? 'Resposta inválida da API';
                throw new \Exception($msg);
            }

            $this->atualizaTimes($response['times']);
            $this->atualizaCampeonatos($response['campeonatos']);

            $termos = <<<SQL
INSERT INTO 
    `sis_jogos` (`insert`, `update`, `token`, `campeonato`, `datacadastro`, `data`, `hora`, `timecasa`, `timefora`, `status`, `cotacoes`, `refimport`, `limite1`, `limite2`, `limite3`)

SELECT 
    NOW(), NOW(), :token, campeonato.id, CURDATE(), :data, :hora, timecasa.id, timefora.id, 1, :cotacoes, :refimport, 300, 500, 1000

FROM 
    `sis_times` AS timecasa, `sis_times` AS timefora, `sis_campeonatos` AS campeonato
    
WHERE 
    timecasa.title = :timecasa     
    AND timefora.title = :timefora 
    AND campeonato.title = :campeonato

LIMIT 1

ON DUPLICATE KEY UPDATE 
    `data` = :data, 
    `hora` = :hora, 
    `update` = NOW();     
SQL;

            $jogos = $response['jogos'];

            $prepare = Conn::getConn()->prepare($termos);

            $novos = $antigos = $erros = 0;

            foreach ($jogos as $v) {
                $prepare->execute([
                    'token' => Utils::gerarToken(),
                    'data' => $v['data'],
                    'campeonato' => $v['campeonato'],
                    'timecasa' => $v['timecasa'],
                    'timefora' => $v['timefora'],
                    'hora' => $v['hora'],
                    'cotacoes' => json_encode($v['cotacoes']),
                    'refimport' => $v['refid'],
                ]);

                if ($prepare->rowCount() == 1) {
                    $novos++;
                } else if ($prepare->rowCount() == 2) {
                    $antigos++;
                } else {
                    $erros++;
                }
            }

            return [
                'novos' => $novos,
                'antigos' => $antigos,
                'erros' => $erros,
                'message' => 'Jogos importados',
                'result' => 1,
            ];
        } catch (\Exception $exception) {
            return [
                'result' => 0,
                'message' => $exception->getMessage() ?: 'Erro ao importar jogos',
            ];
        }
    }

    /**
     * @return array
     */
    public function getJogos()
    {
        $base = rtrim($this->baseUrl, '/');
        $candidates = [
            "{$base}/jogos",
            "{$base}",
            "{$base}?action=jogos",
            "{$base}?jogos=1",
        ];
        $lastBody = null;
        $lastHttp = null;
        $lastErr  = null;
        $response = null;
        
        foreach ($candidates as $url) {
            $ch = \curl_init($url);
            \curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_CONNECTTIMEOUT => 20,
                CURLOPT_TIMEOUT => 45,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTPHEADER => ['Accept: application/json'],
            ]);
            $body = \curl_exec($ch);
            $http = \curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err  = \curl_error($ch);
            \curl_close($ch);
            $lastBody = $body;
            $lastHttp = $http;
            $lastErr  = $err;
            
            if ($body !== false && $http === 200) {
                $decoded = json_decode($body, true);
                if (is_array($decoded)) {
                    $response = $decoded;
                    break;
                }
            }
        }
        
        if (!is_array($response)) {
            $snippet = substr((string)$lastBody, 0, 200);
            $hint = $lastErr ?: "HTTP {$lastHttp}";
            throw new \Exception("Não foi possível decodificar o retorno da API ({$hint}): {$snippet}");
        }
        
        // Normaliza estrutura caso a API não envie 'result'
        if (!isset($response['result'])) {
            // tenta mapear listas conhecidas
            $jogos = $response['jogos'] ?? (isset($response[0]) ? $response : []);
            $response = [
                'result' => 1,
                'message' => 'OK',
                'jogos' => is_array($jogos) ? $jogos : [],
                'times' => $response['times'] ?? [],
                'campeonatos' => $response['campeonatos'] ?? [],
            ];
        }

        $limiteCotacao = DadosModel::get()->getLimiteCotacao();

        if ($limiteCotacao > 0 && isset($response['jogos']) && is_array($response['jogos'])) {
            foreach ($response['jogos'] as $index => $jogo) {
                if (!empty($jogo['cotacoes']) && is_array($jogo['cotacoes'])) {
                    foreach ($jogo['cotacoes'] as $tempo => $cotacoes) {
                        foreach ((array)$cotacoes as $campo => $valor) {
                            $response['jogos'][$index]['cotacoes'][$tempo][$campo] = min($limiteCotacao, Number::float($valor));
                        }
                    }
                }
            }
        }

        return $response;
    }

    /**
     * Atualiza a lista de times
     * @param array $times
     */
    private function atualizaTimes(array $times)
    {

        $termos = <<<SQL
INSERT INTO `sis_times` (`insert`, `update`, `token`, `title`, `status`)
SELECT NOW(), NOW(), :token, :time, 1 FROM DUAL
WHERE NOT EXISTS (SELECT b.title FROM `sis_times` AS b WHERE b.title = :time LIMIT 1)
LIMIT 1
SQL;

        $prepare = Conn::getConn()->prepare($termos);

        $total = 0;

        foreach ($times as $time) {
            $prepare->execute([
                'token' => Utils::gerarToken(),
                'time' => $time,
            ]);

            $total += $prepare->rowCount();
        }

    }

    /**
     * @param array $campeonatos
     */
    private function atualizaCampeonatos(array $campeonatos)
    {

        $termos = <<<SQL
INSERT INTO `sis_campeonatos` (`insert`, `update`, `token`, `title`, `status`)
SELECT NOW(), NOW(), :token, :campeonato, 1 FROM DUAL
WHERE NOT EXISTS (SELECT b.title FROM `sis_campeonatos` AS b WHERE b.title = :campeonato LIMIT 1)
LIMIT 1
SQL;

        $prepare = Conn::getConn()->prepare($termos);

        $total = 0;

        foreach ($campeonatos as $nome) {

            $prepare->execute([
                'token' => Utils::gerarToken(),
                'campeonato' => $nome,
            ]);

            $total += $prepare->rowCount();

        }

    }

    /**
     * Importar placares
     * @throws \Exception
     * @return []
     */
   public function importarPlacares()
    {
ini_set('memory_limit', '-1');
       error_reporting(0); 

        $termos = <<<SQL
WHERE 
    a.refimport IS NOT NULL AND a.refimport != '' 
    AND a.status = 1 
    AND (a.data < curdate() OR a.data = curdate() AND a.hora < curtime())
    
ORDER BY 
    a.data ASC, a.hora ASC;
SQL;

        /** @var JogoVO[] $listagem */
        $listagem = JogosModel::lista($termos);

        /** @var JogoVO[] $jogos */
        $jogos = [];

        foreach ($listagem as $jogo) {
            $jogos[$jogo->getRefImport()] = $jogo;
        }

        if (!$jogos) {
            throw new \Exception("Nenhum jogo foi alterado.");
        }

        $totalJogos = count($jogos);
        $totalDefinidos = 0;

        $message = '';

        $url = "http://apijogos.com/resultados/index.php";


    $page_content = file_get_contents($url);
    $response = json_decode($page_content);

      

        foreach ($response as $placar) {

            $jogo = $jogos[$placar->refid];

            if ($jogo instanceof JogoVO) {

                if ($placar->refid == $jogo->getRefImport()) {

                    $jogo->setTimeCasaPlacarPrimeiro($placar->timecasaplacarprimeiro);
                    $jogo->setTimeCasaPlacarSegundo($placar->timecasaplacarsegundo);
                    $jogo->setTimeForaPlacarPrimeiro($placar->timeforaplacarprimeiro);
                    $jogo->setTimeForaPlacarSegundo($placar->timeforaplacarsegundo);
                
                  
                                      
                    
                    JogosModel::definePlacar($jogo);

                    $totalDefinidos++;

                    $message .= "{$jogo->getTimeCasaTitle()} {$jogo->getTimeCasaPlacar()}x{$jogo->getTimeForaPlacar()} {$jogo->getTimeForaTitle()}\n";

                }

                sleep(1);
            }

        }

        return [
            'message' => "{$message}{$totalDefinidos}/{$totalJogos} jogos foram definidos os placares.",
            'result' => 1,
        ];
    }

}