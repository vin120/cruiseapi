<?php 
	namespace app\modules\wifiservice\controllers;

	use Yii;
	use yii\helpers\Url;
	use app\models\Member;
	use yii\web\Controller;
	use app\components\MemberService;
	use app\modules\wifiservice\components\MyWifi;
	use app\modules\wifiservice\components\MyCurl;
	
	class WifiController extends Controller
	{	
		//上网购买页面
		public function actionIndex()
		{
			$sign = Yii::$app->admin->identity->sign;
			$mcode = Yii::$app->admin->identity->member_code;
			
			$type = Yii::$app->admin->identity->member_type;
			
			if($type == 1){
				//会员
				$membership = MemberService::getMemberbysign($sign);
			}else {
				$membership =  MemberService::getCrewBySign($sign);
			}
			
			$wifi_items = MyWifi::FindWifiService();
			$passport = $membership['passport_number'];
			
			//查询流量信息
			$flow_info = MyCurl::CheckFlowAndParse($passport);
			
			//最近5条购买记录
			$sql = " SELECT a.pay_time,b.goods_name as name, FORMAT(b.goods_price * b.buy_num/100,2) as price FROM vcos_member_order a 
			LEFT JOIN vcos_member_order_detail b ON a.order_serial_num = b.order_serial_num
			WHERE membership_code='$mcode' AND order_type=3 ORDER BY order_id DESC LIMIT 5";
			$pay_log = Yii::$app->db->createCommand($sql)->queryAll();
			
			return $this->render('index',['membership'=>$membership,'wifi_items'=>$wifi_items,'flow_info'=>$flow_info,'pay_log'=>$pay_log]);
		}
		
		//确认支付页面
		public function actionOrderconfirm()
		{
			$wifi_id = Yii::$app->request->get('wifi_id','');
			$type = Yii::$app->admin->identity->member_type;
			
			if($wifi_id != ''){
				//获取套餐信息
				$wifi_item = MyWifi::FindWifiServiceById($wifi_id);		
				$sign = Yii::$app->admin->identity->sign;
				if($type == 1){
					//会员
					$membership = MemberService::getMemberbysign($sign);
				}else {
					$membership =  MemberService::getCrewBySign($sign);
				}
				
				$passport = $membership['passport_number'];
				//查询流量信息
				$flow_info = MyCurl::CheckFlowAndParse($passport);
				return $this->render('orderconfirm',['membership'=>$membership,'flow_info'=>$flow_info,'wifi_item'=>$wifi_item]);
			}else{
				return Yii::$app->getResponse()->redirect(Url::to("/wifiservice/wifi/index"));
			}
		}
		
		
		//支付
		public function actionWifipayment()
		{
			$wifi_id = Yii::$app->request->post('wifi_id');
			$sign = Yii::$app->admin->identity->sign;
			$response = MyWifi::WifiPay($sign,$wifi_id);
			echo json_encode($response);
		}

		
		//支付出错界面
		public function actionPaymenterror()
		{
			$sign = Yii::$app->admin->identity->sign;
// 			$membership = MemberService::getMemberbysign($sign);

			$type = Yii::$app->admin->identity->member_type;
			if($type == 1){
				//会员
				$membership = MemberService::getMemberbysign($sign);
			}else {
				$membership =  MemberService::getCrewBySign($sign);
			}
			$passport = $membership['passport_number'];
			//查询流量信息
			$flow_info = MyCurl::CheckFlowAndParse($passport);
			return $this->render('paymenterror',['membership'=>$membership,'flow_info'=>$flow_info]);
		}
		
		
		//支付失败界面
		public function actionPaymentfail()
		{
			$sign = Yii::$app->admin->identity->sign;
// 			$membership = MemberService::getMemberbysign($sign);
			$type = Yii::$app->admin->identity->member_type;
			if($type == 1){
				//会员
				$membership = MemberService::getMemberbysign($sign);
			}else {
				$membership =  MemberService::getCrewBySign($sign);
			}
			$passport = $membership['passport_number'];
			//查询流量信息
			$flow_info = MyCurl::CheckFlowAndParse($passport);
			return $this->render('paymentfail',['membership'=>$membership,'flow_info'=>$flow_info]);
		}
		
		//支付成功页面
		public function actionPaymentsuccess()
		{
			$sign = Yii::$app->admin->identity->sign;
// 			$membership = MemberService::getMemberbysign($sign);
			$type = Yii::$app->admin->identity->member_type;
			if($type == 1){
				//会员
				$membership = MemberService::getMemberbysign($sign);
			}else {
				$membership =  MemberService::getCrewBySign($sign);
			}
			$passport = $membership['passport_number'];
			//查询流量信息
			$flow_info = MyCurl::CheckFlowAndParse($passport);
			return $this->render('paymentsuccess',['membership'=>$membership,'flow_info'=>$flow_info]);
		}
		
		
		//判断当前是否在登录状态
		public function actionLoginstatus()
		{
			$mcode = Yii::$app->admin->identity->member_code;
			$sign =  Yii::$app->admin->identity->sign;
// 			$membership = MemberService::getMemberbysign($sign);
			$type = Yii::$app->admin->identity->member_type;
			if($type == 1){
				//会员
				$membership = MemberService::getMemberbysign($sign);
			}else {
				$membership =  MemberService::getCrewBySign($sign);
			}
			$passport = $membership['passport_number'];
			//查询流量信息
			$flow_info = MyCurl::CheckFlowAndParse($passport);
			//查询状态
			$status = MyWifi::FindWifiLoginStatus($mcode);		// true为在线， false为离线
			return $this->render('loginstatus',['status'=>$status]);
		}

		
		//上网连接页面
		public function actionConnect()
		{
			$mcode = Yii::$app->admin->identity->member_code;
			$sign = Yii::$app->admin->identity->sign;
// 			$membership = MemberService::getMemberbysign($sign);
			$type = Yii::$app->admin->identity->member_type;
			if($type == 1){
				//会员
				$membership = MemberService::getMemberbysign($sign);
			}else {
				$membership =  MemberService::getCrewBySign($sign);
			}
			$passport = $membership['passport_number'];
			//查询流量信息
			$flow_info = MyCurl::CheckFlowAndParse($passport);
			//查询状态
			$status = MyWifi::FindWifiLoginStatus($mcode);		// true为在线， false为离线
			//连接记录
			$log = MyWifi::FindWifiLoginLog($mcode);
			return $this->render('connect',['mcode'=>$mcode,'log'=>$log,'status'=>$status,'membership'=>$membership,'flow_info'=>$flow_info,'passport'=>$passport]);
		}

		
		//断开连接界面
		public function actionDisconnect()
		{
			$mcode = Yii::$app->admin->identity->member_code;
			$sign = Yii::$app->admin->identity->sign;
// 			$membership = MemberService::getMemberbysign($sign);
			$type = Yii::$app->admin->identity->member_type;
			if($type == 1){
				//会员
				$membership = MemberService::getMemberbysign($sign);
			}else {
				$membership =  MemberService::getCrewBySign($sign);
			}
			$passport = $membership['passport_number'];
			//查询流量
			$flow_info = MyCurl::CheckFlowAndParse($passport);
			//查询状态
			$status = MyWifi::FindWifiLoginStatus($mcode);		// true为在线， false为离线
			//连接记录
			$log = MyWifi::FindWifiLoginLog($mcode);
			return $this->render('disconnect',['log'=>$log,'status'=>$status,'membership'=>$membership,'flow_info'=>$flow_info]);
		}
		
		
		
		
	public function actionConnecterror()
	{
		$mcode = Yii::$app->admin->identity->member_code;
		$sign = Yii::$app->admin->identity->sign;
// 		$membership = MemberService::getMemberbysign($sign);
		$type = Yii::$app->admin->identity->member_type;
		if($type == 1){
			//会员
			$membership = MemberService::getMemberbysign($sign);
		}else {
			$membership =  MemberService::getCrewBySign($sign);
		}
		$passport = $membership['passport_number'];
		//查询流量
		$flow_info = MyCurl::CheckFlowAndParse($passport);
		//连接记录
		$log = MyWifi::FindWifiLoginLog($mcode);
		
		return $this->render('connecterror',['membership'=>$membership,'flow_info'=>$flow_info]);
	}
		
		
	public function actionDisconnecterror()
	{
		$mcode = Yii::$app->admin->identity->member_code;
		$sign = Yii::$app->admin->identity->sign;
// 		$membership = MemberService::getMemberbysign($sign);
		$type = Yii::$app->admin->identity->member_type;
		if($type == 1){
			//会员
			$membership = MemberService::getMemberbysign($sign);
		}else {
			$membership =  MemberService::getCrewBySign($sign);
		}
		$passport = $membership['passport_number'];
		//查询流量
		$flow_info = MyCurl::CheckFlowAndParse($passport);
		//连接记录
		$log = MyWifi::FindWifiLoginLog($mcode);
		
		return $this->render('disconnecterror',['membership'=>$membership,'flow_info'=>$flow_info]);
	}
	
	
		//测试用的
		public function actionTest()
		{
// 			$sign = Yii::$app->admin->identity->sign;
// 			$member = MemberService::getMemberbysign($sign);
			
// 			$transaction = Yii::$app->mdb->beginTransaction();
// 			try {
// 				//直接支付
// 				$money = 1000 - (1 * 100);	//注意转换单位
// 				if($type == 1){
// 					$member['member_money'] = $money;
// 					$member->save();
// 				}else{
// 					$sql = " UPDATE vcos_wifi_crew SET money='$money' WHERE crew_id='{$member['member_id']}' ";
// 					Yii::$app->mdb->createCommand($sql)->execute();
// 				}
			
			
// 				//充值wifi对应的钱，对接接口
// 				// 						MyCurl::RechargeWifi($member['passport_number'],$sale_price);
// 				MyCurl::RechargeWifi($member['passport_number'],$wifi_item['wifi_flow']);		//comst 充值时按照流量和金额1:1比例
			
// 				//记录购买Wifi记录
// 				self::CreateWifiPayLog($sign,$membership_code,$wifi_item,$type);
			
// 				$response['data'] = ['code'=>1,'message'=>'Pay Success!'];
// 				$transaction->commit();
// 			}catch (EXception $e){
// 				$transaction->rollBack();
// 				$response ['error'] = ['errorCode' => 2,'message' => 'System Wrong!'];
// 			}
			
		}
	}

