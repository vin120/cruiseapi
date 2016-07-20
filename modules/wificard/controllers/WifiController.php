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
	
		$ip = MyCurl::getIp();
		
		
 		//验证该卡是否已经出售
		$find_card_sell = MyCurl::CheckSell($card);
		$check_sell_and_active = MyCurl::CheckSellAndActive($card);
		// var_dump($check_sell_and_active);die();

		if (empty($check_sell_and_active['sell_date']) || ($check_sell_and_active['is_cancel'] == 1)) {
			//错误的 卡未出售
			return Yii::$app->getResponse()->redirect(Url::toRoute(['/wifiservice/site/login',
					'active'=> 0,
					'response'=> '非法操作（此卡未出售或已取消）']));
		}

		//查看是否存在该用户，验证是否已导入设备
		$find_res_tmp = MyCurl::FindUser($card);

		$find_res = json_decode($find_res_tmp,true);
	
		if($find_res['data']){
			
			//连接网络(本身包含了用户名及密码的验证)
			$online_json = MyCurl::Connect($card,$password);
			$online_json_trim = str_replace("\r\n\r\n","",$online_json);	//过滤分行
			$online_json_trim = str_replace("\n","",$online_json_trim);	//过滤最后一个分行

			$online_arr = json_decode($online_json,true);
				
			if($online_arr['success']){
				//验证该卡是否已激活
				// $find_card_active_log = MyCurl::FindCardActiveLog($card);
				if (empty($check_sell_and_active['active_time'])) {
					//在card_active_log表增加一条记录
					MyCurl::WriteCardActiveLogToDB($card, $ip);
					// echo "111";die();
				}else{
					//如果超过首次登陆的7天，则为失效
					if (strtotime("-7 days") > strtotime($check_sell_and_active['active_time'])) {
						//过期失效
						return Yii::$app->getResponse()->redirect(Url::toRoute(['/wifiservice/site/login',
								'active'=> 0,
								'response'=>'该卡已过期',]));
					}
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
			//卡号不存在时
			return Yii::$app->getResponse()->redirect(Url::toRoute(['/wifiservice/site/login',
					'active'=> 0,
					'response'=>'非法卡号',]));
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