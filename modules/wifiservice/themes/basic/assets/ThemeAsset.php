<?php
	namespace app\modules\wifiservice\themes\basic\assets;
	use yii\web\AssetBundle;

	class ThemeAsset extends AssetBundle
	{
		public $sourcePath = "@app/modules/wifiservice/themes/basic/static";

		public $css = [
			'css/public.css',
			'css/pages.css',
		];

		public $js=[
			'js/jquery-2.2.2.min.js',
			'js/buypackage.js',
			'js/selectPackage.js',
		];
	}