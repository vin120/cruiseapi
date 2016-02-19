<?php

namespace app\components;

use Yii;

/**
 * Description of MallService
 *
 * @author Rock.Lei
 */
class MallService {
	
	public static function  getMainPage()
	{
		$response = array(
			'1' => 'Open',
			'2' => 'Open',
			'3' => 'Open',
			'4' => 'Open',
			'5' => 'Open',
			'6' => 'Open',
			'7' => 'Open',
			'8' => 'Open',
			'9' => 'Open',
			'10' => 'Open',
			'11' => 'Open',
			'12' => 'Open',
			'13' => 'Open',
			'14' => 'Open',
			'15' => 'Close',
		);
		return $response;
	}
	
	public static function getShowNavigation($cruise_id,$style_type)
	{
		$sql_value = 'SELECT navigation_id,navigation_name,activity_id,sort_order,is_main,is_category FROM vcos_navigation  
				WHERE cruise_id='.$cruise_id.' AND navigation_style_type='.$style_type.' AND status=1 ORDER BY sort_order,navigation_id';
		
		$navigation_array = Yii::$app->pdb->createCommand($sql_value)->queryAll();
		
		return $navigation_array;
	}
	
	public static function getActivityById($activity_id){
		
		$sql_value = 'SELECT * FROM vcos_activity WHERE activity_id = '.$activity_id.' AND `status` =1';
		$activity = Yii::$app->pdb->createCommand($sql_value)->queryOne();
		
		return $activity;
	}
	
	public static function getAllProductByActivityId($activity_id)
	{
		$curr_time = date('Y-m-d H:i:s');
		
		$sql_value = 'SELECT * FROM vcos_activity_product
				WHERE activity_id = '.$activity_id.' AND start_show_time < \''.$curr_time.'\' AND end_show_time >\''.$curr_time.'\' AND is_overdue !=2   ORDER BY product_type,sort_order,id';
		$all_product_array = Yii::$app->pdb->createCommand($sql_value)->queryAll();

		return $all_product_array;
		
	}
	
	public static function getActivityByActivityId($activity_id_array)
	{
		$in_value = join(',', $activity_id_array);
		$curr_time = date('Y-m-d H:i:s');
		$activity_array = array();
		if(!empty($in_value)){
			$sql_value = 'SELECT activity_id,activity_name,activity_desc,activity_img,start_time,end_time
				FROM vcos_activity WHERE activity_id IN ('.$in_value.') AND `status` =1  AND start_time < \''.$curr_time.'\' AND end_time > \''.$curr_time.'\'' ;
			
			$activity_array = Yii::$app->pdb->createCommand($sql_value)->queryAll();
		}
		
		$temp_sort_activity_array = array();
		$sort_activity_array = array();
		foreach ($activity_array as $activity)
		{
			$temp_activity_id = $activity['activity_id'];
			$temp_sort_activity_array[$temp_activity_id] = $activity;
				
		}
		foreach ($activity_id_array as $activity_id)
		{
			if(isset($temp_sort_activity_array[$activity_id]))
			{
				$sort_activity_array[] = $temp_sort_activity_array[$activity_id];
			}
		}
		
		return $sort_activity_array;

	}
	
