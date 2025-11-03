<?php

namespace app\modules\auth\controllers;

use app\core\Controller;
use app\models\ApostasModel;
use app\models\UsersModel;

class indexController extends Controller
{

    function indexAction()
    {
        // Redirecionar para página de login do admin
        header('Location: /admin-login.php');
        exit;
    }

    function emailAction()
    {
        $this->view('email/aposta-risco', [
            'aposta' => ApostasModel::getByLabel('id', 368),
        ]);
    }

    function logOutAction()
    {
        UsersModel::Instance()->logOut();
        location();
    }

}
