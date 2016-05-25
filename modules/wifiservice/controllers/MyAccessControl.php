<?php
	namespace app\modules\wifiservice\controllers;
	use yii\filters\AccessControl;

	class MyAccessControl extends AccessControl
	{
		public $user = 'admin';
	}