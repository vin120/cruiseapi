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
			$on_cruise = Yii::$app->params['on_cruise'];		//是否在船上
			if(!$on_cruise){
				return Yii::$app->getResponse()->redirect(Url::to("/wifi/wifi/403"));
			}
			
			$mcode = Yii::$app->request->get('mcode');
			$wifi_type = 1;	//wifi套餐类型：1为会员套餐类型，2为船员普通套餐类型，3为船员-半价-半流量套餐类型
			
			
			if((substr($mcode,0,3) == 'TS@') || (substr($mcode, 0,3) == 'ts@') || (substr($mcode, 0,3) == 'TS_') || (substr($mcode, 0,3) == 'ts_')){
				//船员
				$sql  =' SELECT crew_id as member_id,crew_code as member_code,cn_name,smart_card_number, passport_number, crew_password as member_password ,
					crew_email as member_email,mobile_number,money as member_money,crew_credit as member_credit,sign,overdraft_limit,curr_overdraft_amount	
					FROM vcos_wifi_crew WHERE crew_code=\''.$mcode.'\' ';
				$membership = Yii::$app->mdb->createCommand($sql)->queryOne();
				$type = 2;//1是会员，2是船员
				$wifi_type = 2; //wifi套餐类型：2为船员普通套餐类型
				
				if(date('d',time()) >= Yii::$app->params['half_price_day']) {
					$wifi_type = 3; //wifi套餐类型：3为船员-半价-半流量套餐类型
				}
				
			} else {
				//会员
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
				$type = 1;		//1是会员，2是船员
				$wifi_type = 1; //wifi套餐类型：1为会员套餐类型
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
			return $this->render('index',['membership'=>$membership,'wifi_items'=>$wifi_items,'flow_info'=>$flow_info,'pay_log'=>$pay_log,'mcode'=>$mcode]);
		}
		
		
		//确认支付页面
		public function actionOrderconfirm()
		{
			$on_cruise = Yii::$app->params['on_cruise'];		//是否在船上
			if(!$on_cruise){
				return Yii::$app->getResponse()->redirect(Url::to("/wifi/wifi/403"));
			}
			
			$wifi_id = Yii::$app->request->get('wifi_id');
			$mcode = Yii::$app->request->get('mcode');
			
			if($wifi_id != ''){
				
				if((substr($mcode,0,3) == 'TS@') || (substr($mcode, 0,3) == 'ts@') || (substr($mcode, 0,3) == 'TS_') || (substr($mcode, 0,3) == 'ts_')){
					//船员
					$sql  =' SELECT crew_id as member_id,crew_code as member_code,cn_name,smart_card_number, passport_number, crew_password as member_password ,
					crew_email as member_email,mobile_number,money as member_money,crew_credit as member_credit,sign,overdraft_limit,curr_overdraft_amount
					FROM vcos_wifi_crew WHERE crew_code=\''.$mcode.'\' ';
					$membership = Yii::$app->mdb->createCommand($sql)->queryOne();
					$type=2; //1是普通会员，2是船员
				}else {
					
					//会员
					$member = Member::find ()->select ( [
							'sign',
					] )->where ( [
							'member_code' => $mcode
					] )->one ();
					$type=1;	//1是普通会员，2是船员
					$sign = $member['sign'];
					$membership = MemberService::getMemberbysign($sign);
				}
				
				//获取套餐信息
				$wifi_item = MyWifi::FindWifiServiceById($wifi_id);		
				$wifi_items = MyWifi::FindWifiService($type);
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
			$on_cruise = Yii::$app->params['on_cruise'];		//是否在船上
			if(!$on_cruise){
				return Yii::$app->getResponse()->redirect(Url::to("/wifi/wifi/403"));
			}
			
			$wifi_id = Yii::$app->request->post('wifi_id');
			$mcode = Yii::$app->request->post('mcode');
			
			if((substr($mcode,0,3) == 'TS@') || (substr($mcode, 0,3) == 'ts@') || (substr($mcode, 0,3) == 'TS_') || (substr($mcode, 0,3) == 'ts_')){
				//船员
				$sql  =' SELECT crew_id as member_id,crew_code as member_code,cn_name,smart_card_number, passport_number, crew_password as member_password ,
					crew_email as member_email,mobile_number,money as member_money,crew_credit as member_credit,sign,overdraft_limit,curr_overdraft_amount
					FROM vcos_wifi_crew WHERE crew_code=\''.$mcode.'\' ';
				$member = Yii::$app->mdb->createCommand($sql)->queryOne();
				$type = 2;
			}else {
				//会员
				$member = Member::find ()->select ( [
						'sign',
				] )->where ( [
						'member_code' => $mcode
				] )->one ();
				$type = 1;
			}
			
			$sign =  $member['sign'];
			$response = MyWifi::WifiPay($sign,$wifi_id,$type);
			echo json_encode($response);
		}

		
		//支付出错界面
		public function actionPaymenterror()
		{
			$on_cruise = Yii::$app->params['on_cruise'];		//是否在船上
			if(!$on_cruise){
				return Yii::$app->getResponse()->redirect(Url::to("/wifi/wifi/403"));
			}
			
			$mcode = Yii::$app->request->get('mcode');
			
			if((substr($mcode,0,3) == 'TS@') || (substr($mcode, 0,3) == 'ts@') || (substr($mcode, 0,3) == 'TS_') || (substr($mcode, 0,3) == 'ts_')){
				//船员
				$sql  =' SELECT crew_id as member_id,crew_code as member_code,cn_name,smart_card_number, passport_number, crew_password as member_password ,
					crew_email as member_email,mobile_number,money as member_money,crew_credit as member_credit,sign,overdraft_limit,curr_overdraft_amount
					FROM vcos_wifi_crew WHERE crew_code=\''.$mcode.'\' ';
				$membership = Yii::$app->mdb->createCommand($sql)->queryOne();
				$type = 2;
			}else {
				//会员
				$member = Member::find ()->select ( [
					'sign',
				] )->where ( [
					'member_code' => $mcode
				] )->one ();
				$type = 1;
				$sign =  $member['sign'];
				$membership = MemberService::getMemberbysign($sign);
			}
			
			$passport = $membership['passport_number'];
			//查询流量信息
			$flow_info = MyCurl::CheckFlowAndParse($passport);
			return $this->render('paymenterror',['membership'=>$membership,'flow_info'=>$flow_info,'mcode'=>$mcode]);
		}
		
		
		//支付失败界面
		public function actionPaymentfail()
		{
			$on_cruise = Yii::$app->params['on_cruise'];		//是否在船上
			if(!$on_cruise){
				return Yii::$app->getResponse()->redirect(Url::to("/wifi/wifi/403"));
			}
			
			$mcode = Yii::$app->request->get('mcode');
			
			if((substr($mcode,0,3) == 'TS@') || (substr($mcode, 0,3) == 'ts@') || (substr($mcode, 0,3) == 'TS_') || (substr($mcode, 0,3) == 'ts_')){
				//船员
				$sql  =' SELECT crew_id as member_id,crew_code as member_code,cn_name,smart_card_number, passport_number, crew_password as member_password ,
					crew_email as member_email,mobile_number,money as member_money,crew_credit as member_credit,sign,overdraft_limit,curr_overdraft_amount
					FROM vcos_wifi_crew WHERE crew_code=\''.$mcode.'\' ';
				$membership = Yii::$app->mdb->createCommand($sql)->queryOne();
				$type = 2;
			}else {
				//会员
				$member = Member::find ()->select ( [
					'sign',
				] )->where ( [
					'member_code' => $mcode
				] )->one ();
				$type = 1;
				$sign =  $member['sign'];
				$membership = MemberService::getMemberbysign($sign);
			}
			
			$passport = $membership['passport_number'];
			//查询流量信息
			$flow_info = MyCurl::CheckFlowAndParse($passport);
			return $this->render('paymentfail',['membership'=>$membership,'flow_info'=>$flow_info,'mcode'=>$mcode]);
		}
		
		//支付成功页面
		public function actionPaymentsuccess()
		{
			$on_cruise = Yii::$app->params['on_cruise'];		//是否在船上
			if(!$on_cruise){
				return Yii::$app->getResponse()->redirect(Url::to("/wifi/wifi/403"));
			}
			
			$mcode = Yii::$app->request->get('mcode');
			
			if((substr($mcode,0,3) == 'TS@') || (substr($mcode, 0,3) == 'ts@') || (substr($mcode, 0,3) == 'TS_') || (substr($mcode, 0,3) == 'ts_')){
				//船员
				$sql  =' SELECT crew_id as member_id,crew_code as member_code,cn_name,smart_card_number, passport_number, crew_password as member_password ,
					crew_email as member_email,mobile_number,money as member_money,crew_credit as member_credit,sign,overdraft_limit,curr_overdraft_amount
					FROM vcos_wifi_crew WHERE crew_code=\''.$mcode.'\' ';
				$membership = Yii::$app->mdb->createCommand($sql)->queryOne();
				$type = 2;
			}else {
				//会员
				$member = Member::find ()->select ( [
					'sign',
				] )->where ( [
					'member_code' => $mcode
				] )->one ();
				$type = 1;
				$sign =  $member['sign'];
				$membership = MemberService::getMemberbysign($sign);
			}
			
			$passport = $membership['passport_number'];
			//查询流量信息
			$flow_info = MyCurl::CheckFlowAndParse($passport);
			return $this->render('paymentsuccess',['membership'=>$membership,'flow_info'=>$flow_info,'mcode'=>$mcode]);
		}
		
		
		//判断当前是否在登录状态
		public function actionLoginstatus()
		{
			$on_cruise = Yii::$app->params['on_cruise'];		//是否在船上
			if(!$on_cruise){
				return Yii::$app->getResponse()->redirect(Url::to("/wifi/wifi/403"));
			}
			
			$mcode = Yii::$app->request->get('mcode');
			
			if((substr($mcode,0,3) == 'TS@') || (substr($mcode, 0,3) == 'ts@') || (substr($mcode, 0,3) == 'TS_') || (substr($mcode, 0,3) == 'ts_')){
				//船员
				$sql  =' SELECT crew_id as member_id,crew_code as member_code,cn_name,smart_card_number, passport_number, crew_password as member_password ,
					crew_email as member_email,mobile_number,money as member_money,crew_credit as member_credit,sign,overdraft_limit,curr_overdraft_amount
					FROM vcos_wifi_crew WHERE crew_code=\''.$mcode.'\' ';
				$membership = Yii::$app->mdb->createCommand($sql)->queryOne();
				$type = 2;
			}else {
				//会员
				$member = Member::find ()->select ( [
					'sign',
				] )->where ( [
					'member_code' => $mcode
				] )->one ();
				$type = 1;
				$sign =  $member['sign'];
				$membership = MemberService::getMemberbysign($sign);
			}
			
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
			$on_cruise = Yii::$app->params['on_cruise'];		//是否在船上
			if(!$on_cruise){
				return Yii::$app->getResponse()->redirect(Url::to("/wifi/wifi/403"));
			}
			
			$mcode = Yii::$app->request->get('mcode');
			
			if((substr($mcode,0,3) == 'TS@') || (substr($mcode, 0,3) == 'ts@') || (substr($mcode, 0,3) == 'TS_') || (substr($mcode, 0,3) == 'ts_')){
				//船员
				$sql  =' SELECT crew_id as member_id,crew_code as member_code,cn_name,smart_card_number, passport_number, crew_password as member_password ,
					crew_email as member_email,mobile_number,money as member_money,crew_credit as member_credit,sign,overdraft_limit,curr_overdraft_amount
					FROM vcos_wifi_crew WHERE crew_code=\''.$mcode.'\' ';
				$membership = Yii::$app->mdb->createCommand($sql)->queryOne();
				$type = 2;
			}else {
				//会员
				$member = Member::find ()->select ( [
					'sign',
				] )->where ( [
					'member_code' => $mcode
				] )->one ();
				$type = 1;
				$sign =  $member['sign'];
				$membership = MemberService::getMemberbysign($sign);
			}
			
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
			$on_cruise = Yii::$app->params['on_cruise'];		//是否在船上
			if(!$on_cruise){
				return Yii::$app->getResponse()->redirect(Url::to("/wifi/wifi/403"));
			}
			
			$mcode = Yii::$app->request->get('mcode');
			
			if((substr($mcode,0,3) == 'TS@') || (substr($mcode, 0,3) == 'ts@') || (substr($mcode, 0,3) == 'TS_') || (substr($mcode, 0,3) == 'ts_')){
				//船员
				$sql  =' SELECT crew_id as member_id,crew_code as member_code,cn_name,smart_card_number, passport_number, crew_password as member_password ,
					crew_email as member_email,mobile_number,money as member_money,crew_credit as member_credit,sign,overdraft_limit,curr_overdraft_amount
					FROM vcos_wifi_crew WHERE crew_code=\''.$mcode.'\' ';
				$membership = Yii::$app->mdb->createCommand($sql)->queryOne();
				$type = 2;
			}else {
				//会员
				$member = Member::find ()->select ( [
					'sign',
				] )->where ( [
					'member_code' => $mcode
				] )->one ();
				$type = 1;
				$sign =  $member['sign'];
				$membership = MemberService::getMemberbysign($sign);
			}
			
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
			$on_cruise = Yii::$app->params['on_cruise'];		//是否在船上
			if(!$on_cruise){
				return Yii::$app->getResponse()->redirect(Url::to("/wifi/wifi/403"));
			}
			
			$mcode = Yii::$app->request->get('mcode');
				
			if((substr($mcode,0,3) == 'TS@') || (substr($mcode, 0,3) == 'ts@') || (substr($mcode, 0,3) == 'TS_') || (substr($mcode, 0,3) == 'ts_')){
				//船员
				$sql  =' SELECT crew_id as member_id,crew_code as member_code,cn_name,smart_card_number, passport_number, crew_password as member_password ,
					crew_email as member_email,mobile_number,money as member_money,crew_credit as member_credit,sign,overdraft_limit,curr_overdraft_amount
					FROM vcos_wifi_crew WHERE crew_code=\''.$mcode.'\' ';
				$membership = Yii::$app->mdb->createCommand($sql)->queryOne();
				$type = 2;
			}else {
				//会员
				$member = Member::find ()->select ( [
					'sign',
				] )->where ( [
					'member_code' => $mcode
				] )->one ();
				$type = 1;
				$sign =  $member['sign'];
				$membership = MemberService::getMemberbysign($sign);
			}
			
			$passport = $membership['passport_number'];
			//查询流量
			$flow_info = MyCurl::CheckFlowAndParse($passport);
			//连接记录
			$log = MyWifi::FindWifiLoginLog($mcode);
			
			return $this->render('connecterror',['membership'=>$membership,'flow_info'=>$flow_info,'mcode'=>$mcode]);
		}
		
		
		public function actionDisconnecterror()
		{
			$on_cruise = Yii::$app->params['on_cruise'];		//是否在船上
			if(!$on_cruise){
				return Yii::$app->getResponse()->redirect(Url::to("/wifi/wifi/403"));
			}
			
			$mcode = Yii::$app->request->get('mcode');
			if((substr($mcode,0,3) == 'TS@') || (substr($mcode, 0,3) == 'ts@') || (substr($mcode, 0,3) == 'TS_') || (substr($mcode, 0,3) == 'ts_')){
				//船员
				$sql  =' SELECT crew_id as member_id,crew_code as member_code,cn_name,smart_card_number, passport_number, crew_password as member_password ,
					crew_email as member_email,mobile_number,money as member_money,crew_credit as member_credit,sign,overdraft_limit,curr_overdraft_amount
					FROM vcos_wifi_crew WHERE crew_code=\''.$mcode.'\' ';
				$membership = Yii::$app->mdb->createCommand($sql)->queryOne();
				$type = 2;
			}else {
				//会员
				$member = Member::find ()->select ( [
					'sign',
				] )->where ( [
					'member_code' => $mcode
				] )->one ();
				$type = 1;
				$sign =  $member['sign'];
				$membership = MemberService::getMemberbysign($sign);
			}
			
			$passport = $membership['passport_number'];
			//查询流量
			$flow_info = MyCurl::CheckFlowAndParse($passport);
			//连接记录
			$log = MyWifi::FindWifiLoginLog($mcode);
			
			return $this->render('disconnecterror',['membership'=>$membership,'flow_info'=>$flow_info,'mcode'=>$mcode]);
		}
		
		public function action403()
		{
			return $this->render('403');
		}
	
	}

