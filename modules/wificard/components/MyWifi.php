<?php
	namespace app\modules\wificard\components;

	use Yii;
	use app\components\MemberService;
	use app\models\Member;
	use app\models\MemberOrder;
	use app\models\MemberOrderDetail;
	use app\components\OrderService;
	use app\modules\wifiservice\components\MyCurl;

	class MyWifi 
	{
		
		//Find All WifiService
		public static function FindWifiService($my_lang='zh_cn')
		{
			$response = array();
			$params = ['my_lang'=>$my_lang];
			$sql = 'SELECT t1.wifi_id,FORMAT(t1.sale_price/100,2) as sale_price,t1.wifi_flow,t2.wifi_name 
					FROM vcos_wifi_item_flow t1,vcos_wifi_item_language_flow t2 
					WHERE t1.wifi_id=t2.wifi_id AND t1.`status`=1 AND t2.iso=:my_lang ';
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
		public static function WifiPay($sign,$wifi_id)
		{
// 			$member = MemberService::getMemberbysign($sign);
			$type = Yii::$app->admin->identity->member_type;
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
					if(!$find_res['data']){
						//没找到用户
						//创建用户,并加入组，对接接口
						$res = MyCurl::CreateUser($member);
						$res =  json_decode($res,true);
						if($res['success'] === false){
							return $response['error'] = ['errorCode'=>2,'message'=>$res['Info']];
							die();
						}
					}

					//事务处理
					$transaction = Yii::$app->db->beginTransaction();
					try {
						//直接支付
						$money = $member_money - ($sale_price * 100);	//注意转换单位 
						if($type == 1){
							$member['member_money'] = $money;
							$member->save();
						}else{
							$sql = " UPDATE vcos_wifi_crew SET money='$money' WHERE crew_id='{$member['member_id']}' ";
							Yii::$app->db->createCommand($sql)->execute();
						}
						

						//充值wifi对应的钱，对接接口
// 						MyCurl::RechargeWifi($member['passport_number'],$sale_price);
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

		
		
		
/*
		//create user in comst system
		private static function CreateWifiUserInComst($member)
		{
			//模拟登录
// 			MyCurl::vcurl(Yii::$app->params['wifi_url'],'status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);

// 			$create_url = "http://192.168.9.250/jsp/um_add/comstserver.awm?";

// 			//UTF-8 转换为 GB2312
// 			$date = iconv('UTF-8','GB2312', date('Y年m月d日',time()));
// 			$LinkName = iconv('UTF-8','GB2312', $member['cn_name']);

// 			$create_user_param = "status=manage&opt=dbcs&dbName=usermanage_umb&subopt=add&Account=".$member['passport_number']."&pwd=".$member['passport_number']."&idUgb=1&isStartAcc=1&LinkName=".$LinkName."&paperType=6&paperNum=".$member['passport_number']."&phone=".$member['mobile_number']."&email=".$member['member_email']."&limitData=".$date;

// 			$create_json = MyCurl::vcurl($create_url,$create_user_param);
// 			$create_json = iconv('GB2312', 'UTF-8', $create_json);

			$create_json = MyCurl::CreateUser($member);
			return $create_json;
		}
*/
		
/*
		//search user in comst system via passport
		public static function FindWifiUserInComst($username)
		{
			//模拟登录
// 			MyCurl::vcurl(Yii::$app->params['wifi_url'],'status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);

// 			$find_url = "http://192.168.9.250/jsp/um_query/comstserver.awm?";
// 			$find_params = "status=manage&opt=dbcs&dbName=usermanage_umb&subopt=query&account=".$username."&IsAccount=1";

// 			$find_json = MyCurl::vcurl($find_url,$find_params);
// 			$find_json = iconv('GB2312', 'UTF-8', $find_json);
			$find_json = MyCurl::FindUser($username);
			return $find_json;
		}
*/

		
/*
		//recharge in comst system via passport 
		private static function RechargeWifiInComst($passport,$price)
		{
// 			//模拟登录
// 			MyCurl::vcurl(Yii::$app->params['wifi_url'],'status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);

// 			//查找comst中$passport对应的idRec
// 			$url = "http://192.168.9.250/jsp/fee_checkout/comstserver.awm?";
// 			$find_params = "status=manage&subopt=checkout&opt=dbcs&dbName=usermanage_umb&admin=".Yii::$app->params['wifi_login_name']."&account=".$passport;
// 			$find_json = MyCurl::vcurl($url,$find_params);
// 			$find_json = iconv('GB2312', 'UTF-8', $find_json);
// 			$res = json_decode($find_json,true);
// 			$idRec = $res['data']['userId'];

// 			//在comst系统中充钱
// 			$pay_params = "admin=".Yii::$app->params['wifi_login_name']."&opt=dbcs&status=manage&subopt=paymoney&dbName=usermanage_umb&idRec=".$idRec."&money=".$price;
// 			$pay_json = MyCurl::vcurl($url,$pay_params);
// 			$pay_json = iconv('GB2312', 'UTF-8', $pay_json);
			$find_json = MyCurl::RechargeWifi($passport,$price);
			
			return $find_json;
		}
*/

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
			$type = Yii::$app->admin->identity->member_type;
			if($type == 1){
				//会员
				$member = Member::find ()->select ( [
						'sign',
				] )->where ( [
						'member_code' => $mcode
				] )->one ();
				$sign = $member['sign'];
				
				$membership = MemberService::getMemberbysign($sign);
			}else {
				$sql = "SELECT sign FROM vcos_wifi_crew WHERE crew_code='$mcode'";
				$sign = Yii::$app->db->createCommand($sql)->queryOne()['sign'];
				
				$membership =  MemberService::getCrewBySign($sign);
			}
			
			
// 			$membership =  MemberService::getMemberbysign($sign);
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
			
// 			$sql = " SELECT * FROM vcos_wifi_connect_log_flow WHERE membership_code='$mcode' ORDER BY `id` DESC ";
// 			$db_status = Yii::$app->db->createCommand($sql)->queryOne();
			return $status;
		}
		
		
		//write login log to db
		public static function WriteWifiLoginLogToDB($membership,$wifi_online_in_flow,$wifi_online_out_flow,$wifi_online_total_flow)
		{
			$membership_id = $membership['member_id'];
			$membership_code = $membership['member_code'];
			$ip_address = '';
			$mac_address = '';
			$wifi_login_time = date("Y-m-d H:i:s",time());
// 			$wifi_logout_time = '';
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
// 					'wifi_logout_time'=>$wifi_logout_time,
					'wifi_online_in_flow'=>$wifi_online_in_flow,
					'wifi_online_out_flow'=>$wifi_online_out_flow,
					'wifi_online_total_flow'=>$wifi_online_total_flow,
// 					'wifi_used_total_flow' => 0,
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
					'wifi_used_total_flow'=>$wifi_online_total_flow - $wifi['wifi_online_total_flow'],
			],[
					'membership_id'=>$membership_id,
					'membership_code'=>$membership_code,
					'id'=>$wifi['id'],
			])->execute();
		}
	}
	