	public static function getProductByActivityId($product_id_array){
		$in_value = join(',', $product_id_array);
		$curr_time = date('Y-m-d H:i:s');
		$product_array = array();
		if(!empty($in_value)){
			$sql_value = 'SELECT product_id,product_name,product_desc,product_img,(standard_price/100) as standard_price,
					(sale_price/100) as sale_price,sale_start_time,sale_end_time,inventory_num,shop_id 
					FROM vcos_product WHERE product_id IN ('.$in_value.') AND `status` =1 AND sale_start_time < \''.$curr_time.'\' AND sale_end_time > \''.$curr_time.'\'';

			$product_array = Yii::$app->pdb->createCommand($sql_value)->queryAll();
		}
		
		$temp_sort_product_array = array();
		$sort_product_array = array();
		foreach ($product_array as $product)
		{
			$temp_product_id = $product['product_id'];
			$temp_sort_product_array[$temp_product_id] = $product;
			
		}
		foreach ($product_id_array as $product_id)
		{
			if(isset($temp_sort_product_array[$product_id]))
			{
				$sort_product_array[] = $temp_sort_product_array[$product_id];
			}
		}
		
		return $sort_product_array;
	}
	
	
	public static function getShopByActivityId($shop_id_array){
		$in_value = join(',', $shop_id_array);
		$shop_array = array();
		if(!empty($in_value)){
			$sql_value = 'SELECT shop_id,shop_title,shop_logo,shop_img_url,shop_address,shop_desc 
					FROM vcos_shop WHERE shop_id IN ('.$in_value.') AND `shop_status` =1';
				
			$shop_array = Yii::$app->pdb->createCommand($sql_value)->queryAll();
		}
		
		
		$temp_sort_shop_array = array();
		$sort_shop_array = array();
		foreach ($shop_array as $shop)
		{
			$temp_shop_id = $shop['shop_id'];
			$temp_sort_shop_array[$temp_shop_id] = $shop;
				
		}
		foreach ($shop_id_array as $shop_id)
		{
			if(isset($temp_sort_shop_array[$shop_id]))
			{
				$sort_shop_array[] = $temp_sort_shop_array[$shop_id];
			}
		}

		return $sort_shop_array;
	}
	
	public static function getCategoryByActivityId($activity_id)
	{
		$sql_value = 'SELECT * FROM vcos_activity_category WHERE activity_id ='.$activity_id.' AND `status`=1 ORDER BY sort_order';
		
		$activity_category_array = Yii::$app->pdb->createCommand($sql_value)->queryAll();
		
		return $activity_category_array;
	}
	
	
	public static function getActivityAllInfo($activity_id)
	{
		$response = array();
		
		$activity = MallService::getActivityById($activity_id);
		
		if(1 == $activity['is_show_category']){
			$activity_category_array = MallService::getCategoryByActivityId($activity_id);
			$response['data']['category_items'] = $activity_category_array;
			
		}
		
		if(1 == $activity['is_show_head']){
			$response['data']['activity'] = $activity;
				
		}
		
		$all_product_array = MallService::getAllProductByActivityId($activity_id);
		$activity_id_array = array();
		$product_id_array = array();
		$shop_id_array = array();
			
		foreach ($all_product_array as $product)
		{
			switch ($product['product_type']){
				case 3:
					$shop_id_array[] = $product['product_id'];
					break;
				case 4:
					$activity_id_array[] = $product['product_id'];
					break;
				case 6:
					$product_id_array[] = $product['product_id'];
					break;
			}
		}
			
		$activity_array = MallService::getActivityByActivityId($activity_id_array);
		$product_array = MallService::getProductByActivityId($product_id_array);
		$shop_array = MallService::getShopByActivityId($shop_id_array);
		
		
		
		$response['data']['activity_items'] = $activity_array;
		$response['data']['product_items'] = $product_array;
		$response['data']['shop_items'] = $shop_array;
		

		return $response;
	}
	
	/**
	 * 根据产品id获得产品基本信息。
	 * @param unknown $product_id
	 */
	public static function getProductBasicInfoById($product_id)
	{
		$curr_time = date('Y-m-d H:i:s');
		$limit_time = ' AND a.sale_start_time <= \''.$curr_time.'\' AND a.sale_end_time>\'' .$curr_time.'\'';
		
		$sql_value = 'SELECT a.product_id,a.product_name,a.product_desc,(a.sale_price/100) as sale_price,
							(a.standard_price/100) as standard_price,
							a.origin,a.inventory_num,a.comment_num,a.shop_id,b.shop_title,b.shop_logo,
							a.sale_start_time,a.sale_end_time
							FROM vcos_product a,vcos_shop b WHERE a.shop_id = b.shop_id AND a.product_id = '.$product_id.$limit_time;
		
		$product = Yii::$app->pdb->createCommand($sql_value)->queryOne();
		
		return $product;
		
	}
	
