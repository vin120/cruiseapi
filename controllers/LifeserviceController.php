<?php

namespace app\controllers;

use Yii;
use app\components\LifeserviceService;


class LifeserviceController extends MyActiveController
{
	public function actionFindallcategory()
	{
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		$sql = "SELECT a.lc_id as category_id, a.lc_img_url as img_url, a.bg_color, b.lc_name as category_name
				FROM vcos_lifeservice_category a LEFT JOIN vcos_lifeservice_category_language b 
				ON a.lc_id = b.lc_id WHERE b.iso = '".$my_lang."' AND a.lc_state = '1'";
		
		$lifeservice_category_array = Yii::$app->db->createCommand($sql)->queryAll();
		$response['data']=$lifeservice_category_array;
		 
		return $response;

	}
	
	public function actionFindlifeservicebycategoryid()
	{
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		$category_id = isset($_POST['category_id']) ? $_POST['category_id'] : '';
		$response = array();
		if(!empty($category_id)){
			$sql = 'SELECT a.ls_id as lifeservice_id, a.ls_img_url as img_url,b.ls_title as lifeservice_title,b.ls_desc lifeservice_describe FROM vcos_lifeservice a LEFT JOIN vcos_lifeservice_language b 
					ON a.ls_id =b.ls_id WHERE b.iso = \''.$my_lang.'\' AND a.ls_state =\'1\' 
					AND a.ls_category = \''.$category_id.'\' ORDER BY lifeservice_id DESC';
			$lifeservice_array = Yii::$app->db->createCommand($sql)->queryAll();
			
			$response['data'] = $lifeservice_array;
		}else {
			$response['error'] = array('error_code'=>1,'message'=>'category id can not be empty');
		}
		
		return $response;
	}
	
	public function actionFindlifeservicebyid()
	{
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		$lifeservice_id = isset($_POST['lifeservice_id']) ? $_POST['lifeservice_id'] : '';
		
		$response = array();
		if(!empty($lifeservice_id)){
			
			$lifeservice = LifeserviceService::getLifeserviceById($lifeservice_id,$my_lang);
			$img_array = LifeserviceService::getLifeserviceImageById($lifeservice_id,$my_lang);
			
			$lifeservice['img_items'] = $img_array;
			
			$response['data'] = $lifeservice;
			
		}else {
			$response['error'] = array('error_code'=>1,'message'=>'category id can not be empty');
		}
		
		return $response;
	}
	
	public function actionLifeservicebooking()
	{
		
	}
	
}