<?php

namespace app\modules\wifiservice\themes\basic\myasset;

use yii\web\AssetBundle;

class LoginAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/wifiservice/themes/basic/static';
    public $css = [
        'css/public.css',
    	'css/login.css',
    ];
    public $js = [
 		'js/buypackage.js',
 		'js/selectPackage.js',
     	'js/login.js',
    	'js/testajax.js',
    ];
    public $depends = [
    	'yii\web\YiiAsset',
    ];
}
