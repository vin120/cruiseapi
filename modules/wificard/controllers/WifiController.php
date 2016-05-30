<?php

namespace app\modules\wificard\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use app\modules\wificard\components\MyCurl;


class WifiController extends Controller
{
	public function actionLogin()
	{
		$card = Yii::$app->request->post('card');
		$password = Yii::$app->request->post('password');
		
		//先查看comst 中有没有这个用户
		$find_res = MyCurl::FindUser($card);
		$find_res = json_decode($find_res,true);
		if($find_res['data']){
			
			//连接网络
			$online_json = MyCurl::Connect($card,$password);
			$online_arr = json_decode($online_json,true);
			if($online_arr['success']){
				//写入数据库 TODO
				$active_time = date("Y-m-d H:i:s",time());
				$card_number = $card;
				
				$sql = "SELECT * FROM vcos_card_active_log WHERE card_number='$card'";
				$card = Yii::$app->db->createCommand($sql)->queryAll();
				
				if(!$card){
					//记录激活的卡号和时间，ip等字段
					$sql = "INSERT INTO vcos_card_active_log (`card_number`,`active_time`) VALUES ('$card_number','$active_time')";
					Yii::$app->db->createCommand($sql)->execute();
				}
			
				$flow_info['data'] = ['Code'=>1];
			}else{
				$flow_info['error'] = ['errorCode'=>2];
			}
		}else{
			//第一次用户不存在时
			$flow_info['error'] = ['errorCode'=>1];
		}
		
		$tmp = json_encode($flow_info);
		
		echo $tmp;
	}
	
	
    public function actionIndex()
    {
    	$card = Yii::$app->request->get('card');
    	
    	//查询流量信息
		$flow_info = MyCurl::CheckFlowAndParse($card);
		
        return $this->render('index',['flow_info'=>$flow_info]);
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