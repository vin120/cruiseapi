<?php

namespace app\components;
use Yii;
/**
 * Description of BaggageService
 *
 * @author Rock.Lei
 */
class CruiseLineService {
	/**
	 * 根据当前时间判断所在航线
	 * @return type
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
	
	
}