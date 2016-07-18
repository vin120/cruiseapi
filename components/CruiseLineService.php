<?php

namespace app\components;
use Yii;
use app\modules\wifiservice\components\MyCurl;
/**
 * Description of BaggageService
 *
 * @author Rock.Lei
 */
class CruiseLineService {
	/**
	 * 根据当前时间判断所在航线
	 * @return type
	 * 
	 */
	public static function getCruiseLineByCurrTime()
	{
		$curr_time = time();
		$sql_value = 'SELECT trip_id,trip_name,trip_no,trip_start_time,trip_end_time 
				FROM vcos_cruise_trip WHERE trip_state =0 AND trip_start_time< '.$curr_time.' AND trip_end_time > '.$curr_time.' LIMIT 1';
		$cruise_trip = Yii::$app->mdb->createCommand($sql_value)->queryOne();
		
		return $cruise_trip;
	}
	
	public static function getCruiseAddress($member_code,$cruise_trip){
		$sql_value = 'SELECT t2.cabin_name_num,t2.floor FROM vcos_boarding_ticket t1,vcos_cruise_cabin t2
		WHERE t1.cabin_id=t2.cabin_id AND t1.trip_id=t2.trip_id AND t1.trip_id=\''.$cruise_trip.'\' AND t1.member_or_crew_code=\''.$member_code.'\' LIMIT 1';
		$cabin = Yii::$app->mdb->createCommand($sql_value)->queryOne();
		
		return $cabin;
	}
	
	//初始化用户流量
	public static function getRunInitStatus($type,$passport)
	{
		$sql = '';
		if($type == 1){
			//会员
			$cruise_trip = CruiseLineService::getCruiseLineByCurrTime();
			$trip_id = $cruise_trip['trip_id'];
	
			$sql = "SELECT * FROM vcos_wifi_login_log WHERE login_type=$type AND trip_id='$trip_id' AND passport='$passport'";
			$sql_result = Yii::$app->db->createCommand($sql)->queryOne();
	
			if (empty($sql_result)) {
				$my_date = date('Y-m-d');
				$my_time = date('H:i:s');
				
				if(!empty($trip_id)){
					$sql = "INSERT INTO `vcos_wifi_login_log` (`login_date`,`login_time`,`login_type`,`trip_id`,`passport`)
					VALUES ('$my_date','$my_time','$type','$trip_id','$passport')";
				}else {
					$sql = "INSERT INTO `vcos_wifi_login_log` (`login_date`,`login_time`,`login_type`,`passport`)
					VALUES ('$my_date','$my_time','$type','$passport')";
				}
				
				Yii::$app->db->createCommand($sql)->execute();
				MyCurl::InitAccount($passport);
				
// 				if($passport == 'E74166818'){
// 					\app\modules\wifiservice\components\MyCurl::InitAccount($passport);
// 				}
			}
	
		}else{
			//船员
			$temp_date = date('Y-m-');
			$like_value = $temp_date.'%';
			$sql = "SELECT * FROM vcos_wifi_login_log WHERE login_type=$type AND login_date LIKE '$like_value' AND passport='$passport'";
			$sql_result = Yii::$app->db->createCommand($sql)->queryOne();
			if (empty($sql_result)) {
				$my_date = date('Y-m-d');
				$my_time = date('H:i:s');
	
				$sql = "INSERT INTO `vcos_wifi_login_log` (`login_date`,`login_time`,`login_type`,`passport`)
				VALUES ('$my_date','$my_time','$type','$passport')";
	
				Yii::$app->db->createCommand($sql)->execute();
				MyCurl::InitAccount($passport);
	
			}
		}
	
	
	}
	
	
}