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
use yii\web\HeaderCollection;
use yii\base\Object;
use app\components\RestaurantService;

class RestaurantController extends MyActiveController
{
	/**
	 * get all restaurant
	 * @return multitype:
	 */
    public function actionFindall()
    {
     	$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
    	
     	$sql = 'SELECT a.restaurant_id, a.bg_color, a.restaurant_img_url, b.restaurant_name,b.restaurant_feature 
     			FROM vcos_restaurant a, vcos_restaurant_language b WHERE a.restaurant_id = b.restaurant_id 
     			AND b.iso = \''.$my_lang.'\' AND a.restaurant_state = \'1\' ORDER BY a.restaurant_sequence ASC';
     	
     	$restaurantArray = Yii::$app->db->createCommand($sql)->queryAll();
     	
    	$response['data']=$restaurantArray;
    	
    	return $response;
    }
    
    /**
     * 显示餐厅详情
     * @return multitype:multitype:number string  multitype:
     */
    public function actionFindrestaruantbyid()
    {
    	$restaurant_id = isset($_POST['restaurant_id']) ? $_POST['restaurant_id'] : '';
    	$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
    	
    	$response = array();
    	
    	if(!empty($restaurant_id))
    	{
    		$restaurant = RestaurantService::getRestaurantById($restaurant_id,$my_lang);
    		$restaurant_img_array = RestaurantService::getRestaurantImageById($restaurant_id,$my_lang);
	    	
	    	$restaurant['img_items'] = $restaurant_img_array;
	    	
	    	$response['data'] = $restaurant;
    	
    	}else{
    		$response['error'] = array('error_code'=>1,'message'=>'restaurant_id can not be empty');
    	}
    	
    	return  $response;
    }
    
    
    /**
     * 显示餐厅所有分类
     * @return multitype:multitype:number string  multitype:
     */
    public function actionFindallfoodcategory()
    {
    	$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
    	$restaurant_id = isset($_POST['restaurant_id']) ? $_POST['restaurant_id'] : '';

    	$response = array();
    	
    	if(!empty($restaurant_id)){
	    	$sql = 'SELECT fc.food_category_id as food_category_id,fcl.food_category_name as category_name,fc.list_order FROM vcos_food_category fc,vcos_food_category_language fcl 
	    			WHERE fc.food_category_id=fcl.food_category_id AND fc.restaurant_id =\''.$restaurant_id.'\' AND fc.food_category_state = 1 AND fcl.iso = \''.$my_lang.'\'';
	    	
	    	$food_category_array = Yii::$app->db->createCommand($sql)->queryAll();

	    	$response['data'] = $food_category_array;
	    	
    	}else{
    		$response['error'] = array('error_code'=>1,'message'=>'restaurant_id can not be empty');
    	}
    	
    	return $response;
    	
    }
    
    /**
     * 获取所有分类下的食品
     */
    public function actionFindfoodbycategoryid()
    {
    	$my_lang = isset($_POST['mylang']) ? $_POST['mylang'] : 'zh_cn';
    	$restaurant_id = isset($_POST['restaurant_id']) ? $_POST['restaurant_id'] : '';
    	$category_id =  isset($_POST['food_category_id']) ? $_POST['food_category_id'] : '';

   		$response =  array();
   		
   		if(!empty($restaurant_id)){
   			
   			$sql_value = '';
   			if(!empty($category_id)){
   				$sql_value =  ' AND food.food_category_id = '.$category_id;
   			}
   			$sql = 'SELECT food.food_id,(food.food_price/100) as food_price,food.food_img_url,food.max_buy,food.sales_volume,
   					food_lang.food_title,food.food_category_id FROM vcos_food food, vcos_food_language food_lang ,vcos_food_category fc'
						.' WHERE food.food_id=food_lang.food_id AND food.food_category_id=fc.food_category_id '.$sql_value
						.' AND food.restaurant_id = '.$restaurant_id.' AND fc.restaurant_id = '.$restaurant_id
   						.' AND fc.food_category_state =1 AND food.food_state =1 AND food_lang.iso = \''.$my_lang.'\'';
   					
   			$food_array = Yii::$app->db->createCommand($sql)->queryAll();

   			$response['data'] = $food_array;
   		
   		}else{
   			$response['error'] = array('error_code'=>1,'message'=>'restaurant id can not be empty');
   			
   		}
   		
   		return $response;
    }
    
    public function actionCreateorder()
    {
//     	{"sign":"123456","restaurant_id":"123456","delivery_time":"20140501123412","address":"地址","totle_price":"2000","remark":"备注","food_items":[{"food_id":"51","food_price":"26000","max_buy":"2","food_state":"1","food_category_id":"77","restaurant_id":"73","food_title":"秘制生鲜"},{"food_id":"61","food_price":"40000","max_buy":"2","food_state":"1","food_category_id":"77","restaurant_id":"73","food_title":"生鱼片"}]}
//     	$disc_array = json_decode(iconv('GB2312', 'UTF-8', $disc_json),true);
		$data = $_POST['data'];
		$data_array = json_decode($data,true);
		if(!empty($data_array)){
			$sign=$data_array['sign'];
		}
		$sign=$data_array['sign'];
    	return $data_array;
    }
}