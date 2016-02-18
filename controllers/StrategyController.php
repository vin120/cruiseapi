<?php

namespace app\controllers;

use Yii;

class StrategyController extends MyActiveController
{	
	
	/**
	 * 显示攻略国家
	 */
	public function actionFindallcountry()
	{
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		
		$country_array = array();
		$sql_country = 'SELECT a.country_id,b.country_name FROM vcos_strategy_country a,vcos_strategy_country_language b 
				WHERE a.country_id=b.country_id AND a.state=1 AND b.iso=\''.$my_lang.'\'';
		$country_array = Yii::$app->db->createCommand($sql_country)->queryAll();
		
		$sql_country_in_array = array();
		foreach ($country_array as $country)
		{
			$sql_country_in_array[] = $country['country_id'];
		}
		
		$city_array = array();
		if(!empty($sql_country_in_array)){
			$in_value = join(',', $sql_country_in_array);
			$sql_city = 'SELECT a.city_id,a.country_id,b.city_name FROM vcos_strategy_city a,vcos_strategy_city_language b 
					WHERE a.city_id = b.city_id AND a.state=1 AND a.country_id IN ('.$in_value.') AND b.iso = \''.$my_lang.'\'';
			$city_array = Yii::$app->db->createCommand($sql_city)->queryAll();
		}
		
		for($i=0;$i<count($country_array);$i++){
			$country_array[$i]['city_items'] = array();//先定义city_items
			
			$temp_count = 0;
			foreach ($city_array as $key => $city){
				if($country_array[$i]['country_id'] == $city['country_id']){
					$country_array[$i]['city_items'][$temp_count]['city_id'] = $city['city_id'];
					$country_array[$i]['city_items'][$temp_count]['city_name'] = $city['city_name'];
					$temp_count++;
				}
			}
		}
		
		$response['data'] = $country_array;
		
		return $response;
	}
	
	/**
	 * 获取分类的攻略
	 */
	public function actionFindstrategybycategoryandcity() {
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		$curr_page = isset($_POST['page']) ? $_POST['page'] : 1;
		$limit_size = isset($_POST['limit_size']) ? $_POST['limit_size'] : 20;
		
		$category_id = isset($_POST['category_id']) ? $_POST['category_id'] : '';
		$city_id = isset($_POST['city_id']) ? $_POST['city_id'] : '';
		
		$response = array();
		if(!empty($category_id) && !empty($city_id)){
			
			$sql_select_value = 'SELECT a.strategy_id,b.strategy_name,(b.avg_price/100) as avg_price,b.strategy_feature,b.address,b.show_style,b.img_url,b.img_url2,b.img_url3 ';
			$sql_select_count = 'SELECT COUNT(*) ';
			$sql_from_where = 'FROM vcos_strategy a,vcos_strategy_language b 
					WHERE a.strategy_id=b.strategy_id AND a.strategy_category_id='.$category_id
					.' AND a.city_id='.$city_id.' AND b.iso=\''.$my_lang.'\'';
			
			$sql_count = $sql_select_count.$sql_from_where;
			$strategy_count = Yii::$app->db->createCommand($sql_count)->queryScalar();
			
			//计算页数
			$total_page = ceil($strategy_count/$limit_size);
			if($curr_page>$total_page || $curr_page < 1)
			{
				$curr_page =1;
			}
			$sql_limit = ' LIMIT '.($curr_page-1)*$limit_size.','.$limit_size;
			
			$sql = $sql_select_value.$sql_from_where.$sql_limit;
			
			$strategy_array = Yii::$app->db->createCommand($sql)->queryAll();
			
			$_page['curr_page'] = $curr_page;
			$_page['total_page'] = $total_page;
			$_page['limit_size'] = $limit_size;
			
			$response['data'] = $strategy_array;
			$response['_page'] = $_page;
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'category_id and city id can not be empty');
		}
		
		return $response;
		
	}
	
	/**
	 * 获取攻略详情
	 */
	public function actionFindstrategydetailbyid()
	{
		$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
		$strategy_id = isset($_POST['strategy_id']) ? $_POST['strategy_id'] : '1';
		
		$response = array();
		
		if(!empty($strategy_id)){
			$sql = 'SELECT a.strategy_id,b.strategy_name,(b.avg_price/100) as avg_price,b.strategy_feature,b.address,
						b.telphone,b.strategy_type,b.strategy_describe ,b.strategy_details  
						FROM vcos_strategy a,vcos_strategy_language b
						WHERE a.strategy_id=b.strategy_id AND a.strategy_id='.$strategy_id.' AND b.iso=\''.$my_lang.'\'';
		
			$strategy = Yii::$app->db->createCommand($sql)->queryOne();
			
			$sql_img = 'SELECT img_url FROM vcos_strategy_img WHERE strategy_id='.$strategy_id.' AND iso=\''.$my_lang.'\'';
			$strategy_img_array = Yii::$app->db->createCommand($sql_img)->queryAll();
			$strategy['img_items'] = $strategy_img_array;
			
			$response['data'] = $strategy;
			
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'strategy_id can not be empty');
		}
		
		return  $response;
	}
}