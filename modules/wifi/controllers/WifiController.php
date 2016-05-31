<?php 
	namespace app\modules\wifi\controllers;

	use Yii;
	use yii\helpers\Url;
	use app\models\Member;
	use yii\web\Controller;
	use app\components\MemberService;
	use app\modules\wifi\components\MyWifi;
	use app\modules\wifi\components\MyCurl;
	
	class WifiController extends Controller
	{	
		//上网购买页面
		public function actionIndex()
		{
			$mcode = Yii::$app->request->get('mcode');
			
			$membership = Member::find ()->select ( [
					'member_id',
					'member_code',
					'cn_name',
					'passport_number',
					'member_password',
					'member_email',
					'mobile_number',
					'member_money',
					'sign',
			] )->where ( [
					'member_code' => $mcode
			] )->one ();
			
			$wifi_items = MyWifi::FindWifiService();
			$passport = $membership['passport_number'];
			
			//查询流量信息
			$flow_info = MyCurl::CheckFlowAndParse($passport);
			
			//最近5条购买记录
			$sql = " SELECT a.pay_time,b.goods_name as name, FORMAT(b.goods_price * b.buy_num/100,2) as price FROM vcos_member_order a 
			LEFT JOIN vcos_member_order_detail b ON a.order_serial_num = b.order_serial_num
			WHERE membership_code='$mcode' AND order_type=3 ORDER BY order_id DESC LIMIT 5";
			$pay_log = Yii::$app->db->createCommand($sql)->queryAll();
			return $this->render('index',['membership'=>$membership,'wifi_items'=>$wifi_items,'flow_info'=>$flow_info,'pay_log'=>$pay_log,'mcode'=>$mcode]);
		}
		
		//确认支付页面
		public function actionOrderconfirm()
		{
			$wifi_id = Yii::$app->request->get('wifi_id');
			$mcode = Yii::$app->request->get('mcode');
			
			$member = Member::find ()->select ( [
					'sign',
			] )->where ( [
					'member_code' => $mcode
			] )->one ();
			
			if($wifi_id != ''){
				//获取套餐信息
				$wifi_item = MyWifi::FindWifiServiceById($wifi_id);		
				$sign = $member['sign'];
				$membership = MemberService::getMemberbysign($sign);
				$wifi_items = MyWifi::FindWifiService();
				$passport = $membership['passport_number'];
				//查询流量信息
				$flow_info = MyCurl::CheckFlowAndParse($passport);
				return $this->render('orderconfirm',['membership'=>$membership,'flow_info'=>$flow_info,'wifi_item'=>$wifi_item,'mcode'=>$mcode]);
			}else{
				return Yii::$app->getResponse()->redirect(Url::to("/wifi/wifi/index")."?mcode=$mcode");
			}
		}
		
		
		//支付
		public function actionWifipayment()
		{
			$wifi_id = Yii::$app->request->post('wifi_id');
			$mcode = Yii::$app->request->post('mcode');
			
			$member = Member::find ()->select ( [
					'sign',
			] )->where ( [
					'member_code' => $mcode
			] )->one ();
			
			$sign =  $member['sign'];
// 			$passport = $member['']
			$response = MyWifi::WifiPay($sign,$wifi_id);
			echo json_encode($response);
		}

		
		//支付出错界面
		public function actionPaymenterror()
		{
			$mcode = Yii::$app->request->get('mcode');
			
			$member = Member::find ()->select ( [
					'sign',
			] )->where ( [
					'member_code' => $mcode
			] )->one ();
			
			$sign =  $member['sign'];
			$membership = MemberService::getMemberbysign($sign);
			$passport = $membership['passport_number'];
			//查询流量信息
			$flow_info = MyCurl::CheckFlowAndParse($passport);
			return $this->render('paymenterror',['membership'=>$membership,'flow_info'=>$flow_info,'mcode'=>$mcode]);
		}
		
		
		//支付失败界面
		public function actionPaymentfail()
		{
			$mcode = Yii::$app->request->get('mcode');
			
			$member = Member::find ()->select ( [
					'sign',
			] )->where ( [
					'member_code' => $mcode
			] )->one ();
			
			$sign =  $member['sign'];
			$membership = MemberService::getMemberbysign($sign);
			$passport = $membership['passport_number'];
			//查询流量信息
			$flow_info = MyCurl::CheckFlowAndParse($passport);
			return $this->render('paymentfail',['membership'=>$membership,'flow_info'=>$flow_info,'mcode'=>$mcode]);
		}
		
		//支付成功页面
		public function actionPaymentsuccess()
		{
			$mcode = Yii::$app->request->get('mcode');
			
			$member = Member::find ()->select ( [
					'sign',
			] )->where ( [
					'member_code' => $mcode
			] )->one ();
			
			$sign =  $member['sign'];
			$membership = MemberService::getMemberbysign($sign);
			$passport = $membership['passport_number'];
			//查询流量信息
			$flow_info = MyCurl::CheckFlowAndParse($passport);
			return $this->render('paymentsuccess',['membership'=>$membership,'flow_info'=>$flow_info,'mcode'=>$mcode]);
		}
		
		
		//判断当前是否在登录状态
		public function actionLoginstatus()
		{
			$mcode = Yii::$app->request->get('mcode');
			
			$member = Member::find ()->select ( [
					'sign',
			] )->where ( [
					'member_code' => $mcode
			] )->one ();
			
			$sign =  $member['sign'];
			$membership = MemberService::getMemberbysign($sign);
			$passport = $membership['passport_number'];
			//查询流量信息
			$flow_info = MyCurl::CheckFlowAndParse($passport);
			//查询状态
			$status = MyWifi::FindWifiLoginStatus($mcode);		// true为在线， false为离线
			return $this->render('loginstatus',['status'=>$status,'mcode'=>$mcode]);
		}

		
		//上网连接页面
		public function actionConnect()
		{
			$mcode = Yii::$app->request->get('mcode');
			
			$member = Member::find ()->select ( [
					'sign',
			] )->where ( [
					'member_code' => $mcode
			] )->one ();
			
			$sign =  $member['sign'];
			$membership = MemberService::getMemberbysign($sign);
			$passport = $membership['passport_number'];
			//查询流量信息
			$flow_info = MyCurl::CheckFlowAndParse($passport);
			//查询状态
			$status = MyWifi::FindWifiLoginStatus($mcode);		// true为在线， false为离线
			//连接记录
			$log = MyWifi::FindWifiLoginLog($mcode);
			return $this->render('connect',['mcode'=>$mcode,'log'=>$log,'status'=>$status,'membership'=>$membership,'flow_info'=>$flow_info,'mcode'=>$mcode,'passport'=>$passport]);
		}

		
		//断开连接界面
		public function actionDisconnect()
		{
			$mcode = Yii::$app->request->get('mcode');
			
			$member = Member::find ()->select ( [
					'sign',
			] )->where ( [
					'member_code' => $mcode
			] )->one ();
			
			$sign =  $member['sign'];
			$membership = MemberService::getMemberbysign($sign);
			$passport = $membership['passport_number'];
			//查询流量
			$flow_info = MyCurl::CheckFlowAndParse($passport);
			//查询状态
			$status = MyWifi::FindWifiLoginStatus($mcode);		// true为在线， false为离线
			//连接记录
			$log = MyWifi::FindWifiLoginLog($mcode);
			return $this->render('disconnect',['log'=>$log,'status'=>$status,'membership'=>$membership,'flow_info'=>$flow_info,'mcode'=>$mcode]);
		}
		
		
		public function actionConnecterror()
		{
			$mcode = Yii::$app->request->get('mcode');
				
			$member = Member::find ()->select ( [
					'sign',
			] )->where ( [
					'member_code' => $mcode
			] )->one ();
			
			$sign =  $member['sign'];
			$membership = MemberService::getMemberbysign($sign);
			$passport = $membership['passport_number'];
			//查询流量
			$flow_info = MyCurl::CheckFlowAndParse($passport);
			//连接记录
			$log = MyWifi::FindWifiLoginLog($mcode);
			
			return $this->render('connecterror',['membership'=>$membership,'flow_info'=>$flow_info,'mcode'=>$mcode]);
		}
		
		
		public function actionDisconnecterror()
		{
			$mcode = Yii::$app->request->get('mcode');
			$member = Member::find ()->select ( [
					'sign',
			] )->where ( [
					'member_code' => $mcode
			] )->one ();
			$sign =  $member['sign'];
			$membership = MemberService::getMemberbysign($sign);
			$passport = $membership['passport_number'];
			//查询流量
			$flow_info = MyCurl::CheckFlowAndParse($passport);
			//连接记录
			$log = MyWifi::FindWifiLoginLog($mcode);
			
			return $this->render('disconnecterror',['membership'=>$membership,'flow_info'=>$flow_info,'mcode'=>$mcode]);
		}
	
	}

