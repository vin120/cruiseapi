<?php

namespace app\modules\wifi\controllers;

use yii\web\Controller;

class DefaultController extends Controller
{
    public function actionIndex()
    {
//     	echo date("Ymd H:i:s",time());
        return $this->render('index');
    }
}
