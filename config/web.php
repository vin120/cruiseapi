<?php
$params = require (__DIR__ . '/params.php');

$config = [ 
		'id' => 'basic',
		'basePath' => dirname ( __DIR__ ),
// 		'timeZone'=>'etc/gmt-8',
		'bootstrap' => ['log'],
		'components' => [ 
				'request' => [
						// !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
						'cookieValidationKey' => 'yP-lcOEcDInV9Zdlip8iveQVDRF9se5t' 
				],
				'cache' => [ 
						'class' => 'yii\caching\FileCache' 
				],
				'user' => [ 
						'identityClass' => 'app\models\User',
						'enableAutoLogin' => true, 
						'enableSession' => false
				],
				
				'errorHandler' => [ 
						'errorAction' => 'site/error' 
				],
				'mailer' => [ 
						'class' => 'yii\swiftmailer\Mailer',
						// send all mails to a file by default. You have to set
						// 'useFileTransport' to false and configure a transport
						// for the mailer to send real emails.
						'useFileTransport' => true 
				],
				'log' => [ 
						'traceLevel' => YII_DEBUG ? 3 : 0,
						'targets' => [ 
								[ 
										'class' => 'yii\log\FileTarget',
										'levels' => [ 
												'error',
												'warning' 
										] 
								] 
						] 
				],
				'db' => require (__DIR__ . '/db.php'),
				'mdb' => require (__DIR__ . '/member_db.php'),
				'pdb' => require (__DIR__ . '/product_db.php'),
				
				'urlManager' => [ 
						'enablePrettyUrl' => true,
						'showScriptName' => false,
						'rules' => [ ] 
				] 
		],
		
		'params' => $params 
];

if (YII_ENV_DEV) {
	// configuration adjustments for 'dev' environment
	$config ['bootstrap'] [] = 'debug';
	$config ['modules'] ['debug'] = [ 
			'class' => 'yii\debug\Module' 
	];
	
	$config ['bootstrap'] [] = 'gii';
	$config ['modules'] ['gii'] = [ 
			'class' => 'yii\gii\Module' 
	];
}

return $config;
