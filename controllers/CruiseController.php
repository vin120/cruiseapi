<?php

namespace app\controllers;


use Yii;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use yii\rest\ActiveController;
use yii\helpers\ArrayHelper;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use app\components\MemberService;
use app\components\OrderService;
use app\components\SurveyService;
use app\models\MemberOrder;
use app\models\MemberOrderDetail;

class CruiseController extends MyActiveController
{
	/**
	 * 显示所有客房
	 */
	public function actionFindallroom(){
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		
		$sql = "SELECT a.room_id, b.room_name,b.describe,b.img_url FROM vcos_room a LEFT JOIN vcos_room_language  b ON a.room_id = b.room_id WHERE b.iso = '".$my_lang."' AND a.room_state = '1'";
		
		$room_type_array = Yii::$app->db->createCommand($sql)->queryAll();
		
		$response['data'] = $room_type_array;
		
		return  $response;
		
	}
	
	/**
	 * 客房详情
	 */
	public function actionFindroomdetailbyid()
	{
		$room_id = isset($_POST['room_id']) ? $_POST['room_id'] : '';
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
			
		$response = array();
		
		if(!empty($room_id)){
			$sql = "SELECT a.room_id,a.room_img_url, b.room_describe FROM vcos_room_detail a 
					LEFT JOIN vcos_room_detail_language b ON a.detail_id = b.detail_id 
					WHERE b.iso = '".$my_lang."' AND a.detail_state = '1' AND a.room_id = '{$room_id}'";
			$room_array = Yii::$app->db->createCommand($sql)->queryAll();
				
			$response['data'] = $room_array;
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'room_id can not be empty','value'=>$_POST);
		}
		
		
		return  $response;
	}
	
	/**
	 * 显示所有服务时间
	 */
	public function actionFindservicetime()
	{
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		
		$sql = "SELECT b.service_department, b.service_opening_time, a.service_tel, b.service_address 
				FROM vcos_service_time a LEFT JOIN vcos_service_time_language b ON a.service_id = b.service_id 
				WHERE b.iso = '".$my_lang."' AND a.service_state = '1'";
           
		$room_type_array = Yii::$app->db->createCommand($sql)->queryAll();
		
		$response['data'] = $room_type_array;
		
		return  $response;
	}
	
	/**
	 * 会员设施
	 */
	public function actionFindmeetingroom(){
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		
		$sql = "SELECT a.img_url, b.title FROM vcos_meetingroom a LEFT JOIN vcos_meetingroom_language b ON a.m_id = b.m_id
				WHERE b.iso = '".$my_lang."' AND a.state = '1'";
		
		 
		$room_type_array = Yii::$app->db->createCommand($sql)->queryAll();
		
		$response['data'] = $room_type_array;
		
		return  $response;
	}
	
	/**
	 * 邮轮介绍
	 */
	public function actionFindcruiseintroduce(){

		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		
		$sql = "SELECT a.cruise_img, b.cruise_info FROM vcos_cruise_info a LEFT JOIN vcos_cruise_info_language b ON a.info_id = b.info_id 
				WHERE b.iso = '".$my_lang."' AND a.state = '1' ORDER BY a.info_id ASC";
		
		$cruise_array = Yii::$app->db->createCommand($sql)->queryAll();
		
		$response['data'] = $cruise_array;
		
		return  $response;
		
	}
	
	/**
	 * 所有航线
	 */
	public function actionFindallcruiseline(){
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		
		$sql = "SELECT a.line_id, b.line_name FROM vcos_line a LEFT JOIN vcos_line_language b ON a.line_id = b.line_id 
				WHERE b.iso = '".$my_lang."' AND a.state = '1' ORDER BY a.line_id ASC";
		
		
		$route_line_array = Yii::$app->db->createCommand($sql)->queryAll();
		
		$response['data'] = $route_line_array;
		
		return  $response;
		
	}
	
	/**
	 * 航线详情
	 */
	public function actionFindcruiselinedetailbyid(){
		
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		$line_id  = isset($_POST['line_id']) ? $_POST['line_id'] : '';

		$response = array();
		
		if(!empty($line_id)){
			$sql = "SELECT b.title,b.img_url,b.content FROM vcos_line_detail a LEFT JOIN vcos_line_detail_language b ON a.detail_id = b.detail_id 
					WHERE b.iso = '".$my_lang."' AND a.detail_state = '1' AND a.line_id = '{$line_id}' ORDER BY a.sequence ASC";
			
			$line_detail_array = Yii::$app->db->createCommand($sql)->queryAll();
			$response['data'] = $line_detail_array;
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'line_id can not be empty');
		}
		
		
		return  $response;
	}
	
	/**
	 * 邮轮所有港口
	 */
	public function actionFindallcruiseport(){
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		
		$sql = "SELECT a.port_id, b.port_name,b.describe,b.img_url FROM vcos_port a LEFT JOIN vcos_port_language b ON a.port_id = b.port_id 
				WHERE b.iso = '".$my_lang."' AND a.port_state = '1'";
		
		$port_array = Yii::$app->db->createCommand($sql)->queryAll();
		
		$response['data'] = $port_array;
		
		return  $response;
	}
	
	/**
	 * 港口详情
	 */
	public function actionFindcruiseportdetail(){
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		$port_id  = isset($_POST['port_id']) ? $_POST['port_id'] : '';
		
		$response = array();
		
		if(!empty($port_id)){
			$sql = "SELECT a.detail_img_url, b.detail FROM vcos_port_detail a LEFT JOIN vcos_port_detail_language b ON a.detail_id = b.detail_id 
					WHERE b.iso = '".$my_lang."' AND a.port_id = '{$port_id}' AND a.detail_state = '1'";
		
				
			$port_detail_array = Yii::$app->db->createCommand($sql)->queryAll();
			$response['data'] = $port_detail_array;
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'port_id can not be empty');
		}
		
		return  $response;
		
	}
	

	/**
	 * 显示甲板图
	 */
	public function actionFindallcruisedeck()
	{
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		
		$sql_deck = 'SELECT a.deck_id,a.deck_layer,b.img_url,b.deck_name 
				FROM vcos_cruise_deck a,vcos_cruise_deck_language b 
				WHERE a.deck_id = b.deck_id AND a.deck_state=1 AND b.iso=\''.$my_lang.'\'  ORDER BY a.deck_layer DESC';
		
		$deck_array = Yii::$app->db->createCommand($sql_deck)->queryAll();

		$sql_deck_in_array = array();
		foreach ($deck_array as $deck)
		{
			$sql_deck_in_array[] = $deck['deck_id'];
		}
		
		if(!empty($sql_deck_in_array)){
			$in_value = join(',', $sql_deck_in_array);
			$sql_deck_point = 'SELECT a.deck_id,a.deck_point_id,a.deck_number,b.deck_point_name 
					FROM vcos_cruise_deck_point a,vcos_cruise_deck_point_language b
					WHERE a.deck_point_id = b.deck_point_id AND a.deck_point_state=1 AND b.iso=\''.$my_lang.'\'  
					AND a.deck_id IN ('.$in_value.')';
			$deck_point_array = Yii::$app->db->createCommand($sql_deck_point)->queryAll();
			
		}
		
		for($i=0;$i<count($deck_array);$i++){
			
			$deck_array[$i]['deck_point_items'] = array();//先定义deck_point_items
			
			$temp_count = 0;
			foreach ($deck_point_array as $key => $deck_point){
				if($deck_array[$i]['deck_id'] == $deck_point['deck_id']){
					$deck_array[$i]['deck_point_items'][$temp_count]['deck_point_id'] = $deck_point['deck_point_id'];
					$deck_array[$i]['deck_point_items'][$temp_count]['deck_number'] = $deck_point['deck_number'];
					$deck_array[$i]['deck_point_items'][$temp_count]['deck_point_name'] = $deck_point['deck_point_name'];
					$temp_count++;
				}
			}
		}
		
		$response['data'] = $deck_array;
		
		return $response;
	}
	
	/**
	 * 显示甲板点介绍信息
	 */
	public function actionFindcruisedeckpoint()
	{
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		$deck_point_id  = isset($_POST['deck_point_id']) ? $_POST['deck_point_id'] : '';
		
		$response = array();
		
		if(!empty($deck_point_id)){
			$sql = 'SELECT a.deck_id,a.deck_point_id,a.deck_number,b.deck_point_name,b.img_url,b.deck_point_describe,b.deck_point_info
					FROM vcos_cruise_deck_point a,vcos_cruise_deck_point_language b
					WHERE a.deck_point_id = b.deck_point_id AND a.deck_point_state=1 AND b.iso=\''.$my_lang.'\'  
					AND a.deck_point_id = '.$deck_point_id;
		
			$deck_point = Yii::$app->db->createCommand($sql)->queryOne();
			$response['data'] = $deck_point;
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'desc_point_id can not be empty');
		}
		
		return  $response;
	}
	
	/**
	 * 安全分类
	 */
	public function actionFindallsafe()
	{
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
				
		$sql = 'SELECT a.safe_id,b.safe_title FROM  vcos_safe a,vcos_safe_language b 
				WHERE a.safe_id = b.safe_id AND a.safe_state = 1 AND b.iso=\''.$my_lang.'\''; 
		$safe_array = Yii::$app->db->createCommand($sql)->queryAll();
		$response['data'] = $safe_array;
		
		return $response;
	}
	
	/**
	 * 安全详情
	 */
	public function actionFindsafedetailbyid()
	{
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		$safe_id  = isset($_POST['safe_id']) ? $_POST['safe_id'] : '';
		
		$response = array();
		if(!empty($safe_id)){
			$sql = "SELECT b.safe_title,b.safe_content FROM vcos_safe_language b  
					WHERE b.iso = '".$my_lang."' AND b.safe_id = '{$safe_id}'";
			
			$safe = Yii::$app->db->createCommand($sql)->queryOne();
			$response['data'] = $safe;
			
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'desc_point_id can not be empty');
		}
		
		return $response;
	}
	
	/**
	 * 获得帮助信息分类名
	 */
	public function actionFindallhelpcategory()
	{
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		
		$sql = 'SELECT b.category_id,b.category_name FROM vcos_help_category a,vcos_help_category_language b 
				WHERE a.id=b.category_id AND a.state=1 AND b.iso=\''.$my_lang.'\'';
		
		$help_category_array = Yii::$app->db->createCommand($sql)->queryAll();
		$response['data'] = $help_category_array;
		
		return $response;
	}
	/**
	 * 帮助信息
	 */
	public function actionFindallhelp()
	{
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		$category_id = isset($_POST['category_id']) ? $_POST['category_id'] : '';
		
		$response = array();
		if(!empty($category_id)){
			$sql = 'SELECT a.cm_id as id,b.cm_title as ask,b.cm_reply as reply 
					FROM vcos_common_problems a, vcos_common_problems_language b 
					WHERE a.cm_id = b.cm_id AND a.cm_state =1 AND a.category_id = '.$category_id.' AND b.iso=\''.$my_lang.'\'';
			
			$help_array = Yii::$app->db->createCommand($sql)->queryAll();
			$response['data'] = $help_array;
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'category_id can not be empty');
		}
		
		return $response;
	}
	
	
	/**
	 * 获得游客须知分类名
	 */
	public function actionFindnoticetovisitorscategory()
	{
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
	
		$sql = 'SELECT b.category_id,b.category_name FROM vcos_notice_to_visitors_category a,vcos_notice_to_visitors_category_language b
				WHERE a.id=b.category_id AND a.state=1 AND b.iso=\''.$my_lang.'\'';
	
		$ntv_category_array = Yii::$app->db->createCommand($sql)->queryAll();
		$response['data'] = $ntv_category_array;
	
		return $response;
	}
	/**
	 * 游客须知
	 */
	public function actionFindnoticetovisitors()
	{
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		$category_id = isset($_POST['category_id']) ? $_POST['category_id'] : '';
		
		$response = array();
		if(!empty($category_id)){
			$sql = 'SELECT a.id,a.sort_order,b.img_url,b.content FROM vcos_notice_to_visitors a, vcos_notice_to_visitors_language b 
					WHERE a.id=b.n_id AND iso=\''.$my_lang.'\' AND a.category_id = '.$category_id.' AND a.state=1 ORDER BY a.sort_order';
			
			$help_array = Yii::$app->db->createCommand($sql)->queryAll();
			$response['data'] = $help_array;
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'category_id can not be empty');
		}
		
		return $response;
	}
	
	/**
	 * 所有文章信息（邮轮动态）
	 */
	public function actionFindallarticle()
	{
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		$my_time = isset($_POST['date']) ? $_POST['date'] : time();
		
		$date_time = date('Y-m-d H:i:s', $my_time);
		
		$sql = 'SELECT a.article_id,a.article_start_time,a.article_end_time,a.article_img_url,b.article_title,b.article_abstract 
				FROM vcos_article a,vcos_article_language b WHERE a.article_id=b.article_id 
				AND a.article_start_time<\''.$date_time.'\' AND a.article_end_time>\''.$date_time.'\' AND b.iso=\''.$my_lang.'\' AND a.article_state=1 ORDER BY a.article_start_time DESC';

		$article_array = Yii::$app->db->createCommand($sql)->queryAll();
		$response['data'] = $article_array;
		
		return $response;
	}
	
	/**
	 * 获得文章详情（邮轮动态详情）
	 */
	public function actionFindarticlebyid()
	{
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		$article_id = isset($_POST['id']) ? $_POST['id'] : '';
		
		$response = array();
		if(!empty($article_id)){
			$sql = 'SELECT a.article_id,a.article_start_time,a.article_end_time,a.article_img_url,b.article_title,b.article_abstract,b.article_content  
				FROM vcos_article a,vcos_article_language b WHERE a.article_id=b.article_id 
				AND a.article_id=\''.$article_id.'\' AND b.iso=\''.$my_lang.'\'';
		
			
			$article = Yii::$app->db->createCommand($sql)->queryOne();
			$response['data'] = $article;
			
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'id can not be empty');
		}
		
		return $response;
	}
	
	/**
	 * 获取城市天气
	 */
	public function actionFindweatherbycityid()
	{
		$curr_time = time();
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		$city_id = isset($_POST['city_id']) ? $_POST['city_id'] : '';

		$response = array();
		if(!empty($city_id)){
			$my_min = isset($_POST['min_date']) ? $_POST['min_date'] : $curr_time;
			$my_max = isset($_POST['max_date']) ? $_POST['max_date'] : $curr_time;
			
			$min_date_time = date('Y-m-d H:i:s', $my_min);
			$max_date_time = date('Y-m-d H:i:s', $my_max);
				
			$sql = 'SELECT d.city_name,b.weather_id,b.weather_name,b.wind_scale,b.wind_direction,b.record_start_time start_time,b.record_end_time as end_time,
					b.record_temperature_min lowest,b.record_temperature_max highest 
					FROM vcos_weather_record b, vcos_strategy_city c,vcos_strategy_city_language d
					WHERE b.city_id = c.city_id AND c.city_id=d.city_id
					AND b.record_start_time <= \''.$min_date_time.'\' AND b.record_end_time >= \''.$max_date_time.'\'
					AND b.city_id ='.$city_id.' AND d.iso=\''.$my_lang.'\'  ORDER BY b.record_start_time';
			
			$weather_array = Yii::$app->db->createCommand($sql)->queryAll();
			$response['data'] = $weather_array;
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'city id can not be empty');
		}
		return $response;
		
	}
	
	/**
	 * 获取广告信息
	 */
	public function actionFindimagebyposition()
	{
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		$position_id = isset($_POST['position_id']) ? $_POST['position_id'] : '1';

		$response = array();
		if(!empty($position_id))
		{
			$sql = 'SELECT a.ad_id,a.ad_position,b.img_url,b.link_url,a.link_type FROM vcos_ad a, vcos_ad_language b
				WHERE a.ad_id=b.ad_id AND a.ad_position IN ('.$position_id.') AND a.ad_state=1 AND b.iso=\''.$my_lang.'\'';
			$img_array = Yii::$app->db->createCommand($sql)->queryAll();
			
			$response['data'] = $img_array;
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'position_id not is a number or not is a numeric string');
		}	
		
		return $response;
	}
	
	/**
	 * 获取wifi服务项
	 */
	public function actionFindwifiservice()
	{
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		$sign = isset($_POST['sign']) ? $_POST['sign'] : '';
		
		$response = array();
		
// 		$sql = 'SELECT t1.wifi_id,(t1.sale_price/100) as sale_price,t1.wifi_time,t2.wifi_name FROM vcos_wifi_item t1,vcos_wifi_item_language t2 
// 				WHERE t1.wifi_id=t2.wifi_id AND t1.`status`=1 AND t2.iso=\''.$my_lang.'\'';
// 		$wifi_array = Yii::$app->db->createCommand($sql)->queryAll();
		
		
		$params = ['my_lang'=>$my_lang];
		$sql = 'SELECT t1.wifi_id,(t1.sale_price/100) as sale_price,t1.wifi_time,t2.wifi_name 
				FROM vcos_wifi_item t1,vcos_wifi_item_language t2 
				WHERE t1.wifi_id=t2.wifi_id AND t1.`status`=1 AND t2.iso=:my_lang ';
		$wifi_array = Yii::$app->db->createCommand($sql,$params)->queryAll();
		
			
		$response['data']['wifi'] = $wifi_array;
		
		if(!empty($sign))
		{
			$member = MemberService::getMemberbysign($sign);
			if(!empty($member)){
				$member_money = $member->member_money;
				
				$response['data']['member_money'] = $member_money / 100;
			}else{
				$response['error'] = array('error_code'=>1,'message'=>'Member does not exist');
			}
		}else {
			$response['error'] = array('error_code'=>1,'message'=>'sign can not be empty');
		}
		
		return $response;
	}
	
	
	/**
	 * wifi连接
	 */
	public function actionWificonnect()
	{
		$sign = isset($_POST['sign']) ? $_POST['sign'] : '';
		$exit_type = isset($_POST['exit_type']) ? $_POST['exit_type'] : ''; //0 on,1,2,3 off
		$wifi_service_name = isset($_POST['wifi_service_name']) ? $_POST['wifi_service_name'] : '';
		$wifi_time  = isset($_POST['wifi_time']) ? $_POST['wifi_time'] : '0';
		$ip_address = isset($_POST['ip_address']) ? $_POST['ip_address'] : '';
		$mac_address = isset($_POST['mac_address']) ? $_POST['mac_address'] : '';
		$certification_result = isset($_POST['certification_result']) ? $_POST['certification_result'] : '';
		$exit_reason = isset($_POST['exit_reason']) ? $_POST['exit_reason'] : '';
		$wlanacip = isset($_POST['wlanacip']) ? $_POST['wlanacip'] : '';
		$ssid =  isset($_POST['ssid']) ? $_POST['ssid'] : '';

		
		if(!empty($sign))
		{
			$member = MemberService::getMemberbysign($sign);
			if(!empty($member)){
				$membership_id = $member->member_id;
				$membership_code = $member->member_code;
				
				
// 				$totaltime_sql = "SELECT total_wifi_time FROM vcos_wifi_service WHERE membership_id=".$membership_id." AND membership_code=".$membership_code;
// 				$total_wifi_time = Yii::$app->db->createCommand($totaltime_sql)->queryOne();
				
				$params_totalime = ['membership_id'=>$membership_id,'membership_code'=>$membership_code];
				$totaltime_sql =' SELECT total_wifi_time FROM vcos_wifi_service WHERE membership_id=:membership_id AND membership_code= :membership_code ';
				$total_wifi_time = Yii::$app->db->createCommand($totaltime_sql,$params_totalime)->queryOne();
				
				
				if(0 == $exit_type){
					
					//查询wifi total_time，如果时间不足，断开链接
					if($total_wifi_time != '' && $total_wifi_time['total_wifi_time'] > 0)
					{
						//开始上网， todo
						
						
						//记录上网开始时间 
						$wifi_login_time = time();
						$wifi_logout_time = 0;
						$wifi_online_time = 0;
						
// 						$online_sql = ' INSERT INTO vcos_wifi_connect_log (membership_id,membership_code,wifi_service_name,wifi_time,ip_address,
// 							mac_address,wifi_login_time,wifi_logout_time,wifi_online_time,certification_result,exit_reason,exit_type,wlanacip,ssid)
// 							VALUES(\''.$membership_id.'\',\''.$membership_code.'\',\''.$wifi_service_name.'\',\''.$wifi_time.'\',
// 									\''.$ip_address.'\',\''.$mac_address.'\',\''.$wifi_login_time.'\',\''.$wifi_logout_time.'\',
// 									\''.$wifi_online_time.'\',\''.$certification_result.'\',\''.$exit_reason.'\',\''.$exit_type.'\',
// 									\''.$wlanacip.'\',\''.$ssid.'\')';
// 						$online = Yii::$app->db->createCommand($online_sql)->execute();
						
						
						$online = Yii::$app->db->createCommand()->insert('vcos_wifi_connect_log', [
								'membership_id'	=>$membership_id,
								'membership_code' =>$membership_code,
								'wifi_service_name' =>$wifi_service_name,
								'wifi_time'=>$wifi_time,
								'ip_address'=>$ip_address,
								'mac_address'=>$mac_address,
								'wifi_login_time'=>$wifi_login_time,
								'wifi_logout_time'=>$wifi_logout_time,
								'wifi_online_time'=>$wifi_online_time,
								'certification_result'=>$certification_result,
								'exit_reason'=>$exit_reason,
								'exit_type'=>$exit_type,
								'wlanacip'=>$wlanacip,
								'ssid'=>$ssid,
						])->execute();
						
						
						if($online)
						{
							$response['data'] = $total_wifi_time;
						}
						else {
							$response['error'] = array('error_code'=>1,'message'=>'online failed');
						}
					}
					else
					{
						$response['error'] = array('error_code'=>1,'message'=>'wifi time finish');
					}

				}else{
					//停止上网  todo
					

					//停止记录上网时间
					$wifi_logout_time = time();
// 					$logintime_sql = 'SELECT wifi_login_time,id FROM vcos_wifi_connect_log WHERE membership_id=\''.$membership_id.'\' 
// 							AND membership_code=\''.$membership_code.'\' ORDER BY id DESC LIMIT 1 ';
// 					$wifi_login_time = Yii::$app->db->createCommand($logintime_sql)->queryOne();
					
					
					$params_logintime = [':membership_id'=>$membership_id,':membership_code'=>$membership_code];
					$sql_logintime = 'SELECT wifi_login_time,id FROM vcos_wifi_connect_log WHERE membership_id = :membership_id
							AND membership_code = :membership_code ORDER BY id DESC LIMIT 1 ';
					$wifi_login_time = Yii::$app->db->createCommand($sql_logintime,$params_logintime)->queryOne();
					
					
					
					$wifi_online_time = $wifi_logout_time - $wifi_login_time['wifi_login_time'];
					$totaltime = $total_wifi_time['total_wifi_time'] - $wifi_online_time;
					
					$transaction=Yii::$app->db->beginTransaction();
					try{
					
// 						$offline_sql = ' UPDATE vcos_wifi_connect_log  SET wifi_logout_time=\''.$wifi_logout_time.'\',wifi_online_time=\''.$wifi_online_time.'\' 
// 								,exit_type=\''.$exit_type.'\',exit_reason=\''.$exit_reason.'\' WHERE membership_id=\''.$membership_id.
// 								'\' AND membership_code=\''.$membership_code.'\' AND id=\''.$wifi_login_time["id"].'\' LIMIT 1 ';
// 						$offline = Yii::$app->db->createCommand($offline_sql)->execute();
						
						
						$offline = Yii::$app->db->createCommand()->update('vcos_wifi_connect_log', [
								'wifi_logout_time'=>$wifi_logout_time,
								'wifi_online_time'=>$wifi_online_time,
								'exit_type'=>$exit_type,
								'exit_reason'=>$exit_reason,
						],[
								'membership_id'=>$membership_id,
								'membership_code'=>$membership_code,
								'id'=>$wifi_login_time['id'],
						])->execute();
						
						
// 						$toaltime_sql = 'UPDATE vcos_wifi_service SET total_wifi_time=\''.$totaltime.'\' WHERE membership_id=\''.$membership_id.'\' AND membership_code=\''.$membership_code.'\' ';
// 						$total_time = Yii::$app->db->createCommand($toaltime_sql)->execute();
						
						$total_time = Yii::$app->db->createCommand()->update('vcos_wifi_service',[
								'total_wifi_time'=>$totaltime,
						],[
								'membership_id'=>$membership_id,
								'membership_code'=>$membership_code,
						])->execute();
						
						
						$params_wifi_connt = [':membership_id'=>$membership_id,':membership_code'=>$membership_code];
						$conntlog_sql = 'SELECT wifi_login_time,wifi_logout_time,wifi_online_time FROM vcos_wifi_connect_log WHERE membership_id= :membership_id AND membership_code= :membership_code  ORDER BY id DESC LIMIT 5';
						$wifi_connt_log = Yii::$app->db->createCommand($conntlog_sql,$params_wifi_connt)->queryAll();

						$response['data'] = $wifi_connt_log;
						
						$transaction->commit();
					}catch (Exception $e)
					{
						$response['error'] = array('error_code'=>1,'message'=>'error exist');
						$transaction->rollBack();
					}
				}

			}else{
				$response['error'] = array('error_code'=>1,'message'=>'Member does not exist');
			}
		}else {
			$response['error'] = array('error_code'=>1,'message'=>'sign can not be empty');
		}
		
		return $response;
		
	}
	
	/**
	 * wifi   网络连接
	 */
	public function actionFindwifistatus()
	{
		$sign = isset($_POST['sign']) ? $_POST['sign'] : '';

		if(!empty($sign))
		{
			$member = MemberService::getMemberbysign($sign);
			$response = array();
			if(!empty($member)){
				$membership_id = $member->member_id;
				$membership_code = $member->member_code;
				
				$wifiexit_sql = 'SELECT exit_type FROM vcos_wifi_connect_log WHERE membership_id=\''.$membership_id.'\' AND membership_code=\''.$membership_code.'\' ORDER BY id DESC LIMIT 1 ' ;
				$wifiexit = Yii::$app->db->createCommand($wifiexit_sql)->queryOne();
				
				$wifitime_sql  = 'SELECT total_wifi_time FROM vcos_wifi_service WHERE membership_id=\''.$membership_id.'\' AND membership_code=\''.$membership_code.'\'';
				$wifi_time = Yii::$app->db->createCommand($wifitime_sql)->queryOne();
				
				$conntlog_sql = 'SELECT wifi_login_time,wifi_logout_time,wifi_online_time FROM vcos_wifi_connect_log WHERE membership_id=\''.$membership_id.'\' AND membership_code=\''.$membership_code.'\' ORDER BY id DESC LIMIT 5';
				$wifi_connt_log = Yii::$app->db->createCommand($conntlog_sql)->queryAll();
				

// 				$orderlog_sql = 'SELECT wifi_order_time,wifi_service_name FROM vcos_wifi_service_order WHERE  membership_id=\''.$membership_id.'\' AND membership_code=\''.$membership_code.'\' ORDER BY id DESC LIMIT 5';
// 				$wifi_order_log = Yii::$app->db->createCommand($orderlog_sql)->queryAll();
				
				$params_orderlog = ['membership_code'=>$membership_code];
				$orderlog_sql = ' SELECT a.goods_name,b.order_create_time FROM vcos_member_order_detail a,vcos_member_order b WHERE b.membership_code = :membership_code 
						AND a.order_serial_num=b.order_serial_num AND b.order_type=3 LIMIT 5';
				$wifi_order_log = Yii::$app->db->createCommand($orderlog_sql,$params_orderlog)->queryAll();
	
				if($wifiexit === false){
					$wifiexit['exit_type'] = 0;
				}
				if($wifi_time === false){
					$wifi_time['total_wifi_time'] = 0;
				}
				
				//得到wifi的情况 ，并返回
				$response['data']['connt_status'] = $wifiexit;
				$response['data']['wifi_time'] = $wifi_time;
				$response['data']['wifi_connt_log'] = $wifi_connt_log;
				$response['data']['wifi_order_log'] = $wifi_order_log;
				$response['data']['wifi_traffic'] = 1024;
				
			}else{
				$response['error'] = array('error_code'=>1,'message'=>'Member does not exist');
			}
		}else {
			$response['error'] = array('error_code'=>1,'message'=>'sign can not be empty');
		}
		
		return $response;
		
	}
	
	/***
	 * wifi 网上订购
	 */
	 
	public function actionWifipay()
	{
		$sign = isset($_POST['sign']) ? $_POST['sign'] : '';
		$wifi_service_name = isset($_POST['wifi_service_name']) ? $_POST['wifi_service_name'] : '';
		$wifi_service_price = isset($_POST['wifi_service_price']) ? $_POST['wifi_service_price']*100 : '';
		$wifi_service_time = isset($_POST['wifi_service_time']) ? $_POST['wifi_service_time'] : '';
		$wifi_id = isset($_POST['wifi_id']) ? $_POST['wifi_id'] : '';

		//查找 member's money 和  wifi total time 
		if(!empty($sign)) 
		{
			$member = MemberService::getMemberbysign($sign);
			if(!empty($member)){
				$membership_id = $member->member_id;
				$membership_code = $member->member_code;
				$member_money = $member->member_money;
				$mobile_number = $member->mobile_number;
				$overdraft_limit = $member->overdraft_limit;
				$curr_overdraft_amount = $member->curr_overdraft_amount;
				
				//判断会员余额和透支额度是否够支付账单
				if($wifi_service_price <= $member_money + $member->overdraft_limit - $member->curr_overdraft_amount && $member_money >= 0)
				{
					// 够支付
					// 查找 total_wifi_time
					$totaltime_sql = 'SELECT total_wifi_time FROM vcos_wifi_service WHERE membership_id=\''.$membership_id.'\' AND membership_code=\''.$membership_code.'\'';
					$tmp_total_wifi_time = Yii::$app->db->createCommand($totaltime_sql)->queryOne();
					if($tmp_total_wifi_time)
					{
						$total_wifi_time = $tmp_total_wifi_time['total_wifi_time'];
					}else {
						$total_wifi_time = 0;
					}
					
					// 查找wifi service 是否存在，如果存在，就update，否则，就insert
					$wifiservice_sql = 'SELECT * FROM vcos_wifi_service  WHERE membership_id=\''.$membership_id.'\' AND membership_code=\''.$membership_code.'\'';
					$wifiservice = Yii::$app->db->createCommand($wifiservice_sql)->queryOne();
						
					$transaction=Yii::$app->db->beginTransaction();
					try{
						
						if($wifi_service_price <= $member_money){
							// 会员余额够支付，直接支付
							// 减少 member_money
							$member->member_money = $member_money - $wifi_service_price;
						}else {
							//会员余额不够支付，使用会员余额+信用额度支付
							$draft = $wifi_service_price - $member_money;
							$member->member_money = 0;
							$member->curr_overdraft_amount = $curr_overdraft_amount + $draft;
						}
						
						$member->save();
					
						//添加 total_wifi_time
						$total_time = $total_wifi_time + $wifi_service_time;
						if($wifiservice)
						{
							//wifi service 存在,update
							$updatetime_sql = 'UPDATE vcos_wifi_service SET total_wifi_time=\''.$total_time.'\'  WHERE membership_id=\''.$membership_id.'\' AND membership_code=\''.$membership_code.'\' LIMIT 1';
							$updatetime = Yii::$app->db->createCommand($updatetime_sql)->execute();
							$response['data'] = array('code'=>1,'message'=>'update wifi service ');
						}else {
							//wifi service 不存在,insert
							$inserttime_sql = ' INSERT INTO vcos_wifi_service(membership_id,membership_code,total_wifi_time,mobile_number ) VALUES(\''.$membership_id.'\',\''.$membership_code.'\',\''.$total_time.'\',\''.$mobile_number.'\') ' ;
							$inserttime = Yii::$app->db->createCommand($inserttime_sql)->execute();
							$response['data'] = array('code'=>1,'message'=>'insert wifi service ');
						}
					
					
						//  记录 buy_wifi_log
						$wifi_order_time = time();
						$wifi_order_number  = OrderService::getMemberOrderNO();
						$wifi_order_type=isset($_POST['order_type']) ? $_POST['order_type'] : '3';
						$order_price = $wifi_service_price;
						$pay_type = isset($_POST['pay_type']) ? $_POST['pay_type'] : '1';
						$wifi_order_state = 3;
					
					
						$order_check_num = md5($sign.$wifi_order_type.$wifi_order_time);
						$myMemberOrder = OrderService::getOrderCheckNum($order_check_num);
						if(empty($myMemberOrder)){
								
							$memberOrder = new MemberOrder();
							$memberOrder->order_serial_num = $wifi_order_number;
							$memberOrder->membership_code = $member->member_code;
							$memberOrder->totale_price = $wifi_service_price;
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
							$memberOrderDetail->goods_id = $wifi_id;
							$memberOrderDetail->goods_name = $wifi_service_name;
							$memberOrderDetail->goods_price = $wifi_service_price;
							$memberOrderDetail->buy_num = 1;
							$memberOrderDetail->last_change_time = date('Y-m-d H:i:s',$wifi_order_time);
							$memberOrderDetail->save();
					
							$response['data'] = array('code'=>1,'message'=>'pay success');
						}else {
							$response['error'] = array('error_code'=>2,'message'=>'Please do not submit a duplicate');
						}
					
						$transaction->commit();
					}catch (Exception $e)
					{
						$response['error'] = array('error_code'=>1,'message'=>'error exist');
						$transaction->rollBack();
					}
				}else {
					$response['error'] = array('error_code'=>1,'message'=>'Not enought money ');
				}
			}else{
				$response['error'] = array('error_code'=>1,'message'=>'Member does not exist');
			}
		}else {
			$response['error'] = array('error_code'=>1,'message'=>'sign can not be empty');
		}
		return $response;
	}
	
	
	/***
	 * 获取评价反馈项
	 */
	
	public function actionFindsurvey()
	{
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		$sign = isset($_POST['sign']) ? $_POST['sign'] : '';
	
		$response = array();
		
		$sql = 'SELECT s2.survey_id,s2.survey_title 
				FROM vcos_survey_language s2,vcos_survey s1 
				WHERE s2.survey_id = s1.survey_id AND s1.survey_state=1 
				AND s2.iso=\''.$my_lang.'\'';
		
		$survey_array = Yii::$app->db->createCommand($sql)->queryAll();
			
		$response['data']['survey'] = $survey_array;

		return $response;
	}
	
	
	/***
	 * 提交反馈
	 */
	public function actionCommitsurvey()
	{
//     	$data='{
// 				"sign":"12",
// 				"comment_content":"卫生不好",
// 				"survey":[
// 					{
// 						"survey_id":"35",
// 						"star_value":"5"
// 					},
// 					{
// 						"survey_id":"29",
// 						"star_value":"4"
// 					},
// 					{
// 						"survey_id":"31",
// 						"star_value":"1"
// 					}
// 				]
// 			}';
		$data = $_POST['data'];
		
		$data_array = json_decode($data,true);
		$response = array();
		
		
		if(!empty($data_array))
		{
			$sign = $data_array['sign'];
			$comment_type_id = "1";
			
			$member = MemberService::getMemberbysign($sign);
			if(!empty($member)){
				
				if(isset($data_array['survey']) && !empty($data_array['comment_content']) ){
					$survey = $data_array['survey'];
					$comment_content = $data_array['comment_content'];
					foreach ($survey as $mysurvey){
						$survey_id = $mysurvey['survey_id'];
						$star_value = $mysurvey['star_value'];
						SurveyService::setSurveyRecode($survey_id,$member->member_code,$star_value);
					}
					SurveyService::setComment($comment_content,$member->member_code,$comment_type_id);
					
					$response['data'] = array('code'=>1,'message'=>'comment success');
				}else {
					$response['error'] = array('error_code'=>1,'message'=>'survey and comment_connect can not be empty');
				}
				
			}else {
				$response['error'] = array('error_code'=>1,'message'=>'Member does not exist');
			}
		}else {
			$response['error'] = array('error_code'=>1,'message'=>'data can not be empty');
		}
	
		return $response;
		
	}
	

	
	/***
	 *  获取电话服务
	 */
	public function actionGetphoneservice()
	{
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		$sign = isset($_POST['sign']) ? $_POST['sign'] : '';
		
		$response = array();
		
		
		$params = ['my_lang'=>$my_lang];
		$sql = 'SELECT t1.tel_id,(t1.sale_price/100) as sale_price,t1.tel_time,t2.tel_name
				FROM vcos_tel_item t1,vcos_tel_item_language t2
				WHERE t1.tel_id=t2.tel_id AND t1.`status`=1 AND t2.iso=:my_lang ';
		$tel_array = Yii::$app->db->createCommand($sql,$params)->queryAll();
		
			
		$response['data']['tel'] = $tel_array;
		
		if(!empty($sign))
		{
			$member = MemberService::getMemberbysign($sign);
			if(!empty($member)){
				$member_money = $member->member_money;
		
				$response['data']['member_money'] = $member_money / 100;
			}else{
				$response['error'] = array('error_code'=>1,'message'=>'Member does not exist');
			}
		}else {
			$response['error'] = array('error_code'=>1,'message'=>'sign can not be empty');
		}
		
		return $response;
	}
	
	
	/***
	 *  获取电话购买记录
	 */
	
	public function actionGettelorder()
	{
		
		$sign = isset($_POST['sign']) ? $_POST['sign'] : '';
		
		if(!empty($sign)){
			$member = MemberService::getMemberbysign($sign);
			$response = array();
			if(!empty($member)){
				$membership_id = $member->member_id;
				$membership_code = $member->member_code;
		
				$params_orderlog = ['membership_code'=>$membership_code];
				$orderlog_sql = ' SELECT a.goods_name,b.order_create_time FROM vcos_member_order_detail a,vcos_member_order b WHERE b.membership_code = :membership_code
								AND a.order_serial_num=b.order_serial_num AND b.order_type=4 LIMIT 5';
				$tel_order_log = Yii::$app->db->createCommand($orderlog_sql,$params_orderlog)->queryAll();
				
				
				$response['data']['tel_order_log'] = $tel_order_log;
			}else{
				$response['error'] = array('error_code'=>1,'message'=>'Member does not exist');
			}
		}else {
			$response['error'] = array('error_code'=>1,'message'=>'sign can not be empty');
		}
		return $response;
	}
	
	
	/***
	 *  购买电话服务
	 */
	public function actionPhoneservicebuy()
	{
		$sign = isset($_POST['sign']) ? $_POST['sign'] : '';
		$tel_name = isset($_POST['tel_name']) ? $_POST['tel_name'] :'';
		$tel_price = isset($_POST['tel_price']) ? $_POST['tel_price']*100 : '';
		$tel_id = isset($_POST['tel_id']) ? $_POST['tel_id'] : '';
		
		if(!empty($sign))
		{
			$member = MemberService::getMemberbysign($sign);
			if(!empty($member)){
				$membership_id = $member->member_id;
				$membership_code = $member->member_code;
				$member_money = $member->member_money;
				$overdraft_limit = $member->overdraft_limit;
				$curr_overdraft_amount = $member->curr_overdraft_amount;
				
				//判断会员余额是否够支付账单
				if( $tel_price <= $member_money + $overdraft_limit - $curr_overdraft_amount && $member_money >= 0)
				{
					if( $tel_price <= $member_money ){
						// 会员余额能够支付账单，直接支付
						// 更新 member money
						$member->member_money = $member_money - $tel_price;
						
					}else {
						//使用会员余额 + 透支额度支付
						$draft = $tel_price - $member_money;
						$member->member_money = 0;
						$member->curr_overdraft_amount = $curr_overdraft_amount + $draft;
					}
					$member->save();
					//改变 tel_sn_code status
					$cur_time = date('Y-m-d H:i:s',time());
					$params_tel = ['cur_time'=>$cur_time,'tel_id'=>$tel_id];
					$sql_tel_sn = ' SELECT * FROM vcos_tel_sn_code
							WHERE start_time < :cur_time AND end_time > :cur_time
							AND status=1 AND tel_id= :tel_id ';
					$tel_sn_code = Yii::$app->db->createCommand($sql_tel_sn,$params_tel)->queryOne();
						
					if($tel_sn_code){
						Yii::$app->db->createCommand()->update('vcos_tel_sn_code', ['status'=>2],['id'=>$tel_sn_code['id']])->execute();
					
						//记录  member_order 和  member_order_detail
						$tel_order_time = time();
						$tel_order_number  = OrderService::getMemberOrderNO();
						$tel_order_type=isset($_POST['order_type']) ? $_POST['order_type'] : '4';
						$order_price = $tel_price;
						$pay_type = isset($_POST['pay_type']) ? $_POST['pay_type'] : '1';
						$tel_order_state = '3';
					
						$order_check_num = md5($sign.$tel_order_type.$tel_order_time);
						$myMemberOrder = OrderService::getOrderCheckNum($order_check_num);
						if(empty($myMemberOrder)){
								
							$memberOrder = new MemberOrder();
							$memberOrder->order_serial_num = $tel_order_number;
							$memberOrder->membership_code = $member->member_code;
							$memberOrder->totale_price = $tel_price;
							$memberOrder->pay_type = $pay_type;
							$memberOrder->order_check_num = $order_check_num;
							$memberOrder->pay_time = date('Y-m-d H:i:s',$tel_order_time);
							$memberOrder->order_create_time =  date('Y-m-d H:i:s',$tel_order_time);
							$memberOrder->order_status = $tel_order_state;
							$memberOrder->order_type = $tel_order_type;
							$memberOrder->receiving_way = 0;
							$memberOrder->save();
								
							$memberOrderDetail = new MemberOrderDetail();
							$memberOrderDetail->order_serial_num = $tel_order_number;
							$memberOrderDetail->goods_id = $tel_sn_code['id'];
							$memberOrderDetail->goods_name = $tel_name;
							$memberOrderDetail->goods_price = $tel_price;
							$memberOrderDetail->buy_num = 1;
							$memberOrderDetail->sub_goods_remark = $tel_sn_code['sn_code'].','.$tel_sn_code['sn_password'].';';
							$memberOrderDetail->last_change_time =  date('Y-m-d H:i:s',$tel_order_time);
							$memberOrderDetail->save();
								
							$response['data'] = array('code'=>1,'message'=>'pay success');
						}else{
							$response['error'] = array('error_code'=>2,'message'=>'Please do not submit a duplicate');
						}
					}else {
						$response['error'] = array('error_code'=>1,'message'=>'tel sn code had finished');
					}
				}else {
					$response['error'] = array('error_code'=>1,'message'=>'Not enought money ');
				}
			}else {
				$response['error'] = array('error_code'=>1,'message'=>'Member does not exist');
			}
		}else {
			$response['error'] = array('error_code'=>1,'message'=>'sign can not be empty');
		}
		return $response;
	}
	
}