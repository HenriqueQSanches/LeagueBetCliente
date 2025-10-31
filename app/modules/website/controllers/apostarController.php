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

    function cotacoes()
    {

        $termos = <<<SQL
SELECT a.sigla, a.titulo AS title, a.campo, a.cor, a.grupo, a.principal
FROM `sis_cotacoes` AS a
WHERE a.status = 1 
ORDER BY a.ordem ASC, a.titulo ASC
SQL;

        return Model::pdoRead()->FullRead($termos)->getResult();
    }

    function jogos(&$datas = [])
    {

        $termos = <<<SQL
SELECT
    a.id,
    a.campeonato AS campeonatoId,
    d.pais,
    d.title AS campeonato,
    b.title AS casa,
    c.title AS fora,
    a.data,
    a.hora,
    a.cotacoes
    
FROM
    `sis_jogos` AS a
    
INNER JOIN
    `sis_times` AS b ON b.id = a.timecasa AND b.status = 1
    
INNER JOIN
    `sis_times` AS c ON c.id = a.timefora AND c.status = 1
    
INNER JOIN
    `sis_campeonatos` AS d ON d.id = a.campeonato AND d.status = 1
    
WHERE 
    a.status = 1 AND a.data >= :hoje
    AND (a.data > :hoje OR a.hora > :hora)
    
ORDER BY
    d.title ASC, a.data ASC, a.hora ASC
SQL;

        $minutos = DadosModel::get()->getMinutosJogo() + 5;
        $time = strtotime("+{$minutos}minutes");

        $places = [
            'hoje' => date('Y-m-d', $time),
            'hora' => date("H:i:s", $time),
        ];

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
            if (!in_array($v['campeonatoId'], $campeonatos))
                $campeonatos[(int)$v['campeonatoId']] = $v['campeonato'];
        }


        if ($paises = array_unique($paises)) {
            $paises = OptionsModel::lista('WHERE a.id IN(' . implode(', ', $paises) . ') ORDER BY a.ordem ASC, a.title ASC LIMIT :limit', [
                'limit' => count($paises),
            ]);
        }

        $i = 0;
        foreach ($campeonatos as $id => $campeonato) {
            $result[$i]['id'] = $id;
            $result[$i]['title'] = $campeonato;
            foreach ($registros as $v) {
                if ((int)$v['campeonatoId'] == $id) {
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
                'title' => 'Outros',
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
                $cotacao = CotacoesModel::getByLabel('campo', $v['cotacao']);

                if (!$jogo instanceof JogoVO) {
                    throw new \Exception("Jogo inválido");
                } else if (!$cotacao instanceof CotacaoVO) {
                    throw new \Exception("Cotação inválida");
                } else if ($cotacao->getStatus() != 1) {
                    throw new \Exception("Cotação foi desativada");
                } else if ($jogo->jaComecou() or $jogo->getStatus() != 1) {
                    throw new \Exception("Jogo `{$jogo->getDescricao()}` não está mais recebendo apostas");
                }

                $valorCotacao = (float)$jogo->getCotacoes(true)[$v['tempo']][$cotacao->getCampo()] ?? 1;

                if ($valorCotacao <= 1) {
                    throw new \Exception("Cotação inválida");
                }

                $apostaJogos[] = ApostaJogosModel::newValueObject([
                    'user' => $user ? $user->getId() : 0,
                    'jogo' => $jogo->getId(),
                    'valor' => $valor,
                    'tempo' => $v['tempo'],
                    'tipo' => $cotacao->getCampo(),
                    'cotacaovalor' => $valorCotacao,
                    'cotacaotitle' => $cotacao->getTitulo(),
                    'cotacaogrupo' => $cotacao->getGrupo(),
                    'cotacaosigla' => $cotacao->getSigla(),
                    'jogos' => count($jogos),
                    'cotacaocampo' => $cotacao->getCampo(),
                ]);

            }

            /** @var ApostaVO $aposta */
            $aposta = ApostasModel::newValueObject();

            $aposta->setValor($valor);
            $aposta->setData(date('Y-m-d'));

            if ($user) {
                $aposta->setUser($user->getId());
                $aposta->setGerente($user->getUser());
                $aposta->setApostadorNome(inputPost('cliente') ?: $user->getNome());
                $aposta->setApostadorTelefone(inputPost('telefone') ?: $user->getTelefone());
                $aposta->setApostadorRevendedor(inputPost('revendedor') ?: $user->getTelefone());
                $aposta->setStatus(ApostasModel::STATUS_ATIVA);
            } else {
                if (!inputPost('cliente'))
                    throw new \Exception("Informe seu nome");
                $aposta->setApostadorNome(inputPost('cliente'));
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
