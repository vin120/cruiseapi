<?php

namespace app\modules\wificard\controllers;

use yii\web\Controller;

class WifiController extends Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }
    
    
    public function connect()
    {
    	return $this->render('connect');
    }
    
    
    public function disconnect()
    {
    	return $this->render('disconnect');
    }
    
}