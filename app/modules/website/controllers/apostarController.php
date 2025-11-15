<?php

namespace app\modules\website\controllers;

use app\core\Controller;
use app\core\crud\Conn;
use app\core\Model;
use app\helpers\Date;
use app\helpers\Number;
use app\models\ApostaJogosModel;
use app\models\ApostasModel;
use app\models\CotacoesModel;
use app\models\DadosModel;
use app\models\helpers\OptionsModel;
use app\models\HistoricoBancarioModel;
use app\models\JogosModel;
use app\modules\admin\Admin;
use app\vo\ApostaJogoVO;
use app\vo\ApostaVO;
use app\vo\CotacaoVO;
use app\vo\helpers\OptionVO;
use app\vo\JogoVO;

// Carrega cliente da BetsAPI (classe global)
require_once __DIR__ . '/../../betsapi/BetsAPIClient.php';

class apostarController extends Controller
{

    protected $tpl = 'website/page/apostar';

    function indexAction()
    {

        $config = DadosModel::get();

        $user = Admin::getLogged();

        $graduacao = $user ? $user->voGraduacao() : null;

        $cotacaoMaxima = ($graduacao ? $graduacao->getCotacaoMaxima() : 0) ?: $config->getCotacaoMaxima();
        $cotacaoMinima = ($graduacao ? $graduacao->getCotacaoMinima() : 0) ?: $config->getCotacaoMinima();
        $apostaMinima = ($user ? $user->getApostaMinima() : 0) ?: ($graduacao ? $graduacao->getApostaMinima() : 0) ?: $config->getApostaMinima();
        $apostaMaxima = ($user ? $user->getApostaMaxima() : 0) ?: ($graduacao ? $graduacao->getApostaMaxima() : 0) ?: $config->getApostaMaxima();
        $retornoMaximo = $config->getRetornoMaximo();
        $minJogos = ($user ? $user->getMinJogos() : 0) ?: ($graduacao ? $graduacao->getMinJogos() : 0) ?: $config->getMinJogos();

        $this->view($this->tpl, [
            'cotacaoMaxima' => $cotacaoMaxima ?: 999,
            'cotacaoMinima' => $cotacaoMinima,
            'apostaMinima' => $apostaMinima,
            'apostaMaxima' => $apostaMaxima ?: 9999,
            'retornoMaximo' => $retornoMaximo ?: 9999,
            'condicaoCotacao' => $config->getCondicaoCotacao(true),
            'minJogos' => $minJogos,
            'premioRevenda' => $config->getRevendaPaga(),
            'pageAposta' => true,
        ]);
    }

    function jogosAction()
    {
        $cotacoes = $this->cotacoes();
        $jogos = $this->jogos($datas);

        return [
            'cotacoes' => $cotacoes,
            'grupos' => CotacoesModel::GRUPOS,
            'paises' => $jogos,
            'datas' => array_values($datas),
        ];
    }