	/**
	 * 根据产品id数组获得产品的价格等基本信息
	 */
	public static function getProductByIdArray($product_id_array)
	{
		$product_array = array();
		if(!empty($product_id_array))
		{
			$curr_time = date('Y-m-d H:i:s');
			$limit_time = ' AND a.sale_start_time <= \''.$curr_time.'\' AND a.sale_end_time>\'' .$curr_time.'\'';
			
			$product_id_in_value = join(',', $product_id_array);
			$sql_value = 'SELECT a.product_id,a.product_name,(a.sale_price/100) as sale_price,
							(a.standard_price/100) as standard_price,a.inventory_num,shop_id,a.product_img 
							FROM vcos_product a WHERE a.product_id IN ('. $product_id_in_value .')'.$limit_time;
			
			$product_array = Yii::$app->pdb->createCommand($sql_value)->queryAll();
		}
		return $product_array;
	}
	
	/**
	 * 产品图片列表
	 * @param unknown $product_id
	 */
	public static function getProductImg($product_id){
		$curr_time = date('Y-m-d H:i:s');
		$limit_time = ' AND b.sale_start_time <= \''.$curr_time.'\' AND b.sale_end_time>\'' .$curr_time.'\'';
		
		$sql_value = 'SELECT a.product_id,a.img_url,a.sort_order FROM vcos_product_img a,vcos_product b
				WHERE a.product_id ='.$product_id.' AND a.product_id=b.product_id '.$limit_time.'ORDER BY sort_order';
	
		$graphic_array = Yii::$app->pdb->createCommand($sql_value)->queryAll();
	
		return $graphic_array;
	}
	
	/**
	 * 产品图文描述
	 * @param unknown $product_id
	 */
	public static function getProductGraphic($product_id){
		$sql_value = 'SELECT product_id,img_url,graphic_desc,sort_order FROM vcos_product_graphic 
				WHERE product_id ='.$product_id.' ORDER BY sort_order';
		
		$graphic_array = Yii::$app->pdb->createCommand($sql_value)->queryAll();
		
		return $graphic_array;
	}
		
	/**
	 * 
	 * @param number $product_id
	 * @param number $comment_type  1全部，2好评，3中评，4差评，5追评，6有图
	 * @param number $sql_type 0统计(default)，1查询数组
	 * @param number $page default 1
	 * @param number $size default 20
	 */
	public static function getProductComment($product_id,$comment_type,$sql_type=0,$page=1,$size=20) {
		
		$pre_sql = 'SELECT COUNT(*) as all_count FROM vcos_product_comment WHERE product_id = '.$product_id.' AND comment_type = 1 AND `status` =1';
		
		if(1 == $sql_type){
			$pre_sql = 'SELECT comment_id,comment_type,product_id,comment_content,crater_time,member_code,order_serial_num,
							member_name,score,url_img1,url_img2,url_img3,url_img4,reply_content,reply_create_time,is_add_comment,is_upload_img 
						FROM vcos_product_comment WHERE product_id= '.$product_id.' AND comment_type=1 AND `status`=1';
		}

		switch ($comment_type)
		{
			case 2:
				$pre_sql .= ' AND score >=4';
				break;
			case 3:
				$pre_sql .= ' AND score =3';
				break;
			case 4:
				$pre_sql .= ' AND score <=2';
				break;
			case 5:
				$pre_sql .= ' AND is_add_comment =1';
				break;
			case 6:
				$pre_sql .= ' AND is_upload_img =1';
				break;
			default:
				break;
		}
		if(1 == $sql_type)
		{
			$sql = $pre_sql.'  ORDER BY comment_id DESC  LIMIT '.(($page-1)*20).','.$size;
			$comment_array = Yii::$app->pdb->createCommand($sql)->queryAll();
			
			foreach ($comment_array as $k => $comment){
				$member_code = $comment['member_code'];
				$sql_passport = " SELECT passport_number FROM vcos_member WHERE member_code='{$member_code}'";
				$passport = Yii::$app->db->createCommand($sql_passport)->queryOne()['passport_number'];
				$sql_icon = "SELECT icon FROM vcos_im_member WHERE member_id='{$passport}'";
				$icon = Yii::$app->db->createCommand($sql_icon)->queryOne()['icon'];
				if($icon === null){
					$icon = '';
				}
				$comment_array[$k]['icon'] = $icon;
			}
			
			$add_member_code_array = array();
			foreach ($comment_array as $comment)
			{
				//判断是否有追评
				if (1 == $comment['is_add_comment'])
				{
					$add_member_code_array[] = $comment['member_code'];
				}
			}
			//如果有追评，查询并且添加到主评中
			if(!empty($add_member_code_array))
			{
				$add_comment_array = MallService::getAddComment($product_id, $add_member_code_array);
				
				$temp_count = count($comment_array);
				for($i=0;$i<$temp_count;$i++)
				{
					$temp_member_code = $comment_array[$i]['member_code'];
					$temp_order_serial_num = $comment_array[$i]['order_serial_num'];
					foreach ($add_comment_array as $add_comment){
						if($temp_member_code == $add_comment['member_code'] && $temp_order_serial_num == $add_comment['order_serial_num'] ){
							$comment_array[$i]['add_comment_items'][] = $add_comment;
						}
					}
				}
			}
			
			return $comment_array;
		}else{
			$count = Yii::$app->pdb->createCommand($pre_sql)->queryScalar();
			
			return $count;
		}
		
	}

	
	
