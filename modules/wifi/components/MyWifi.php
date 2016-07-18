<?php
	namespace app\modules\wifi\components;

	use Yii;
	use app\components\MemberService;
	use app\models\Member;
	use app\models\MemberOrder;
	use app\models\MemberOrderDetail;
	use app\components\OrderService;
	use app\modules\wifi\components\MyCurl;

	class MyWifi 
	{
		
		//Find All WifiService
		public static function FindWifiService($type,$my_lang='zh_cn')
		{
			$response = array();
			$params = [':my_lang'=>$my_lang,':type'=>$type];
			$sql = 'SELECT t1.wifi_id,FORMAT(t1.sale_price/100,2) as sale_price,t1.wifi_flow,t2.wifi_name 
					FROM vcos_wifi_item_flow t1,vcos_wifi_item_language_flow t2 
					WHERE t1.wifi_id=t2.wifi_id AND t1.`status`=1 AND t2.iso=:my_lang AND t1.type=:type';
			$wifi_array = Yii::$app->db->createCommand($sql,$params)->queryAll();
			return $wifi_array;
		}

		
		//Find WifiService Via wifi_id
		public static function FindWifiServiceById($wifi_id,$my_lang='zh_cn')
		{
			$params = [':my_lang'=>$my_lang,':wifi_id'=>$wifi_id];
			$sql = 'SELECT t1.wifi_id,FORMAT(t1.sale_price/100,2) as sale_price,t1.wifi_flow,t2.wifi_name 
					FROM vcos_wifi_item_flow t1,vcos_wifi_item_language_flow t2 
					WHERE t1.wifi_id=t2.wifi_id AND t1.`status`=1 AND t2.iso=:my_lang AND t1.wifi_id=:wifi_id';
			$wifi_item = Yii::$app->db->createCommand($sql,$params)->queryOne();
			return $wifi_item;
		}

		
		//WifiPayment,write pay log to db
		public static function WifiPay($sign,$wifi_id,$type)
		{
			if($type == 1){
				//会员
				$member = MemberService::getMemberbysign($sign);
			}else {
				$member =  MemberService::getCrewBySign($sign);
			}
			
			$wifi_item = self::FindWifiServiceById($wifi_id);

			//获取wifi套餐的价格
			$sale_price = $wifi_item['sale_price'];
			if(!empty($member)){

				//查找用户的余额
				$membership_id = $member['member_id'];
				$membership_code = $member['member_code'];
				$member_money = $member['member_money'];
				//判断用户的钱是否足够支付wifi套餐，
				if($member_money >= ($sale_price * 100) && $member_money >= 0){
					//钱足够，进行支付
					
					//查询用户是否存在
					$find_res = MyCurl::FindUser($member['passport_number']);
					$find_res = json_decode($find_res,true);
					if($find_res['success']===false){
						//没找到用户
						//创建用户,并加入组，对接接口
						//创建一个随机的6位密码，存放在comst
						$comst_password  = rand(100000,999999);
						$create_time = date("Y-m-d H:i:s",time());
						$username = $member['passport_number'];
						
						$res = MyCurl::CreateUser($member,$comst_password);
						$res =  json_decode($res,true);
						if($res['success'] === false){
							return $response['error'] = ['errorCode'=>2,'message'=>$res['Info']];
							die();
						}
						//把用户写入本地数据库中
						$sql = "SELECT * FROM `vcos_comst_wifi` WHERE `username`='$username' ";
						$comst_user = Yii::$app->db->createCommand($sql)->queryOne();
						if($comst_user){
							$sql = "UPDATE `vcos_comst_wifi` SET `password`='$comst_password' WHERE `username`='$username' ";
							Yii::$app->db->createCommand($sql)->execute();
						}else {
							$sql = "INSERT INTO `vcos_comst_wifi` (`username`,`password`,`create_time`) VALUES('$username','$comst_password','$create_time')";
							Yii::$app->db->createCommand($sql)->execute();
						}
					}

					//事务处理
					$transaction = Yii::$app->mdb->beginTransaction();
					try {
						//直接支付
						$money = $member_money - ($sale_price * 100);	//注意转换单位 
						if($type == 1){
							$member['member_money'] = $money;
							$member->save();
						}else{
							$sql = " UPDATE vcos_wifi_crew SET money='$money' WHERE crew_id='{$member['member_id']}' ";
							Yii::$app->mdb->createCommand($sql)->execute();
						}
						
						//先断开连接,避免产生流量记录中会出现负数的情况，所以在充值之前要先断开网络
						//查找comst中$passport对应的idRec
						$idRec = MyCurl::FindidRec($member['passport_number']);
						//断开连接网络
						$disc_json = MyCurl::DisConnect($idRec);
						//查流量
						$check_out_json = MyCurl::CheckFlow($member['passport_number']);
						$check_out_array = json_decode($check_out_json,true);
						$arr = explode("<br>", $check_out_array['data']['feeInfo']);
						//剔除不必要的字符
						$wifi_online_in_flow = str_replace('MB','',explode(": ",$arr[5])[1]);
						$wifi_online_out_flow = str_replace('MB','',explode(": ",$arr[6])[1]);
						$wifi_online_total_flow = str_replace('MB','',explode(": ",$arr[7])[1]);
						//断开连接记录写入DB
						MyWifi::WriteWifiLogoutLogToDB($member,$wifi_online_in_flow,$wifi_online_out_flow,$wifi_online_total_flow);

						//充值wifi对应的钱，对接接口
						MyCurl::RechargeWifi($member['passport_number'],$wifi_item['wifi_flow']);		//comst 充值时按照流量和金额1:1比例

						//记录购买Wifi记录
						self::CreateWifiPayLog($sign,$membership_code,$wifi_item,$type);

						$response['data'] = ['code'=>1,'message'=>'Pay Success!'];
						$transaction->commit();
					}catch (EXception $e){
						$transaction->rollBack();
						$response ['error'] = ['errorCode' => 2,'message' => 'System Wrong!'];
					}
				}else{
					//钱不够,返回错误信息
					$response['error'] = ['errorCode'=>'1','message'=>'Not Enought Money To Pay This Wifi Item!'];
				}
			}else{
				$response['error'] = ['errorCode'=>2,'message'=>'Member does not exist'];
			}
			return $response;
		}


		//write wifi pay log to table vcos_member_order and table vcos_member_order_detail 
		private static function CreateWifiPayLog($sign,$code,$wifi_item,$type)
		{
			$wifi_order_time = time();
			$wifi_order_number  = OrderService::getMemberOrderNO();
			$wifi_order_type=isset($_POST['order_type']) ? $_POST['order_type'] : '3';
			$pay_type = isset($_POST['pay_type']) ? $_POST['pay_type'] : '1';
			$wifi_order_state = 3;
			$order_check_num = md5($sign.$wifi_order_type.$wifi_order_time);
			$myMemberOrder = OrderService::getOrderCheckNum($order_check_num);
			if(empty($myMemberOrder)){
					
				$memberOrder = new MemberOrder();
				$memberOrder->order_serial_num = $wifi_order_number;
				$memberOrder->membership_code = $code;
				$memberOrder->tender_type = $type;
				$memberOrder->totale_price = $wifi_item['sale_price'] * 100;
				$memberOrder->pay_type = $pay_type;
				$memberOrder->order_check_num = $order_check_num;
				$memberOrder->pay_time = date('Y-m-d H:i:s',$wifi_order_time);
				$memberOrder->order_create_time = date('Y-m-d H:i:s',$wifi_order_time);
				$memberOrder->order_status = $wifi_order_state;
				$memberOrder->order_type = $wifi_order_type;
				$memberOrder->receiving_way = 0;
				$memberOrder->save();
		
				$memberOrderDetail = new MemberOrderDetail();
				$memberOrderDetail->order_serial_num = $wifi_order_number;
				$memberOrderDetail->goods_id = $wifi_item['wifi_id'];
				$memberOrderDetail->goods_name = $wifi_item['wifi_name'];
				$memberOrderDetail->goods_price = $wifi_item['sale_price']*100;
				$memberOrderDetail->buy_num = 1;
				$memberOrderDetail->last_change_time = date('Y-m-d H:i:s',$wifi_order_time);
				$memberOrderDetail->save();
			}
		}


		//find wifi login log in db
		public static function FindWifiLoginLog($mcode,$count=5)
		{
			$sql = " SELECT wifi_login_time,wifi_logout_time,FORMAT(wifi_used_total_flow,2) as flow  FROM vcos_wifi_connect_log_flow WHERE membership_code='$mcode' ORDER BY `id` DESC  LIMIT  $count" ;
			$log = Yii::$app->db->createCommand($sql)->queryAll();
			return $log;
		}
		
		
		//find current login status in  comst system and db 
		public static function FindWifiLoginStatus($mcode)
		{
			if((substr($mcode,0,3) == 'TS@') || (substr($mcode, 0,3) == 'ts@') || (substr($mcode, 0,3) == 'TS_') || (substr($mcode, 0,3) == 'ts_')){
				//船员
				$sql  =' SELECT crew_id as member_id,crew_code as member_code,cn_name,smart_card_number, passport_number, crew_password as member_password ,
					crew_email as member_email,mobile_number,money as member_money,crew_credit as member_credit,sign,overdraft_limit,curr_overdraft_amount
					FROM vcos_wifi_crew WHERE crew_code=\''.$mcode.'\' ';
				$membership = Yii::$app->mdb->createCommand($sql)->queryOne();
			}else {
				//会员
				$member = Member::find ()->select ( [
						'sign',
				] )->where ( [
						'member_code' => $mcode
				] )->one ();
				
				$sign = $member['sign'];
				$membership =  MemberService::getMemberbysign($sign);
			}
			
			$passport = $membership['passport_number'];
			
			//接口对接
			//查询用户当前在线状态
			$check_out_json = MyCurl::CheckFlow($passport);
			 
			$check_out_array = json_decode($check_out_json,true);
			$status = false;	//初始值为false 
			
			if($check_out_array['success']){
				$arr = explode("<br>", $check_out_array['data']['feeInfo']);
					
				//剔除不必要的字符
				$wifi_online_in_flow = str_replace('MB','',explode(": ",$arr[5])[1]);
				$wifi_online_out_flow = str_replace('MB','',explode(": ",$arr[6])[1]);
				$wifi_online_total_flow = str_replace('MB','',explode(": ",$arr[7])[1]);
				//获取当前的在线状态
				$status = $check_out_array['data']['isOnline'];
				//如果接口获取的状态是离线，设置数据库中的状态为离线
				if($status === false){
					self::WriteWifiLogoutLogToDB($membership,$wifi_online_in_flow,$wifi_online_out_flow,$wifi_online_total_flow);
				}
			}
			
			return $status;
		}
		
		
		//write login log to db
		public static function WriteWifiLoginLogToDB($membership,$wifi_online_in_flow,$wifi_online_out_flow,$wifi_online_total_flow)
		{
			$membership_id = $membership['member_id'];
			$membership_code = $membership['member_code'];
			$ip_address = MyCurl::getIp();
			$mac_address = '';
			$wifi_login_time = date("Y-m-d H:i:s",time());
			$certification_result='';
			$exit_reason='';
			$exit_type=0;
			$wlanacip='';
			$ssid='';
			
			$online = Yii::$app->db->createCommand()->insert('vcos_wifi_connect_log_flow', [
					'membership_id'	=>$membership_id,
					'membership_code' =>$membership_code,
					'ip_address'=>$ip_address,
					'mac_address'=>$mac_address,
					'wifi_login_time'=>$wifi_login_time,
					'wifi_online_in_flow'=>$wifi_online_in_flow,
					'wifi_online_out_flow'=>$wifi_online_out_flow,
					'wifi_online_total_flow'=>$wifi_online_total_flow,
					'certification_result'=>$certification_result,
					'exit_reason'=>$exit_reason,
					'exit_type'=>$exit_type,
					'wlanacip'=>$wlanacip,
					'ssid'=>$ssid,
			])->execute();
		}
		
		
		//write logout log to db
		public static function WriteWifiLogoutLogToDB($membership,$wifi_online_in_flow,$wifi_online_out_flow,$wifi_online_total_flow)
		{	
			$wifi_logout_time = date("Y-m-d H:i:s",time());
			$exit_type = 1;
			$exit_reason = '';
			$membership_id = $membership['member_id'];
			$membership_code = $membership['member_code'];
			
			$params = [':membership_id'=>$membership_id,':membership_code'=>$membership_code];
			$sql = 'SELECT * FROM vcos_wifi_connect_log_flow WHERE membership_id = :membership_id
							AND membership_code = :membership_code AND exit_type=0 ORDER BY id DESC LIMIT 1 ';
			$wifi = Yii::$app->db->createCommand($sql,$params)->queryOne();
			
			$offline = Yii::$app->db->createCommand()->update('vcos_wifi_connect_log_flow', [
					'wifi_logout_time'=>$wifi_logout_time,
					'exit_type'=>$exit_type,
					'exit_reason'=>$exit_reason,
					'wifi_online_in_flow'=>$wifi_online_in_flow,
					'wifi_online_out_flow'=>$wifi_online_out_flow,
					'wifi_online_total_flow'=>$wifi_online_total_flow,
					'wifi_used_total_flow'=>abs($wifi_online_total_flow - $wifi['wifi_online_total_flow']),
			],[
					'membership_id'=>$membership_id,
					'membership_code'=>$membership_code,
					'id'=>$wifi['id'],
			])->execute();
		}
	}
	