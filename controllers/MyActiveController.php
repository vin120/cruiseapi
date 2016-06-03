<?php

namespace app\controllers;


use Yii;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use yii\rest\ActiveController;
use yii\helpers\ArrayHelper;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;


class MyActiveController extends ActiveController
{
    public $modelClass = "";//if not define must be unset defalut action
    public $serializer = [
    		'class' => 'yii\rest\Serializer',
    		'collectionEnvelope' => 'items',
    ];
   
    public function behaviors()
    {   
        $behaviors = parent::behaviors();
//         $behaviors['authenticator'] = [
//         	'class' => HttpBasicAuth::className(),
//         ];
        
        $behaviors['contentNegotiator'] = [ 
				'class' => ContentNegotiator::className (),
				'formats' => [ 
						'application/json' => Response::FORMAT_JSON,
						'application/xml' => Response::FORMAT_XML 
				] 
		];
         $headers=Yii::$app->response->headers;  
  		$headers->add("Access-Control-Allow-Origin","*");  
  		$headers->add("Access-Control-Allow-Headers","Origin, Content-Type, Authorization, Accept,X-Requested-With");
  		$headers->add("Access-Control-Allow-Methods","POST, GET, OPTIONS");
  			
		return $behaviors;
	}
	
	public function behaviors_() {
		return ArrayHelper::merge ( parent::behaviors (), [ 
			'authenticator' => [
				// 这个地方使用`ComopositeAuth` 混合认证
				'class' => CompositeAuth::className (),
				// `authMethods` 中的每一个元素都应该是 一种 认证方式的类或者一个 配置数组
				'authMethods' => [ 
					HttpBasicAuth::className (),
					HttpBearerAuth::className (),
					QueryParamAuth::className () 
				] 
			] 
		] );
	}
	

	public function beforeAction($action)
	{
		if (!parent::beforeAction($action)) {
			return false;
		}

		$response = '{"data":{"status":"403","message":"此功能只在邮轮上开放"}}';
		$_controller = Yii::$app->controller->id;
		$_action = Yii::$app->controller->action->id;
		$permissionName = $_controller.'/'.$_action;
				
		$deny_action = Yii::$app->params['deny_action'];	//在岸上拒绝访问的接口
		$on_cruise = Yii::$app->params['on_cruise'];		//是否在船上
		
		if( !$on_cruise && in_array($permissionName,$deny_action)){
			echo $response;
		}else{
			return true;
		}
		
	}
    
    
	public function actions() {
		$actions = parent::actions ();
		//注销系统自带的实现方法
		unset ($actions ['index'], $actions ['update'], $actions ['create'], $actions ['delete'], $actions ['view']);
		return $actions;
	}
}