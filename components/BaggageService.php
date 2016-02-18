<?php

namespace app\components;

use Yii;

/**
 * Description of BaggageService
 *
 * @author Rock.Lei
 */
class BaggageService {
	
	public static function getAllbaggage($member_code,$trip_id,$check_type)
	{
		$sql_value = 'SELECT member_or_crew_code,baggage_barcode,baggage_status FROM vcos_boarding_baggage 
				WHERE member_or_crew_code = \''.$member_code.'\' AND trip_id='.$trip_id.' AND check_type=\''.$check_type.'\'';
		
		$baggage_array = Yii::$app->mdb->createCommand($sql_value)->queryAll();
		
		return $baggage_array;
	}
	
	public static function getBaggageInfo($baggage_barcode)
	{
		$sql_value = 'SELECT out_by,baggage_status,out_location,out_time FROM log_baggage WHERE baggage_barcode=\''.$baggage_barcode.'\'';

		$baggage_log_array = Yii::$app->mdb->createCommand($sql_value)->queryAll();
		
		return $baggage_log_array;
	}
	
	public static function saveBaggagefiling($member_code,$baggage_num,$filing_time,$status){
		
		$sql_value = 'INSERT INTO vcos_baggage_filing(member_code,baggage_num,filing_time,status) VALUES (\''.$member_code.'\',\''.$baggage_num.'\',\''.$filing_time.'\',\''.$status.'\') ';
		Yii::$app->mdb->createCommand($sql_value)->execute();	
	}
	
	public static function getBaggagefiling($member_code)
	{
		$sql_value = ' SELECT id, baggage_num ,filing_time,status  FROM vcos_baggage_filing WHERE member_code = \''.$member_code. '\' ORDER BY id DESC ';
		$baggage_array = Yii::$app->mdb->createCommand($sql_value)->queryAll();
		return $baggage_array;
	}
	
	public static function delBaggagefiling($id,$member_code)
	{
	
		$bool_del = Yii::$app->mdb->createCommand()
				->delete('vcos_baggage_filing',['id'=>$id,'member_code'=>$member_code])
				->execute();
		
		return $bool_del;
	}
}