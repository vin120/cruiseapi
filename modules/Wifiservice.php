<?php

namespace app\modules\wifiservice;
use Yii;
use yii\base\Theme;

class Wifiservice extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\wifiservice\controllers';
    public $layout = "@app/modules/wifiservice/themes/basic/views/layouts/main.php";

    public function init()
    {
        parent::init();

        // custom initialization code goes here
        \Yii::$app->view->theme = new Theme([
        	'basePath' => '@app/modules/wifiservice/themes/basic',
            'pathMap' => ['@app/modules/wifiservice/views' => '@app/modules/wifiservice/themes/basic/views'],
            'baseUrl' => '@app/modules/wifiservice/themes/basic',
        ]);
    }
}