	/**
	 * 获得追评
	 * @param unknown $product_id
	 * @param unknown $add_member_code_array
	 */
	public static function getAddComment($product_id,$add_member_code_array){
		$in_value = '\''.join('\',\'', $add_member_code_array).'\'';
		
		$sql = 'SELECT comment_id,comment_type,product_id,comment_content,crater_time,member_code,order_serial_num,
							member_name,score,url_img1,url_img2,url_img3,url_img4,reply_content,reply_create_time,is_add_comment,is_upload_img
						FROM vcos_product_comment WHERE product_id= '.$product_id.' AND member_code IN ('.$in_value.') AND comment_type=2 AND `status`=1 ORDER BY crater_time';
		
		$add_comment_array = Yii::$app->pdb->createCommand($sql)->queryAll();
		
		
		return $add_comment_array;
	}
	
	public static function getNavigationGroup($navigation_id)
	{
		$sql_value = 'SELECT * FROM vcos_navigation_group WHERE navigation_id='.$navigation_id.' AND `status`=1 ORDER BY sort_order';
		
		$nav_group_array = Yii::$app->pdb->createCommand($sql_value)->queryAll();
		
		return $nav_group_array;
	}
	
	
	public static function getNavigationBrand($nav_group_id_array)
	{
		$in_value = join(',', $nav_group_id_array);
		
		$sql_value = 'SELECT t1.brand_id, t2.brand_cn_name, t2.brand_logo, t1.sort_order AS brand_order 
				FROM vcos_navigation_group_brand t1, vcos_brand t2
				WHERE t1.navigation_group_id IN ('.$in_value.') AND t1.brand_id = t2.brand_id 
				AND t1.status =1 AND t2.brand_status=1 ORDER BY t1.sort_order';
		
		$nav_brand_array = Yii::$app->pdb->createCommand($sql_value)->queryAll();
		
		return $nav_brand_array;
	}
	
	public static function getNavigationCategory($nav_group_id_array)
	{
		$in_value = join(',', $nav_group_id_array);
		
		$sql_value = 'SELECT * FROM vcos_navigation_group_category 
				WHERE navigation_group_id IN ('.$in_value.') AND status = 1 ORDER BY sort_order';
		
		$nav_category_array = Yii::$app->pdb->createCommand($sql_value)->queryAll();
		
		return $nav_category_array;
	}
	
	
	
	public static function getNavigationCategoryByGroupid($navigation_group_id)
	{
		$sql_value = 'SELECT * FROM vcos_navigation_group_category
				WHERE navigation_group_id='.$navigation_group_id.' AND status = 1 ORDER BY sort_order,navigation_group_cid';
		
		$nav_category_array = Yii::$app->pdb->createCommand($sql_value)->queryAll();
		return $nav_category_array;
	}
	
	
	public static function getNavigationCategoryAndBrand($navigation_id)
	{
		$nav_group_array = MallService::getNavigationGroup($navigation_id);
		
		$nav_group_id_array = array();
		foreach ($nav_group_array as $nav_group)
		{
			$nav_group_id_array[] = $nav_group['navigation_group_id'];
		}
		
		$response = array();
		if(!empty($nav_group_id_array)){
			$nav_brand_array = MallService::GetBrand();
			
			$nav_category_array = MallService::getNavigationCategory($nav_group_id_array);
			
			$temp_count = count($nav_group_array);
			for($i=0;$i<$temp_count;$i++)
			{
				$temp_navigation_group_id = $nav_group_array[$i]['navigation_group_id'];
				foreach ($nav_category_array as $nav_category)
				{
					if($temp_navigation_group_id == $nav_category['navigation_group_id']){
						$nav_group_array[$i]['sub_category_items'][]=$nav_category;
					}
				}
			}
			
			$response['data']['category_items'] = $nav_group_array;
			$response['data']['brand_items'] = $nav_brand_array;
			
		}
		
		return $response;
	}
	
	
	