    /**
     * Odds em tempo real (Bet365 Prematch v4) para o jogo solicitado.
     * Entrada: GET ?jogo={id} ou ?api_id={betsapi_event_id}
     * Saída: ['result'=>1,'cotacoes'=>{ '90': {campo: valor, ...} }]
     */
    function oddsAction()
    {
        try {
            $apiId = (int)inputGet('api_id');
            $debugFlag = (int)inputGet('debug');
            $debug = [];
            if (!$apiId) {
                $jogoId = (int)inputGet('jogo');
                if ($jogoId > 0) {
                    $row = Model::pdoRead()->FullRead('SELECT api_id FROM `sis_jogos` WHERE id = :id LIMIT 1', [
                        'id' => $jogoId,
                    ])->getResult();
                    $apiId = (int)($row[0]['api_id'] ?? 0);
                    if ($debugFlag) {
                        $debug['from_jogo_id'] = $jogoId;
                        $debug['api_id_db'] = $apiId;
                    }
                }
            }

            if (!$apiId) {
                throw new \Exception('Jogo sem referência de API.');
            }

            $client = new \BetsAPIClient();

            if ($debugFlag) {
                // Inspeciona event/view para tentar descobrir o FI
                try {
                    $view = $client->getEvent($apiId);
                    $debug['event_view_success'] = $view && isset($view['results']) && !empty($view['results']);
                    $fi = null;
                    if (!empty($view['results'][0])) {
                        $r = $view['results'][0];
                        $fi = $r['bet365_id'] ?? ($r['FI'] ?? null);
                        if (!$fi && isset($r['bet365']) && is_array($r['bet365'])) {
                            $fi = $r['bet365']['id'] ?? null;
                        }
                    }
                    $debug['fi'] = $fi ?: null;
                } catch (\Throwable $t) {
                    $debug['event_view_error'] = $t->getMessage();
                }
            }
            // 1) Tenta Bet365 Prematch (v4) para +cotações
            if (isset($fi) && $fi) {
                $mapped = $client->getBet365PrematchMappedOdds($apiId, $fi) ?: [];
            } else {
                $mapped = $client->getBet365PrematchMappedOdds($apiId) ?: [];
            }
            if ($debugFlag) {
                $debug['prematch_count'] = array_sum(array_map('count', $mapped ?: []));
            }
            // 2) Fallback: odds completas do evento (v1) caso não haja FI no mapeamento
            if (empty($mapped)) {
                $fallback = $client->getExtendedOdds($apiId) ?: [];
                $mapped = $fallback;
                if ($debugFlag) {
                    $debug['fallback_count'] = array_sum(array_map('count', $fallback ?: []));
                }
            }

            // 3) Enriquecimento a partir do summary (BTTS, Dupla, etc.) quando disponível
            try {
                $sumMapped = $client->getSummaryMappedOdds($apiId) ?: [];
                foreach ($sumMapped as $tempo => $campos) {
                    if (!isset($mapped[$tempo]) || !is_array($mapped[$tempo])) $mapped[$tempo] = [];
                    foreach ($campos as $campo => $valor) {
                        if ($valor > 1) $mapped[$tempo][$campo] = $valor;
                    }
                }
                if ($debugFlag) {
                    $debug['summary_count'] = array_sum(array_map('count', $sumMapped ?: []));
                }
            } catch (\Throwable $t) {
                if ($debugFlag) $debug['summary_error'] = $t->getMessage();
            }

            $response = [
                'result' => 1,
                'cotacoes' => $mapped,
            ];
            if ($debugFlag) {
                $response['debug'] = $debug;
            }
            return $response;
        } catch (\Exception $e) {
            return [
                'result' => 0,
                'message' => $e->getMessage(),
            ];
        }
    }

