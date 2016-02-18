<?php

namespace app\controllers;


use Yii;

class TravelscheduleController extends MyActiveController
{
	
	public function actionFindalltravel()
	{

		$my_date = isset($_POST['date']) ? $_POST['date'] : time();
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		 
		$response = array();

		$select_date = Date("Y-m-d", $my_date);
		
		$sql = 'SELECT a.ts_id AS travel_id, b.ts_title AS travel_title, a.ts_img_url AS img_url, a.ts_time AS travel_date,  
				a.ts_start_time AS start_time, a.ts_end_time AS end_time,b.ts_address AS travel_address, b.ts_desc AS travel_desc 
				FROM vcos_travel_schedule a LEFT JOIN vcos_travel_schedule_language b ON a.ts_id = b.ts_id WHERE b.iso = \''
				.$my_lang.'\' AND a.ts_state = 1 AND a.ts_time = \''.$select_date.'\' ORDER BY travel_id DESC LIMIT 20';
		
		$travel_schedule_array = Yii::$app->db->createCommand($sql)->queryAll();
		
		$response['data'] = $travel_schedule_array;
		
		return  $response;
	}
	
	public function actionFindtravelbyid()
	{

		$travel_id = isset($_POST['travel_id']) ? $_POST['travel_id'] : '';
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
			
		$response = array();
		
		if(!empty($travel_id)){
			$sql = 'SELECT a.ts_id AS travel_id, b.ts_title AS travel_title, a.ts_img_url AS img_url, 
					a.ts_time AS travel_date,  a.ts_start_time AS start_time, a.ts_end_time AS end_time,
					b.ts_address AS travel_address, b.ts_desc AS travel_desc,b.ts_content AS travel_content 
				FROM vcos_travel_schedule a LEFT JOIN vcos_travel_schedule_language b ON a.ts_id = b.ts_id WHERE b.iso = \''
					.$my_lang.'\' AND a.ts_state = 1 AND a.ts_id = '.$travel_id.' LIMIT 1';

			$travel_schedule_array = Yii::$app->db->createCommand($sql)->queryOne();
			
			$response['data'] = $travel_schedule_array;
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'travel_id can not be empty');
		}
		
		
		return  $response;
	}
} 