	public static function GetProduct($cruise_id,$brand_id,$shop_id,$category_code,$sql_type,$keyword,$order_by='',$page=1,$limit_size=20){
		//查询该邮轮品牌下的商品数，
		//统计商品的分类
		
		$from_sql = 'FROM vcos_product p1 WHERE p1.status=1';
		
		if(!empty($cruise_id)){
			$from_sql .= ' AND p1.cruise_id ='.$cruise_id;
		}
		if(!empty($brand_id)){
			$from_sql .= ' AND p1.brand_id ='.$brand_id;
		}
		if(!empty($shop_id)){
			$from_sql .= ' AND p1.shop_id ='.$shop_id;
		}
		if(!empty($keyword)){
			$from_sql .= ' AND p1.product_name LIKE \'%'.$keyword.'%\'';
		}
		if(!empty($category_code)){
			$tmp_category_code = explode(',', $category_code);
			if(strlen($tmp_category_code[0]) < 5)
			{
				//二级分类
				$from_sql .= ' AND p1.category_code LIKE \''.$category_code.'%\'';
			}
			else{
				//三级分类
				$from_sql .= ' AND p1.category_code IN ('.$category_code.')';
			}
		}
		
		$select_sql = 'SELECT COUNT(*)';
		
		$curr_time = date('Y-m-d H:i:s');
		$limit_time = ' AND p1.sale_start_time <= \''.$curr_time.'\' AND p1.sale_end_time>\'' .$curr_time.'\'';
		
		if(1 == $sql_type)
		{
			$select_sql = 'SELECT p1.product_id,p1.product_name,p1.product_desc,p1.product_img,(p1.standard_price/100) as standard_price,
					(p1.sale_price/100) as sale_price,p1.sale_start_time,p1.sale_end_time ';
			
			$sql = $select_sql.$from_sql.$limit_time.$order_by.' LIMIT '.(($page-1)*$limit_size).','.$limit_size;
			
			$product_array = Yii::$app->pdb->createCommand($sql)->queryAll();
			
			return $product_array;
			
		}else{
			$sql = $select_sql.$from_sql.$limit_time.$order_by;
			$count = Yii::$app->pdb->createCommand($sql)->queryScalar();

			return $count;
		}
	}
	

	public static function GetProductByWhere($cruise_id,$mapping_id,$category_type,$shop_id,$where_brand_id,$where_category_code,$order_type,$order_value,$keyword,$curr_page,$limit_size){
		$total_page = 0;
		$count = 0;
		$response = array();
		
		if(!empty($category_type)){
			$order_by = '';
			//1评论数，2销量，3价格
			switch($order_type){
				case 1:
					$order_by =' ORDER BY p1.comment_num '.$order_value;
					break;
				case 2:
					$order_by =' ORDER BY p1.sale_num '.$order_value;
					break;
				case 3:
					$order_by =' ORDER BY p1.sale_price '.$order_value;
					break;
			}
				
			switch ($category_type)
			{
				case 1:
					$category_code = $mapping_id;
					$brand_id =$where_brand_id;
						
					$brand_array = MallService::GetBrandProductCategory($cruise_id, $category_code);
					$response['data']['brand_items']=$brand_array;
					break;
				case 2:
					$brand_id = $mapping_id;
					$category_code = $where_category_code;
						
					$brand_array = MallService::GetCategoryByProductBrand($cruise_id, $brand_id);
					$response['data']['category_items']=$brand_array;
					break;
				case 3:
					$category_code = $where_category_code;
					$brand_id = '';
					break;
			}
				
			$count = MallService::GetProduct($cruise_id,$brand_id,$shop_id, $category_code, 0,$keyword);
			$total_page = ceil($count/$limit_size);
		
			if($curr_page>$total_page || $curr_page<1){
				$curr_page = 1;
			}
		
			$product_array = MallService::GetProduct($cruise_id, $brand_id, $shop_id,$category_code, 1,$keyword,$order_by,$curr_page,$limit_size);
			$response['data']['product_items']=$product_array;
				
		}
		$_page['count'] = $count;
		$_page['curr_page'] = $curr_page;
		$_page['total_page'] = $total_page;
		$_page['limit_size'] = $limit_size;
			
		$response['_page'] = $_page;
		
		return $response;
	}
	
