<?php
namespace app\modules\myassets;

use yii\web\AssetBundle;

class MyAsset extends AssetBundle
{

    public $sourcePath = '@app/modules/basic/static';
    public $css = [
        'css/public.css',
    	'css/pages.css',
    ];

    public $js = [
        'js/jquery-2.2.3.min.js',
    	'js/selectPackage.js',
    ];
}