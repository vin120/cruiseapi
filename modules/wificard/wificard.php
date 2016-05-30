<?php

namespace app\modules\wificard;
use Yii;
use yii\base\Theme;


class wificard extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\wificard\controllers';
    public $layout = "@app/modules/wificard/themes/basic/views/layouts/main.php";
    
    public function init()
    {
        parent::init();

         // custom initialization code goes here
        \Yii::$app->view->theme = new Theme([
        	'basePath' => '@app/modules/wificard/themes/basic',
        	'pathMap' => ['@app/modules/wificard/views' => '@app/modules/wificard/themes/basic/views'],
        	'baseUrl' => '@app/modules/wificard/themes/basic',
        ]);
    }
}