	public static function  GetAllProductByWhere($cruise_id,$mapping_id,$category_type,$shop_id,$where_brand_id,$where_category_code,$order_type,$order_value,$curr_page,$limit_size)
	{
		$total_page = 0;
		$count = 0;
		$response = array();
		
		if(!empty($category_type)){
			$order_by = '';
			//1评论数，2销量，3价格
			switch($order_type){
				case 1:
					$order_by =' ORDER BY comment_num '.$order_value;
					break;
				case 2:
					$order_by =' ORDER BY sale_num '.$order_value;
					break;
				case 3:
					$order_by =' ORDER BY sale_price '.$order_value;
					break;
			}
			
			$category_code = $mapping_id;
			$brand_id =$where_brand_id;
				
			$brand_array = MallService::GetAllBrandProductCategory($cruise_id, $category_code);
			$response['data']['brand_items']=$brand_array;
		
			
			$count = MallService::GetAllProduct($cruise_id,$brand_id,$shop_id, $category_code, 0);
			$total_page = ceil($count/$limit_size);
			
			if($curr_page>$total_page || $curr_page<1){
				$curr_page = 1;
			}
			
			$product_array = MallService::GetAllProduct($cruise_id, $brand_id, $shop_id,$category_code, 1,$order_by,$curr_page,$limit_size);
			$response['data']['product_items']=$product_array;
			
			}
			$_page['count'] = $count;
			$_page['curr_page'] = $curr_page;
			$_page['total_page'] = $total_page;
			$_page['limit_size'] = $limit_size;
				
			$response['_page'] = $_page;
			
			return $response;
	}
	
	
	public static function GetAllProduct($cruise_id,$brand_id,$shop_id,$category_code,$sql_type,$order_by= '',$page=1,$limit_size=20)
	{
		$tmp_array = explode(',', $category_code);
		$tmp_two = array();
		$tmp_three = array();
		foreach ($tmp_array as $row){
			if(strlen($row) < 5){
				 //二级
				$tmp_two[] = $row;
			}else {
				//三级
				$tmp_three[] = $row;
			}
		}
		$three = implode(',', $tmp_three);

		
		
		$from_sql = 'FROM vcos_product p1 WHERE p1.status=1';
		if(!empty($cruise_id)){
			$from_sql .= ' AND p1.cruise_id ='.$cruise_id;
		}
		if(!empty($brand_id)){
			$from_sql .= ' AND p1.brand_id ='.$brand_id;
		}
		if(!empty($shop_id)){
			$from_sql .= ' AND p1.shop_id ='.$shop_id;
		}
		
	
		$curr_time = date('Y-m-d H:i:s');
		$limit_time = ' AND p1.sale_start_time <= \''.$curr_time.'\' AND p1.sale_end_time>\'' .$curr_time.'\'';
		
		$two_from_sql='';
		$three_from_sql = '';
		
		if(!empty($tmp_two)){
			foreach ($tmp_two as $row )
			{
				$two_from_sql .= ' AND p1.category_code LIKE \''.$row.'%\'';
			}
		}else {
			$two_from_sql .= ' AND p1.category_code LIKE \'    \'';
		}
		
		if(!empty($three)){
			$three_from_sql .= ' AND p1.category_code IN ('.$three.')';
		}else {
			$three_from_sql .= ' AND p1.category_code IN (0)';
		}


		$select_sql = 'SELECT p1.product_id,p1.product_name,p1.product_desc,p1.product_img,p1.comment_num as comment_num,p1.sale_num as sale_num,(p1.sale_price/100) as sale_price,(p1.standard_price/100) as standard_price,
				(p1.sale_price/100) as sale_price1,p1.sale_start_time,p1.sale_end_time ';
		
		
		if(1 == $sql_type){
			$sql = $select_sql.$from_sql.$two_from_sql.$limit_time.' union '. $select_sql.$from_sql.$three_from_sql.$limit_time.$order_by.' LIMIT '.(($page-1)*$limit_size).','.$limit_size;
			$product_array = Yii::$app->pdb->createCommand($sql)->queryAll();
			return $product_array;
		}else{
			
			$sql = $select_sql.$from_sql.$two_from_sql.$limit_time.' union '. $select_sql.$from_sql.$three_from_sql.$limit_time.$order_by ;
			$product_array = Yii::$app->pdb->createCommand($sql)->queryAll();
			$count = count($product_array);
			return $count;
		}
	}
	
	
	
	
	/**
	 * 获取分类根据品牌
	 */
	public static function GetCategoryByProductBrand($cruise_id,$brand_id){
		$sql_value = 'SELECT c1.`cid`,c1.`category_code`,c1.`name` FROM vcos_product t1,vcos_category c1 
				WHERE t1.category_code=c1.category_code AND t1.cruise_id ='.$cruise_id.' AND t1.brand_id ='.$brand_id
				.' AND t1.status=1 GROUP BY t1.category_code';
		
		$category_array = Yii::$app->pdb->createCommand($sql_value)->queryAll();
			
		return $category_array;
	}
	
	
	
