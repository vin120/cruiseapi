<?php

namespace app\components;
use Yii;

/**
 * Description of RestaurantService
 *
 * @author Rock.Lei
 */
class RestaurantService {
	
	public static function getRestaurantById($restaurant_id,$my_lang='zh_cn')
	{
		$restaurant = '';
		if(!empty($restaurant_id)){
	    	$sql = "SELECT a.restaurant_id,b.restaurant_name,a.restaurant_tel,(a.shipping_fee/100) as shipping_fee,(a.box_price/100) as box_price,b.restaurant_feature,b.restaurant_address,
		    		a.can_book,a.can_delivery,b.restaurant_describe,b.restaurant_opening_time FROM 
		    		vcos_restaurant a LEFT JOIN vcos_restaurant_language b ON a.restaurant_id = b.restaurant_id 
		    		WHERE b.iso = '".$my_lang."' AND a.restaurant_id = '{$restaurant_id}'";
	    	$restaurant = Yii::$app->db->createCommand($sql)->queryOne();
		}
		
	    return $restaurant;
	}
	
	public static function getRestaurantImageById($restaurant_id,$my_lang='zh_cn')
	{
		$restaurant_img_array = array();
		if(!empty($restaurant_id)){
			$sql_img = 'SELECT img_url FROM vcos_restaurant_img WHERE restaurant_id='.$restaurant_id.' AND iso=\''.$my_lang.'\' AND state=1';
			$restaurant_img_array = Yii::$app->db->createCommand($sql_img)->queryAll();
		}
		return $restaurant_img_array;
	}
	
	public static function getFoodById($food_id_array,$my_lang='zh_cn')
	{
		$food_id_in_value = join(',', $food_id_array);
		$food_array = array();
		if(!empty($food_id_in_value)){
			$sql = 'SELECT a.food_id,a.food_img_url,b.food_title,a.food_price,a.food_category_id,a.restaurant_id
				FROM `vcos_food` a,vcos_food_language b
				WHERE a.food_id = b.food_id AND a.food_state=1 AND b.iso=\''.$my_lang.'\' AND a.food_id IN ('.$food_id_in_value.')';
			$food_array = Yii::$app->db->createCommand($sql)->queryAll();
		}
		
		return $food_array;
	}
}