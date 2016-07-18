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
			
			$wifi_type = 1;	//wifi套餐类型：1为会员套餐类型，2为船员普通套餐类型，3为船员-半价-半流量套餐类型
			
			if($type == 1){
				$wifi_type = 1;	//wifi套餐类型：1为会员套餐类型
				//普通会员
				$membership = MemberService::getMemberbysign($sign);
			}else {
				$wifi_type = 2;	//wifi套餐类型：2为船员普通套餐类型
				//船员
				$membership =  MemberService::getCrewBySign($sign);
				
				if(date('d',time()) >= Yii::$app->params['half_price_day']) {
					$wifi_type = 3;	//wifi套餐类型: 3为船员-半价-半流量套餐类型
				}
			}
			
			$wifi_items = MyWifi::FindWifiService($wifi_type);
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
					//普通会员
					$membership = MemberService::getMemberbysign($sign);
				}else {
					//船员
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
			$type = Yii::$app->admin->identity->member_type;
			if($type == 1){
				//普通会员
				$membership = MemberService::getMemberbysign($sign);
			}else {
				//船员
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
			$type = Yii::$app->admin->identity->member_type;
			if($type == 1){
				//普通会员
				$membership = MemberService::getMemberbysign($sign);
			}else {
				//船员
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
			$type = Yii::$app->admin->identity->member_type;
			if($type == 1){
				//普通会员
				$membership = MemberService::getMemberbysign($sign);
			}else {
				//船员
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
			$type = Yii::$app->admin->identity->member_type;
			if($type == 1){
				//普通会员
				$membership = MemberService::getMemberbysign($sign);
			}else {
				//船员
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
			$type = Yii::$app->admin->identity->member_type;
			if($type == 1){
				//普通会员
				$membership = MemberService::getMemberbysign($sign);
			}else {
				//船员
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
			$type = Yii::$app->admin->identity->member_type;
			if($type == 1){
				//普通会员
				$membership = MemberService::getMemberbysign($sign);
			}else {
				//船员
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
			$type = Yii::$app->admin->identity->member_type;
			if($type == 1){
				//普通会员
				$membership = MemberService::getMemberbysign($sign);
			}else {
				//船员
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
			$type = Yii::$app->admin->identity->member_type;
			if($type == 1){
				//普通会员
				$membership = MemberService::getMemberbysign($sign);
			}else {
				//船员
				$membership =  MemberService::getCrewBySign($sign);
			}
			$passport = $membership['passport_number'];
			//查询流量
			$flow_info = MyCurl::CheckFlowAndParse($passport);
			//连接记录
			$log = MyWifi::FindWifiLoginLog($mcode);
			
			return $this->render('disconnecterror',['membership'=>$membership,'flow_info'=>$flow_info]);
		}

		//重置密码--第一次登录时
		public function actionResetpassword()
		{
			return $this->render('resetpassword');
		}
		
		
		//重置密码---实现逻辑
		public function actionResetpasswordvalidate()
		{
			$password = Yii::$app->request->post('password');
			$password_again = Yii::$app->request->post('password_again');
			
			if(!empty($password) && !empty($password_again)){
				if($password === $password_again){
					if(strlen($password) < 13 && strlen($password) > 3){
						$type = Yii::$app->admin->identity->member_type;
						$passport_number = Yii::$app->admin->identity->passport_number;
						if($type == 1){
							//普通会员
							$sql = " UPDATE  `vcos_member` SET `member_password`='".md5($password)."' WHERE `passport_number`='$passport_number' ";
							Yii::$app->mdb->createCommand($sql)->execute();
						}else {
							//船员
							$sql = " UPDATE `vcos_wifi_crew` SET `crew_password`='".md5($password)."' WHERE `passport_number`='$passport_number'";
							Yii::$app->mdb->createCommand($sql)->execute();
						}
						return $this->render('resetpassword_right',['password'=>$password]);
					}else {
						return $this->render('resetpassword_wrong',['message'=>'密码长度请保持在4位到12位之间']);
					}
				}else {
					return $this->render('resetpassword_wrong',['message'=>'两次密码不一致']);
				}
			}else{
				return $this->render('resetpassword_wrong',['message'=>'修改的密码不能为空']);
			}
		}
		
		
		//忘记密码界面
		public function actionForgetpassword()
		{
			return $this->render('forgetpassword');
		}
		
		
		//忘记密码---修改密码逻辑实现---
		public function actionForgetpasswordvalidate()
		{
			$passport_number = Yii::$app->request->post('passport_number');
			$mobile_number = Yii::$app->request->post('mobile_number');
			
			if(!empty($passport_number) && !empty($mobile_number)){
				$sql = "SELECT * FROM `vcos_member_crew` WHERE `passport_number`='$passport_number' AND `mobile_number`='$mobile_number'";
				$member = Yii::$app->mdb->createCommand($sql)->queryOne();
				if($member){
					$type = $member['member_type'];
					$new_password  = rand(100000,999999);
					if($type == 1){
						//普通会员
						$sql = " UPDATE  `vcos_member` SET `member_password`='".md5($new_password)."' WHERE `passport_number`='$passport_number' ";
						Yii::$app->mdb->createCommand($sql)->execute();
					}else {
						//船员
						$sql = " UPDATE `vcos_wifi_crew` SET `crew_password`='".md5($new_password)."' WHERE `passport_number`='$passport_number'";
						Yii::$app->mdb->createCommand($sql)->execute();
					}
					return $this->render('forgetpassword_right',['new_password'=>$new_password]);
				}else {
					return $this->render('forgetpassword_wrong');
				}
			}else {
				return $this->render('forgetpassword_wrong');
			}
		}

		
		//修改密码
		public  function actionChangepassword()
		{
			return $this->render('changepassword');
		}
		
		
		//修改密码---逻辑实现---
		public function actionChangepasswordvalidate()
		{
			$password = Yii::$app->request->post('password');
			$password_again = Yii::$app->request->post('password_again');
			
			if(!empty($password) && !empty($password_again)){
				if($password === $password_again){
					if(strlen($password) < 13 && strlen($password) > 3){
						$type = Yii::$app->admin->identity->member_type;
						$passport_number = Yii::$app->admin->identity->passport_number;
						if($type == 1){
							//普通会员
							$sql = " UPDATE  `vcos_member` SET `member_password`='".md5($password)."' WHERE `passport_number`='$passport_number' ";
							Yii::$app->mdb->createCommand($sql)->execute();
						}else {
							//船员
							$sql = " UPDATE `vcos_wifi_crew` SET `crew_password`='".md5($password)."' WHERE `passport_number`='$passport_number'";
							Yii::$app->mdb->createCommand($sql)->execute();
						}
						return $this->render('changepassword_right',['password'=>$password]);
					}else {
						return $this->render('changepassword_wrong',['message'=>'密码长度请保持在4位到12位之间']);
					}
				}else {
					return $this->render('changepassword_wrong',['message'=>'两次密码不一致']);
				}
			}else{
				return $this->render('changepassword_wrong',['message'=>'修改的密码不能为空']);
			}
		}
		
		public function actionLowversion()
		{
			return $this->render('lowversion');
		}
		
		
		public function actionTest()
		{
			
			$name = '';
			$response  = MyCurl::InitAccountGroup($name);
			
			var_dump($response);
		}
		
		
	}

