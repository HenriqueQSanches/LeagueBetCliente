<?php

namespace app\modules\website\controllers\financeiro;

use app\core\Controller;
use app\models\financeiro\DepositosModel;
use app\models\helpers\ArquivosModel;
use app\modules\admin\Admin;

class depositoController extends seguranca
{

    protected $view = 'website/financeiro/deposito';

    function indexAction()
    {
        $this->view($this->view);
    }

    function insertAction()
    {
        try {

            if (empty($_FILES['upfile'])) {
                throw new \Exception("Anexe o comprovante de depósito");
            }

            $deposito = DepositosModel::newValueObject([
                'user' => Admin::getIdLogged(),
                'valor' => inputPost('valor'),
            ])->save();

            ArquivosModel::addArquivo($deposito, 'upfile');

            return [
                'message' => "Depósito enviado com sucesso.\nAssim que for confirmado será liberado os créditos.",
                'result' => 1,
            ];
        } catch (\Exception $ex) {
            return $ex;
        }
    }

    function listAction()
    {
        $parans = inputPost() ?: [];
        $parans += ['page' => 1, 'forpage' => 20];
        $parans['user'] = Admin::getIdLogged();
        $busca = DepositosModel::busca($parans, $parans['page'], $parans['forpage']);
        $this->view($this->view, [
            'busca' => $busca,
        ], 'list');
    }

}