    function cotacoes()
    {

        $termos = <<<SQL
SELECT a.sigla, a.titulo AS title, a.campo, a.cor, a.grupo, a.principal
FROM `sis_cotacoes` AS a
WHERE a.status = 1 
ORDER BY a.ordem ASC, a.titulo ASC
SQL;

        $lista = Model::pdoRead()->FullRead($termos)->getResult();

        // Fallback: garantir cotações essenciais mapeadas dos mercados da API
        // para o modal e o contador “+X” funcionarem mesmo sem cadastro no admin.
        $essenciais = [
            // Duplas
            ['sigla' => '1X',   'title' => 'Casa ou Empate',        'campo' => 'dplcasa', 'cor' => '#000000', 'grupo' => 2, 'principal' => '0'],
            ['sigla' => 'X2',   'title' => 'Empate ou Fora',        'campo' => 'dplfora', 'cor' => '#000000', 'grupo' => 2, 'principal' => '0'],
            ['sigla' => '12',   'title' => 'Casa ou Fora',          'campo' => 'cof',     'cor' => '#000000', 'grupo' => 2, 'principal' => '0'],
            // Ambas marcam
            ['sigla' => 'AMB',  'title' => 'Ambas marcam - Sim',    'campo' => 'amb',     'cor' => '#000000', 'grupo' => 5, 'principal' => '0'],
            ['sigla' => 'AMB.N','title' => 'Ambas marcam - Não',    'campo' => 'ambn',    'cor' => '#000000', 'grupo' => 5, 'principal' => '0'],
            // Empate Anula Aposta (Draw No Bet)
            ['sigla' => 'EA1',  'title' => 'Empate anula - Casa',   'campo' => 'empateanulacasa', 'cor' => '#000000', 'grupo' => 2, 'principal' => '0'],
            ['sigla' => 'EA2',  'title' => 'Empate anula - Fora',   'campo' => 'empateanulafora', 'cor' => '#000000', 'grupo' => 2, 'principal' => '0'],
            // Paridade (Odd/Even)
            ['sigla' => 'IMPAR', 'title' => 'Ímpar',                 'campo' => 'impar',     'cor' => '#000000', 'grupo' => 7, 'principal' => '0'],
            ['sigla' => 'PAR',   'title' => 'Par',                   'campo' => 'par',       'cor' => '#000000', 'grupo' => 7, 'principal' => '0'],
            ['sigla' => 'IMPAR.PT', 'title' => 'Ímpar - 1º tempo',   'campo' => 'impar_pt',  'cor' => '#000000', 'grupo' => 7, 'principal' => '0'],
            ['sigla' => 'PAR.PT',   'title' => 'Par - 1º tempo',     'campo' => 'par_pt',    'cor' => '#000000', 'grupo' => 7, 'principal' => '0'],
            ['sigla' => 'IMPAR.ST', 'title' => 'Ímpar - 2º tempo',   'campo' => 'impar_st',  'cor' => '#000000', 'grupo' => 7, 'principal' => '0'],
            ['sigla' => 'PAR.ST',   'title' => 'Par - 2º tempo',     'campo' => 'par_st',    'cor' => '#000000', 'grupo' => 7, 'principal' => '0'],
            // First Team to Score
            ['sigla' => 'FTS1',  'title' => 'Primeiro a marcar - Casa', 'campo' => 'fts_casa', 'cor' => '#000000', 'grupo' => 8, 'principal' => '0'],
            ['sigla' => 'FTS2',  'title' => 'Primeiro a marcar - Fora', 'campo' => 'fts_fora', 'cor' => '#000000', 'grupo' => 8, 'principal' => '0'],
            ['sigla' => 'FTSNG', 'title' => 'Sem gols',                 'campo' => 'fts_ng',   'cor' => '#000000', 'grupo' => 8, 'principal' => '0'],
            // Intervalo / Final (HT/FT)
            ['sigla' => '1-1', 'title' => 'HT/FT Casa-Casa',           'campo' => 'htft_1_1', 'cor' => '#000000', 'grupo' => 9, 'principal' => '0'],
            ['sigla' => '1-X', 'title' => 'HT/FT Casa-Empate',         'campo' => 'htft_1_x', 'cor' => '#000000', 'grupo' => 9, 'principal' => '0'],
            ['sigla' => '1-2', 'title' => 'HT/FT Casa-Fora',           'campo' => 'htft_1_2', 'cor' => '#000000', 'grupo' => 9, 'principal' => '0'],
            ['sigla' => 'X-1', 'title' => 'HT/FT Empate-Casa',         'campo' => 'htft_x_1', 'cor' => '#000000', 'grupo' => 9, 'principal' => '0'],
            ['sigla' => 'X-X', 'title' => 'HT/FT Empate-Empate',       'campo' => 'htft_x_x', 'cor' => '#000000', 'grupo' => 9, 'principal' => '0'],
            ['sigla' => 'X-2', 'title' => 'HT/FT Empate-Fora',         'campo' => 'htft_x_2', 'cor' => '#000000', 'grupo' => 9, 'principal' => '0'],
            ['sigla' => '2-1', 'title' => 'HT/FT Fora-Casa',           'campo' => 'htft_2_1', 'cor' => '#000000', 'grupo' => 9, 'principal' => '0'],
            ['sigla' => '2-X', 'title' => 'HT/FT Fora-Empate',         'campo' => 'htft_2_x', 'cor' => '#000000', 'grupo' => 9, 'principal' => '0'],
            ['sigla' => '2-2', 'title' => 'HT/FT Fora-Fora',           'campo' => 'htft_2_2', 'cor' => '#000000', 'grupo' => 9, 'principal' => '0'],
            // Over/Under
            ['sigla' => 'M1.5', 'title' => 'Mais de 1.5',           'campo' => 'mais_1_5','cor' => '#000000', 'grupo' => 3, 'principal' => '0'],
            ['sigla' => 'M2.5', 'title' => 'Mais de 2.5',           'campo' => 'mais_2_5','cor' => '#000000', 'grupo' => 3, 'principal' => '0'],
            ['sigla' => 'M3.5', 'title' => 'Mais de 3.5',           'campo' => 'mais_3_5','cor' => '#000000', 'grupo' => 3, 'principal' => '0'],
            ['sigla' => 'N1.5', 'title' => 'Menos de 1.5',          'campo' => 'menos_1_5','cor' => '#000000','grupo' => 3, 'principal' => '0'],
            ['sigla' => 'N2.5', 'title' => 'Menos de 2.5',          'campo' => 'menos_2_5','cor' => '#000000','grupo' => 3, 'principal' => '0'],
            ['sigla' => 'N3.5', 'title' => 'Menos de 3.5',          'campo' => 'menos_3_5','cor' => '#000000','grupo' => 3, 'principal' => '0'],
        ];

        // Fallback: Escanteios e Cartões (linhas padrão)
        // chaves compatíveis com o import (esc_{linha}_{tipo}, cart_{linha}_{tipo})
        $linhasEsc = ['8_5','9_5','10_5','11_5','12_5'];
        foreach ($linhasEsc as $ln) {
            $v = str_replace('_', '.', $ln);
            $essenciais[] = ['sigla' => "ESC+$v", 'title' => "Escanteios - Mais de {$v}",  'campo' => "esc_{$ln}_mais",  'cor' => '#000000', 'grupo' => 10, 'principal' => '0'];
            $essenciais[] = ['sigla' => "ESC-$v", 'title' => "Escanteios - Menos de {$v}", 'campo' => "esc_{$ln}_menos", 'cor' => '#000000', 'grupo' => 10, 'principal' => '0'];
        }

        $linhasCart = ['3_5','4_5','5_5'];
        foreach ($linhasCart as $ln) {
            $v = str_replace('_', '.', $ln);
            $essenciais[] = ['sigla' => "CAR+$v", 'title' => "Cartões - Mais de {$v}",  'campo' => "cart_{$ln}_mais",  'cor' => '#000000', 'grupo' => 11, 'principal' => '0'];
            $essenciais[] = ['sigla' => "CAR-$v", 'title' => "Cartões - Menos de {$v}", 'campo' => "cart_{$ln}_menos", 'cor' => '#000000', 'grupo' => 11, 'principal' => '0'];
        }

        $camposAtuais = array_column($lista, 'campo');
        foreach ($essenciais as $e) {
            if (!in_array($e['campo'], $camposAtuais, true)) {
                $lista[] = $e;
            }
        }

        return $lista;
    }

