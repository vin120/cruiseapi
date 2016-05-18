<?php

namespace app\modules\controllers;

use Yii;
use yii\web\Controller;
use app\modules\components\MyCurl;


class ServiceController extends Controller
{
	public $enableCsrfValidation = false; // csrf validation can't work
	
	//流量查询
    public function actionCheckoutflow()
    {
    	$account = Yii::$app->request->post('account','abc123');
    	
    	MyCurl::vlogin(Yii::$app->params['wifi_url'],'status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
    	$url = "http://192.168.9.250/jsp/fee_checkout/comstserver.awm";
    
 		$check_out_params = "status=manage&opt=dbcs&subopt=checkout&dbName=usermanage_umb&admin=bisheng&account=".$account;
    	$check_out_json = MyCurl::vpost(Yii::$app->params['wifi_url'],$check_out_params);
    	$check_out_json = iconv('GB2312', 'UTF-8', $check_out_json);
   
    	$check_out_array = json_decode($check_out_json,true);
		
		if($check_out_array['success']){
			$arr = explode("<br>", $check_out_array['data']['feeInfo']);
			$check_out_array['data']['flow'] = $arr[7];
			$json = json_encode($check_out_array);
			echo $json;
			
		}else{
			//出现错误时
			echo $check_out_json;
		}
    }
    
    //wifi连接
    public function actionWificonnect()
    { 
    	MyCurl::vlogin(Yii::$app->params['wifi_url'],'status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);MyCurl::vlogin(Yii::$app->params['wifi_url'],'status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
    	$online_param = 'status=login&opt=login&IsAjaxClient=1&account='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password'];
		$online_json = MyCurl::vpost(Yii::$app->params['wifi_url'],$online_param);
 		$online_json = iconv('GB2312', 'UTF-8', $online_json);
 		echo $online_json;
    }
    
    
    
    //wifi断开连接
    public function actionWifidisconnect()
    {
    	MyCurl::vlogin(Yii::$app->params['wifi_url'],'status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
		$disc_param = 'status=manage&opt=dbcs&subopt=disc&dbName=usermanage_umb&idRec=0';
		$disc_json = MyCurl::vpost(Yii::$app->params['wifi_url'],$disc_param);
		$disc_json = iconv('GB2312', 'UTF-8', $disc_json);
		echo $disc_json;
    }
    
    
    
    
}