	/**
	 * 获取品牌根据商品分类
	 */
	public static function GetBrandProductCategory($cruise_id,$category_code){
		
		$tmp_category_code = explode(',', $category_code);
		
		if(strlen($tmp_category_code[0]) < 5){
			//二级分类
			$sql_value= 'SELECT b1.brand_cn_name,b1.brand_en_name,b1.brand_id FROM vcos_product t1,vcos_brand b1 WHERE t1.brand_id=b1.brand_id
					AND t1.cruise_id ='.$cruise_id.' AND t1.category_code LIKE \''.$category_code.'%\'  AND t1.status=1 GROUP BY t1.brand_id';
		}else {
			//三级分类
			$sql_value= 'SELECT b1.brand_cn_name,b1.brand_en_name,b1.brand_id FROM vcos_product t1,vcos_brand b1 WHERE t1.brand_id=b1.brand_id
					AND t1.cruise_id ='.$cruise_id.' AND t1.category_code IN ('.$category_code.') AND t1.status=1 GROUP BY t1.brand_id';
		}												 
			
		$brand_array = Yii::$app->pdb->createCommand($sql_value)->queryAll();
			
		return $brand_array;
	}
	
	
	public static function GetAllBrandProductCategory($cruise_id,$category_code){
		
		$tmp_array = explode(',', $category_code);
		$tmp_two = array();
		$tmp_three = array();
		
		foreach ($tmp_array as $row){
			if(strlen($row) < 5){
				$tmp_two[] = $row;
			}else {
				$tmp_three[] = $row;
			}
		}
		$three = implode(',', $tmp_three);
		
		
		$sql_value = 'SELECT b1.brand_cn_name,b1.brand_en_name,b1.brand_id FROM vcos_product t1,vcos_brand b1';
		$from_sql = ' WHERE t1.brand_id=b1.brand_id AND t1.cruise_id ='.$cruise_id .' AND t1.status=1 ';
		$order_by = ' GROUP BY t1.brand_id ';
		
		$two_from_sql = '';
		$three_from_sql = '';
		if(!empty($tmp_two))
		{
			foreach($tmp_two as $row){
				$two_from_sql .= ' AND t1.category_code LIKE \''.$row.'%\' ';
			}
		}else {
			$two_from_sql .= ' AND t1.category_code LIKE \'  0  \' ';
		}
		
		if(!empty($three)){
			$three_from_sql .= ' AND t1.category_code IN ('.$three.')';
		}else {
			$three_from_sql .= ' AND t1.category_code IN (0)';
		}
		
		
		$sql = $sql_value.$from_sql.$two_from_sql.' union '.$sql_value.$from_sql.$three_from_sql.$order_by; 
		$brand_array = Yii::$app->pdb->createCommand($sql)->queryAll();


		return $brand_array;
	}
	
	
	
	
	/**
	 * 获取分类根据店铺
	 */
	public static function GetCategoryByProductShop($shop_id){
		$sql_value = 'SELECT c1.`cid`,c1.`category_code`,c1.`name` FROM vcos_product t1,vcos_category c1 
		WHERE t1.category_code=c1.category_code AND t1.shop_id='.$shop_id
		.' AND t1.status=1 GROUP BY t1.category_code';
		
		$category_array = Yii::$app->pdb->createCommand($sql_value)->queryAll();
	
			
		return $category_array;
	}
	