    function jogos(&$datas = [])
    {

        $termos = <<<SQL
SELECT
    a.id,
    a.campeonato AS campeonatoId,
    COALESCE(d.pais, 0) AS pais,
    COALESCE(d.title, a.campeonato) AS campeonato,
    COALESCE(b.title, a.timecasa) AS casa,
    COALESCE(c.title, a.timefora) AS fora,
    a.data,
    a.hora,
    a.cotacoes,
    a.api_id
    
FROM
    `sis_jogos` AS a
    
LEFT JOIN
    `sis_times` AS b ON b.id = a.timecasa AND b.status = 1
    
LEFT JOIN
    `sis_times` AS c ON c.id = a.timefora AND c.status = 1
    
LEFT JOIN
    `sis_campeonatos` AS d ON d.id = a.campeonato AND d.status = 1
    
WHERE 
    a.ativo = '1' AND a.data >= :hoje
    AND (a.data > :hoje OR a.hora > :hora)
    AND (:pais IS NULL OR d.pais = :pais)
    
ORDER BY
    COALESCE(d.title, a.campeonato) ASC, a.data ASC, a.hora ASC
SQL;

        $minutos = DadosModel::get()->getMinutosJogo() + 5;
        $time = strtotime("+{$minutos}minutes");

        $places = [
            'hoje' => date('Y-m-d', $time),
            'hora' => date("H:i:s", $time),
            'pais' => null,
        ];

        // Filtro opcional por país – somente ID numérico é aceito (ignora 'BR'/'Brasil')
        $paisParam = trim((string)inputGet('pais'));
        if ($paisParam !== '') {
            if (is_numeric($paisParam)) {
                $places['pais'] = (int)$paisParam;
            } // caso contrário, ignora o filtro
        }

        $hoje = date('Y-m-d');
        $amanha = date('Y-m-d', strtotime('+1day'));

        $paises = [];
        $campeonatos = [];
        $result = [];
        $registros = Model::pdoRead()->FullRead($termos, $places)->getResult();

        foreach ($registros as $i => $v) {

            iF (!isset($datas[$v['data']])) {
                $dt = date('d/m', strtotime($v['data']));
                $datas[$v['data']] = [
                    'data' => $v['data'],
                    'title' => ($v['data'] == $hoje ? 'Jogos de hoje ' : ($v['data'] == $amanha ? 'Jogo de amanhã ' : 'Jogos de ')) . $dt,
                ];
            }

            $paises[] = $v['pais'];
            $registros[$i]['dateTime'] = date('c', strtotime("{$v['data']} {$v['hora']}"));
            $registros[$i]['cotacoes'] = $v['cotacoes'] = json_decode($v['cotacoes'], true);
            
            // Cria chave única para o campeonato (ID ou nome)
            $campeonatoKey = is_numeric($v['campeonatoId']) ? (int)$v['campeonatoId'] : 'api_' . md5($v['campeonato']);
            if (!isset($campeonatos[$campeonatoKey])) {
                $campeonatos[$campeonatoKey] = $v['campeonato'];
                $registros[$i]['campeonatoKey'] = $campeonatoKey;
            } else {
                $registros[$i]['campeonatoKey'] = $campeonatoKey;
            }
        }


        if ($paises = array_unique($paises)) {
            $paises = OptionsModel::lista('WHERE a.id IN(' . implode(', ', $paises) . ') ORDER BY a.ordem ASC, a.title ASC LIMIT :limit', [
                'limit' => count($paises),
            ]);
        }

        $i = 0;
        foreach ($campeonatos as $campeonatoKey => $campeonato) {
            $result[$i]['id'] = $campeonatoKey;
            $result[$i]['title'] = $campeonato;
            foreach ($registros as $v) {
                if ($v['campeonatoKey'] == $campeonatoKey) {
                    $result[$i]['pais'] = (int)$v['pais'];
                    $result[$i]['jogos'][] = $v;
                }
            }
            $i++;
        }

        $resultPaises = [];

        /** @var OptionVO $pais */
        foreach ($paises as $pais) {

            $dados = [
                'id' => $pais->getId(),
                'title' => $pais->getTitle(),
                'img' => $pais->imgCapa()->getSource(true),
                'campeonatos' => [],
            ];

            foreach ($result as $i => $c) {
                if ($c['pais'] == $pais->getId()) {
                    $dados['campeonatos'][] = $c;
                    unset($result[$i]);
                }
            }

            $resultPaises[] = $dados;
        }

        if ($result) {

            $dados = [
                'id' => 0,
                'title' => 'Internacional / Outros',
                'img' => source_images('default.jpg'),
                'campeonatos' => []
            ];

            foreach ($result as $i => $v) {
                $dados['campeonatos'][] = $v;
                unset($result[$i]);
            }

            $resultPaises[] = $dados;
        }

        ksort($datas);

        return $resultPaises;

    }

