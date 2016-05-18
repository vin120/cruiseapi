<?php

namespace app\modules\controllers;

use Yii;
use yii\web\Controller;

class WifiController extends Controller
{
	public $layout = false;  	//don't use the default theme layout 
	public $enableCsrfValidation = false; // csrf validation can't work
	
    public function actionIndex()
    {
    	$account = Yii::$app->request->get('account');
       	return $this->render('index',['account'=>$account]);
    }
    
    public function actionConnect()
    {
    	$account = Yii::$app->request->get('account');
    	return $this->render('connect',['account'=>$account]);
    }
}