	public static function GetSearchProduct($keyword,$brand_id,$order_type, $order_value,$count_bool=true,$page=1,$limit_size=20){
		
		$where_sql = ' AND p1.product_name LIKE \'%'.$keyword.'%\'';
		
		$where_sql .= !empty($brand_id) ? (' AND p1.brand_id='.$brand_id) : '';
		
		$order_by = '';
		//1评论数，2销量，3价格
		switch($order_type){
			case 1:
				$order_by =' ORDER BY p1.comment_num '.$order_value;
				break;
			case 2:
				$order_by =' ORDER BY p1.sale_num '.$order_value;
				break;
			case 3:
				$order_by =' ORDER BY p1.sale_price '.$order_value;
				break;
		}
		
		$curr_time = date('Y-m-d H:i:s');
		$limit_time =' AND p1.sale_start_time <= \''.$curr_time.'\' AND p1.sale_end_time>\'' .$curr_time.'\'';
		
		
		if($count_bool){
			$sql_value = 'SELECT COUNT(*) FROM vcos_product p1 WHERE p1.cruise_id=1 AND p1.`status`=1 ';
			$sql = $sql_value.$where_sql.$limit_time.$order_by;
			
			$count = Yii::$app->pdb->createCommand($sql)->queryScalar();
			return $count;
			
		}else{
			$sql_value = 'SELECT p1.product_id,product_name,p1.product_img,(p1.standard_price/100) as standard_price,
					(p1.sale_price/100) as sale_price,c3.country_logo
				FROM vcos_product p1,vcos_brand b2,vcos_country c3
				WHERE p1.brand_id = b2.brand_id AND b2.country_id=c3.country_id AND p1.cruise_id=1 AND p1.`status`=1';
			
			$sql = $sql_value.$where_sql.$limit_time.$order_by.' LIMIT '.(($page-1)*$limit_size).','.$limit_size;
				
			$product_array = Yii::$app->pdb->createCommand($sql)->queryAll();
				
			return $product_array;
		}
		
	}
	
	public static function GetSearchProductBrand($keyword){
		$curr_time = date('Y-m-d H:i:s');
		$limit_time = ' AND p1.sale_start_time <= \''.$curr_time.'\' AND p1.sale_end_time>\'' .$curr_time.'\'';
		
		$sql_value = 'SELECT b2.brand_id,b2.brand_cn_name 
				FROM vcos_product p1,vcos_brand b2 
				WHERE p1.brand_id = b2.brand_id AND cruise_id=1
				AND p1.product_name LIKE \'%'.$keyword.'%\' AND p1.`status`=1 '.$limit_time.' group by p1.brand_id';
		
		$brand_array = Yii::$app->pdb->createCommand($sql_value)->queryAll();
		
		return $brand_array;
	}
	
	public static function GetBrand(){
		$sql_value= 'SELECT brand_id,brand_cn_name,brand_logo,sort_order FROM vcos_brand WHERE brand_status=1 ORDER BY sort_order';
		$brand_array = Yii::$app->pdb->createCommand($sql_value)->queryAll();
		
		return $brand_array;
	}
	public static function quickSort($s,$l,$r)
	{
		if ($l < $r)
    	{
			//Swap(s[l], s[(l + r) / 2]); //将中间的这个数和第一个数交换 参见注1
	        $i = $l;
	        $j =$r;
	        $x = $s[$l];
	        while ($i < $j)
	        {
	            while($i < $j && $s[j] >= $x) // 从右向左找第一个小于x的数
					$j--;  
	            if($i < $j) 
					$s[$i++] = $s[$j];
				
	            while($i < $j && $s[$i] < $x) // 从左向右找第一个大于等于x的数
					$i++;  
	            if($i < $j) 
					$s[$j--] = $s[$i];
	        }
	        $s[$i] = $x;
	        MallService::quickSort($s, $l, $i - 1); // 递归调用 
	        MallService::quickSort($s, $i + 1, $r);
    	}
	}
}