    function imprimirTabelaAction()
    {

        $places = [
            'disponivel' => 1,
            'data' => Date::data(inputGet('data')),
            'search' => trim(inputGet('search')) ?: null,
            'campeonato' => inputGet('campeonato') > 0 ? inputGet('campeonato') : null,
        ];

        $busca = JogosModel::busca($places, 1, 999);

        $campeonatos = [];

        /** @var JogoVO $v */
        foreach ($busca->getRegistros() as $v) {
            if (empty($campeonatos[$v->getCampeonato()])) {
                $campeonatos[$v->getCampeonato()] = $v->getCampeonatoTitle();
            }
        }

        $this->view('website/page/apostar-imprimir-tabela', [
            'campeonatos' => $campeonatos,
            'jogos' => $busca->getRegistros(),
        ]);

    }

    function imprimirAction()
    {
        try {
            $aposta = ApostasModel::getByLabel('token', url_parans(0));

            if (!$aposta instanceof ApostaVO) {
                throw new \Exception('Aposta inválida');
            }

            $this->view('website/page/pre-bilhete', [
                'aposta' => $aposta,
            ]);

        } catch (\Exception $ex) {
            echo $ex->getMessage();
        }
    }

    function apostarAction()
    {
        try {

            Conn::startTransaction();

            $user = Admin::getLogged();

            $valor = Number::float(inputPost('valor'));
            $jogos = inputPost('jogos') ?: [];
            $dados = DadosModel::get();

            /** @var ApostaJogoVO[] $apostaJogos */
            $apostaJogos = [];

            foreach ($jogos as $v) {

                $jogo = JogosModel::getByLabel('id', $v['jogo']);
                $campoCotacao = (string)$v['cotacao'];
                $cotacao = CotacoesModel::getByLabel('campo', $campoCotacao);
                $cotacaoInfo = null;
                $isDynamic = false;

                if (!$jogo instanceof JogoVO) {
                    throw new \Exception("Jogo inválido");
                }

                // Suporte a mercados dinâmicos não cadastrados (ex.: placar exato cs_h_a)
                if (!$cotacao instanceof CotacaoVO) {
                    // Aceitar mercados dinâmicos em geral (desde que exista odd > 1)
                    $isDynamic = true;
                    // Tratamento especial para Placar Exato
                    if (preg_match('/^cs_(\d+)_(\d+)$/', $campoCotacao, $m)) {
                        $h = (int)$m[1];
                        $a = (int)$m[2];
                        $cotacaoInfo = [
                            'campo' => $campoCotacao,
                            'titulo' => "Resultado exato {$h}:{$a}",
                            'grupo' => 4,
                            'sigla' => "CS {$h}:{$a}",
                            'principal' => '0',
                        ];
                    } else {
                        // Humaniza título básico para outros campos (impar, dplcasa, mais_2_5, esc_10_5_mais etc.)
                        $titulo = strtoupper(str_replace(['_PT','_ST'], [' 1ºT',' 2ºT'], str_replace('_', ' ', $campoCotacao)));
                        $cotacaoInfo = [
                            'campo' => $campoCotacao,
                            'titulo' => $titulo,
                            'grupo' => 0,
                            'sigla' => strtoupper(substr($campoCotacao, 0, 8)),
                            'principal' => '0',
                        ];
                    }
                } else if ($cotacao->getStatus() != 1) {
                    throw new \Exception("Cotação foi desativada");
                } else {
                    // Permitir apostas em jogos AO VIVO
                    $resultAoVivo = Model::pdoRead()->FullRead('SELECT ao_vivo FROM `sis_jogos` WHERE id = :id LIMIT 1', [
                        'id' => $jogo->getId(),
                    ])->getResult();
                    $aoVivo = (int)($resultAoVivo[0]['ao_vivo'] ?? 0);

                    if ((($jogo->jaComecou() && !$aoVivo)) || $jogo->getStatus() != 1) {
                        throw new \Exception("Jogo `{$jogo->getDescricao()}` não está mais recebendo apostas");
                    }
                }

                // Chave do tempo no JSON é string ("90","pt","st"); normalizar
                $tempoKey = (string)$v['tempo'];
                $campoBuscado = $cotacao instanceof CotacaoVO ? $cotacao->getCampo() : $campoCotacao;
                // Aceita valor enviado pelo cliente (para cenários em que o mapeamento não está persistido no DB),
                // mas valida/faz fallback via API se vier vazio/<=1
                $valorCotacao = (float)($v['valor'] ?? 0);
                if ($valorCotacao <= 1) {
                    $valorCotacao = (float)($jogo->getCotacoes(true)[$tempoKey][$campoBuscado] ?? 1);
                }

                if ($valorCotacao <= 1) {
                    // Fallback: buscar valor da API em tempo real
                    try {
                        // Carrega cliente e busca mapeamento
                        require_once __DIR__ . '/../../betsapi/BetsAPIClient.php';
                        $row = Model::pdoRead()->FullRead('SELECT api_id FROM `sis_jogos` WHERE id = :id LIMIT 1', [
                            'id' => $jogo->getId(),
                        ])->getResult();
                        $apiId = (int)($row[0]['api_id'] ?? 0);
                        if ($apiId) {
                            $client = new \BetsAPIClient();
                            $mapped = $client->getBet365PrematchMappedOdds($apiId) ?: [];
                            if (empty($mapped)) {
                                $mapped = $client->getExtendedOdds($apiId) ?: [];
                            }
                            $valorCotacao = (float)($mapped[$tempoKey][$campoBuscado] ?? 1);
                        }
                    } catch (\Throwable $t) {
                        // silencioso; se continuar inválido, cai no erro abaixo
                    }
                }

                if ($valorCotacao <= 1) {
                    throw new \Exception("Cotação inválida");
                }

                $apostaJogos[] = ApostaJogosModel::newValueObject([
                    'User' => $user ? $user->getId() : 0,
                    'Jogo' => $jogo->getId(),
                    'Valor' => $valor,
                    'Tempo' => $v['tempo'],
                    'Tipo' => $campoBuscado,
                    'CotacaoValor' => $valorCotacao,
                    'CotacaoTitle' => $cotacao instanceof CotacaoVO ? $cotacao->getTitulo() : ($cotacaoInfo['titulo'] ?? ''),
                    'CotacaoGrupo' => $cotacao instanceof CotacaoVO ? $cotacao->getGrupo() : ($cotacaoInfo['grupo'] ?? 0),
                    'CotacaoSigla' => $cotacao instanceof CotacaoVO ? $cotacao->getSigla() : ($cotacaoInfo['sigla'] ?? ''),
                    'Jogos' => count($jogos),
                    'CotacaoCampo' => $campoBuscado,
                ]);

            }

            /** @var ApostaVO $aposta */
            $aposta = ApostasModel::newValueObject();

            $aposta->setValor($valor);
            $aposta->setData(date('Y-m-d'));

            if ($user) {
                $aposta->setUser($user->getId());
                $aposta->setGerente($user->getUser());
                $aposta->setApostadorNome($user->getNome());
                $aposta->setApostadorTelefone(inputPost('telefone') ?: $user->getTelefone());
                $aposta->setApostadorRevendedor(inputPost('revendedor') ?: $user->getTelefone());
                $aposta->setStatus(ApostasModel::STATUS_ATIVA);
            } else {
                // Campo cliente removido - usar nome genérico
                $aposta->setApostadorNome('Cliente');
                $aposta->setApostadorTelefone(inputPost('telefone'));
                $aposta->setApostadorRevendedor(inputPost('revendedor'));
                $aposta->setStatus(ApostasModel::STATUS_AGUARDANDO_PAGAMENTO);
            }

            ApostasModel::apostar($aposta, $apostaJogos);

            if ($user) {
                HistoricoBancarioModel::add($user, -$valor, "Pagamento de aposta #{$aposta->getId()}", $aposta, 'aposta');
                ApostasModel::pagarComissoes($aposta);
            }

            Conn::commit();

            $urlBilhete = url('apostas/bilhete', [$aposta->getToken()]);

            return [
                'message' => 'Aposta realizada com sucesso',
                'codigo' => $aposta->getCodigoBilhete(),
                'share' => 'https://api.whatsapp.com/send?' . http_build_query(['text' => $urlBilhete]),
                'link' => $user ? $urlBilhete : url('apostar/imprimir', [$aposta->getToken()]),
                'saldo' => $user ? $user->getCredito() : 0,
                'result' => 1,
            ];
        } catch (\Exception $ex) {
            Conn::rollBack();
            return $ex;
        }
    }

    function dateAction()
    {

        echo date("c");

    }

}
