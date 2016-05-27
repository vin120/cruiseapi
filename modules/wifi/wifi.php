<?php

namespace app\modules\wifi;
use Yii;
use yii\base\Theme;
class wifi extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\wifi\controllers';
	public $layout = "@app/modules/wifi/themes/basic/views/layouts/main.php";
	
    public function init()
    {
        parent::init();

        // custom initialization code goes here
        \Yii::$app->view->theme = new Theme([
        		'basePath' => '@app/modules/wifi/themes/basic',
        		'pathMap' => ['@app/modules/wifi/views' => '@app/modules/wifi/themes/basic/views'],
        		'baseUrl' => '@app/modules/wifi/themes/basic',
        ]);
    }
}
