<?php

namespace app\modules\website\controllers;

use app\core\Controller;
use app\core\Model;
use app\models\CotacoesModel;
use app\models\DadosModel;
use app\models\helpers\OptionsModel;
use app\modules\admin\Admin;

class aovivoController extends Controller
{

    protected $tpl = 'website/page/aovivo';

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
            'pageAoVivo' => true,
            'premioRevenda' => $config->getRevendaPaga(),
            'cotacaoMaxima' => $cotacaoMaxima ?: 999,
            'cotacaoMinima' => $cotacaoMinima,
            'apostaMinima' => $apostaMinima,
            'apostaMaxima' => $apostaMaxima ?: 9999,
            'retornoMaximo' => $retornoMaximo ?: 9999,
            'condicaoCotacao' => $config->getCondicaoCotacao(true),
            'minJogos' => $minJogos,
        ]);
    }

    function jogosAction()
    {
        $cotacoes = $this->cotacoes();
        $jogos = $this->jogosAoVivo();

        return [
            'cotacoes' => $cotacoes,
            'grupos' => CotacoesModel::GRUPOS,
            'paises' => $jogos,
        ];
    }

    private function cotacoes()
    {
        $termos = <<<SQL
SELECT a.sigla, a.titulo AS title, a.campo, a.cor, a.grupo, a.principal
FROM `sis_cotacoes` AS a
WHERE a.status = 1 
ORDER BY a.ordem ASC, a.titulo ASC
SQL;

        $lista = Model::pdoRead()->FullRead($termos)->getResult();

        // Fallback: garantir cotações essenciais mapeadas
        $essenciais = [
            ['sigla' => '1X',   'title' => 'Casa ou Empate',        'campo' => 'dplcasa', 'cor' => '#000000', 'grupo' => 2, 'principal' => '0'],
            ['sigla' => 'X2',   'title' => 'Empate ou Fora',        'campo' => 'dplfora', 'cor' => '#000000', 'grupo' => 2, 'principal' => '0'],
            ['sigla' => '12',   'title' => 'Casa ou Fora',          'campo' => 'cof',     'cor' => '#000000', 'grupo' => 2, 'principal' => '0'],
            ['sigla' => 'AMB',  'title' => 'Ambas marcam - Sim',    'campo' => 'amb',     'cor' => '#000000', 'grupo' => 5, 'principal' => '0'],
            ['sigla' => 'AMB.N','title' => 'Ambas marcam - Não',    'campo' => 'ambn',    'cor' => '#000000', 'grupo' => 5, 'principal' => '0'],
            ['sigla' => 'M1.5', 'title' => 'Mais de 1.5',           'campo' => 'mais_1_5','cor' => '#000000', 'grupo' => 3, 'principal' => '0'],
            ['sigla' => 'M2.5', 'title' => 'Mais de 2.5',           'campo' => 'mais_2_5','cor' => '#000000', 'grupo' => 3, 'principal' => '0'],
            ['sigla' => 'M3.5', 'title' => 'Mais de 3.5',           'campo' => 'mais_3_5','cor' => '#000000', 'grupo' => 3, 'principal' => '0'],
            ['sigla' => 'N1.5', 'title' => 'Menos de 1.5',          'campo' => 'menos_1_5','cor' => '#000000','grupo' => 3, 'principal' => '0'],
            ['sigla' => 'N2.5', 'title' => 'Menos de 2.5',          'campo' => 'menos_2_5','cor' => '#000000','grupo' => 3, 'principal' => '0'],
            ['sigla' => 'N3.5', 'title' => 'Menos de 3.5',          'campo' => 'menos_3_5','cor' => '#000000','grupo' => 3, 'principal' => '0'],
        ];

        $camposAtuais = array_column($lista, 'campo');
        foreach ($essenciais as $e) {
            if (!in_array($e['campo'], $camposAtuais, true)) {
                $lista[] = $e;
            }
        }

        return $lista;
    }

    private function jogosAoVivo()
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
    a.api_id,
    a.placar_casa,
    a.placar_fora
FROM
    `sis_jogos` AS a
LEFT JOIN
    `sis_times` AS b ON b.id = a.timecasa AND b.status = 1
LEFT JOIN
    `sis_times` AS c ON c.id = a.timefora AND c.status = 1
LEFT JOIN
    `sis_campeonatos` AS d ON d.id = a.campeonato AND d.status = 1
WHERE 
    a.ativo = '1' AND a.ao_vivo = 1
    AND CONCAT(a.data, ' ', a.hora) >= DATE_SUB(NOW(), INTERVAL 8 HOUR)
ORDER BY
    COALESCE(d.title, a.campeonato) ASC, a.data DESC, a.hora DESC
SQL;

        $paises = [];
        $campeonatos = [];
        $result = [];
        $registros = Model::pdoRead()->FullRead($termos)->getResult();

        foreach ($registros as $i => $v) {
            $paises[] = (int)$v['pais'];
            $registros[$i]['dateTime'] = date('c', strtotime("{$v['data']} {$v['hora']}"));
            $registros[$i]['cotacoes'] = $v['cotacoes'] = json_decode($v['cotacoes'], true);
            $registros[$i]['placar'] = [
                'casa' => isset($v['placar_casa']) ? (int)$v['placar_casa'] : null,
                'fora' => isset($v['placar_fora']) ? (int)$v['placar_fora'] : null,
            ];

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

        return $resultPaises;
    }
}


