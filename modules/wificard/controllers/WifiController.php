<?php

namespace app\modules\wificard\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use app\modules\wificard\components\MyCurl;


class WifiController extends Controller
{
	public $enableCsrfValidation = false; // csrf validation can't work
public function actionLogin()
	{
		$card = Yii::$app->request->post('card');
		$password = Yii::$app->request->post('password');
	
// 		$find_res_tmp = "test";
// 		$ip = MyCurl::getIp();
		
// 		$sql = "INSERT INTO vcos_log_tmp (`card`,`password`,`response`,`connect`) VALUES ('$card','$password','$find_res_tmp',$ip)";
// 		Yii::$app->db->createCommand($sql)->execute();
		  
		
		//先查看comst 中有没有这个用户
		$find_res_tmp = MyCurl::FindUser($card);
		//$sql = "INSERT INTO vcos_log_tmp (`card`,`password`,`response`,`connect`) VALUES ('$card','$password','$find_res_tmp','nothing')";
		//Yii::$app->db->createCommand($sql)->execute();
	
		$find_res = json_decode($find_res_tmp,true);
	
		if($find_res['data']){
				
			//连接网络
			$online_json = MyCurl::Connect($card,$password);
			$online_json_trim = str_replace("\r\n\r\n","",$online_json);	//过滤分行
			$online_json_trim = substr($online_json_trim,0,strlen($online_json_trim)-1);	//过滤最后一个分行
// 			$sql = "INSERT INTO vcos_log_tmp (`card`,`password`,`response`,`connect`) VALUES ('$card','$password','$find_res_tmp','$online_json_trim')";
// 			Yii::$app->db->createCommand($sql)->execute();
			$online_arr = json_decode($online_json,true);
				
			if($online_arr['success']){
				//写入数据库
				$active_time = date("Y-m-d H:i:s",time());
				$card_number = $card;
	
				$sql = "SELECT * FROM vcos_card_active_log WHERE card_number='$card'";
				$card_arr = Yii::$app->db->createCommand($sql)->queryAll();
	
				if(!$card_arr){
					//记录激活的卡号和时间，ip等字段
					$sql = "INSERT INTO vcos_card_active_log (`card_number`,`active_time`) VALUES ('$card_number','$active_time')";
					Yii::$app->db->createCommand($sql)->execute();
				}
					
				//正确的
				return Yii::$app->getResponse()->redirect(Url::toRoute(['/wificard/wifi/index',
						'active'=> 0,
						'card'=>$card,]));
			}else{
	
				//错误的
				return Yii::$app->getResponse()->redirect(Url::toRoute(['/wifiservice/site/login',
						'active'=> 0,
						'response'=>$online_json_trim,]));
			}
		}else{
			//第一次用户不存在时
			return Yii::$app->getResponse()->redirect(Url::toRoute(['/wifiservice/site/login',
					'active'=> 0,
					'response'=>'卡号或者密码有误',]));
		}
	}
	
    public function actionIndex()
    {
    	$card = Yii::$app->request->get('card');
    	
    	if(!empty($card)){
    		//查询流量信息
    		$flow_info = MyCurl::CheckFlowAndParse($card);
    		
    		return $this->render('index',['flow_info'=>$flow_info]);
    	}else{
    		return $this->redirect(['/wifiservice/site/login']);
    	}
    	
    }
    
    public function actionDisconnect()
    {
    	$card = Yii::$app->request->post('card');
    	//先查看comst 中有没有这个用户
    	$find_res = MyCurl::FindUser($card);
    	$find_res = json_decode($find_res,true);

    	if($find_res['data']){
    	
    		//查找comst中$passport对应的idRec
    		$idRec = MyCurl::FindidRec($card);
    		 
    		//断开连接网络
    		$disc_json = MyCurl::DisConnect($idRec);
    		 
    		echo $disc_json;
    	}else{
    		echo '{"success":false,"Info":"用户不存在，此帐号没有连接网络，请先连接网络"}';
    	}
    }
    
}