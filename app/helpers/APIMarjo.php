<?php

namespace app\helpers;

use app\core\crud\Conn;
use app\models\DadosModel;
use app\models\JogosModel;
use app\vo\JogoVO;
use GuzzleHttp\Client;

class APIMarjo
{

    /**
     * @var Client
     */
    private $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_url' => 'https://apijogos.com/betsports3.php',
            'verify' => false,
        ]);
    }

    public function importarJogos()
    {
        try {

            ini_set('memory_limit', '1024M');
            ini_set('max_execution_time', 0.5 * 60 * 60);

            $response = $this->getJogos();

            if ($response['result'] != 1) {
                throw new \Exception($response['message']);
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
            return $exception;
        }
    }

    /**
     * @return array
     */
    public function getJogos()
    {
        $response = json_decode($this->client->get('/jogos')->getBody()->getContents(), true);
        if (!$response) {
            throw new \Exception("Não foi possível decodificar o retorno da API");
        }

        $limiteCotacao = DadosModel::get()->getLimiteCotacao();

        if ($limiteCotacao > 0) {
            foreach ($response['jogos'] as $index => $jogo) {
                foreach ($jogo['cotacoes'] as $tempo => $cotacoes) {
                    foreach ($cotacoes as $campo => $valor) {
                        $response['jogos'][$index]['cotacoes'][$tempo][$campo] = min($limiteCotacao, Number::float($valor));
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