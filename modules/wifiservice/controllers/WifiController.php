<?php 
	namespace app\modules\wifiservice\controllers;

	use Yii;
	use yii\web\Controller;
	use app\components\MemberService;
	use app\modules\wifiservice\components\MyCurl;
	use app\modules\wifiservice\components\MyWifi;
	use app\models\Member;


	class WifiController extends Controller
	{
		// public $enableCsrfValidation = false;
		
		//上网购买页面
		public function actionIndex()
		{
			$mcode = Yii::$app->request->get('mcode');

			$member = Member::find ()->select ( [ 
					'sign',
			] )->where ( [ 
					'member_code' => $mcode 
			] )->one ();

			if(!$member){
				return $this->render('error',['mcode'=>$mcode]);
				die();
			}
			
			
			$sign = $member['sign'];
			$membership = MemberService::getMemberbysign($sign);

			$wifi_items = MyWifi::FindWifiService();

			// var_dump($membership);
			return $this->render('index',['membership'=>$membership,'wifi_items'=>$wifi_items,'mcode'=>$mcode]);


			
			//邮轮通接口调用
			//需要参数 ：空
			// $response = [];
			// $wifi_items = MyWifi::FindWifiService();
			// $response['data'] = ['code'=>1,'wifi_items'=>$wifi_items];
			// return $response;


			//返回值
			//array(1) { ["data"]=> array(2) { ["code"]=> int(1) ["wifi_items"]=> array(3) { [0]=> array(4) { ["wifi_id"]=> string(1) "1" ["sale_price"]=> string(7) "50.0000" ["wifi_flow"]=> string(2) "50" ["wifi_name"]=> string(9) "50M流量" } [1]=> array(4) { ["wifi_id"]=> string(1) "8" ["sale_price"]=> string(8) "100.0000" ["wifi_flow"]=> string(3) "100" ["wifi_name"]=> string(10) "100M流量" } [2]=> array(4) { ["wifi_id"]=> string(2) "10" ["sale_price"]=> string(8) "150.0000" ["wifi_flow"]=> string(3) "150" ["wifi_name"]=> string(10) "150M流量" } } } } 

		}

		
		
		//支付
		public function actionWifipayment()
		{
			$mcode = Yii::$app->request->post('mcode');
			$wifi_id = Yii::$app->request->post('wifi_id');
			
			$member = Member::find ()->select ( [ 
					'sign',
			] )->where ( [ 
					'member_code' => $mcode 
			] )->one ();

			if(!$member){
				return $this->render('error',['mcode'=>$mcode]);
				die();
			}
			
			
			$sign = $member['sign'];

			$response = MyWifi::WifiPay($sign,$wifi_id);

			echo json_encode($response);


			//邮轮通接口调用
			//需要参数 : sign
			// $sign = Yii::$app->request->post('sign');
			// $response = MyWifi::WifiPay($sign,$wifi_id);
			// return $response;

			//返回值
			//{"error":{"code_code":"1","message":"Not Enought Money To Pay This Wifi Item!"}}
			//{"data":{"code":1,"message":"Pay Success!"}}
		}
		
		
	
		
		//判断当前是否在登录状态
		public function actionLoginstatus()
		{
			$mcode = Yii::$app->request->get('mcode');
			$status = MyWifi::FindWifiLoginStatus($mcode)['exit_type'];
			
			return $this->render('loginstatus',['status'=>$status,'mcode'=>$mcode]);
		}
		
	
		
		//登录页面
		public function actionConnect()
		{
			$mcode = Yii::$app->request->get('mcode');
			$member = Member::find ()->select ( [ 
					'sign',
			] )->where ( [ 
					'member_code' => $mcode 
			] )->one ();
			
			if(!$member){
				return $this->render('error',['mcode'=>$mcode]);
				die();
			}

			$sign = $member['sign'];
			$membership = MemberService::getMemberbysign($sign);
			$membership_id = $member->member_id;
			$membership_code = $member->member_code;
	
			$log = MyWifi::FindWifiLoginLog($mcode);
			$status = MyWifi::FindWifiLoginStatus($mcode)['exit_type'];

			return $this->render('connect',['mcode'=>$mcode,'log'=>$log,'status'=>$status]);
		}

		
		//出错页面
		public function actionError()
		{
			$mcode = Yii::$app->request->get('mcode');
			return $this->render('error',['mcode'=>$mcode]);
		}


	
		
		//断开连接页面
		public function actionDisconnect()
		{
			$mcode = Yii::$app->request->get('mcode');
			return $this->render('disconnect',['mcode'=>$mcode]);
		}



		//测试用的
		public function actionTest()
		{
// 			$mcode = Yii::$app->request->get('mcode');
// 			$membership = Member::find ()->select ( [ 
// 					'sign',
// 			] )->where ( [ 
// 					'member_code' => $mcode 
// 			] )->one ();
// 			$sign = $membership['sign'];
// 			$member = MemberService::getMemberbysign($sign);

			// $res = MyWifi::CreateWifiUser($member);
			// $res =  json_decode($res,true);
			
			// $res = MyWifi::FindWifiUserInComst($member->passport_number);
			// $res =  json_decode($res,true);

			
			// $res = MyWifi::RechargeWifi($member->passport_number,100);
			// $res =  json_decode($res,true);
// 			$log = MyWifi::FindWifiLoginLog($mcode);
// 			var_dump($log);
			$mcode = '010000134559';
			$member = Member::find ()->select ( [
					'sign',
			] )->where ( [
					'member_code' => $mcode
			] )->one ();

			$sign = $member['sign'];
			$membership = MemberService::getMemberbysign($sign);
			
			var_dump($membership);
			
		}
	}

