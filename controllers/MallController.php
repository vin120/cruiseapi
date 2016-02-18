<?php

namespace app\controllers;


use Yii;
use app\components\MemberService;
use app\components\BaggageService;
use app\components\CruiseLineService;
use app\components\MallService;
// use app\components\UploadImgService;
use app\components\UploadImgBase64;
use app\models\ProductComment;



class MallController extends MyActiveController
{

	public function actionMainpage()
	{
		$cruise_id = isset($_POST['cruise_id']) ? $_POST['cruise_id'] : '1';
		$style_type = isset($_POST['style_type']) ? $_POST['style_type'] : '1';
		
		$response = array();
		
		if(!empty($cruise_id)){
			$navigation_array = MallService::getShowNavigation($cruise_id, $style_type);
			$main_activity_id = 0;
			foreach($navigation_array as $navigation){
				if(1 == $navigation['is_main']){
					$main_activity_id = $navigation['activity_id'];
					break;
				}
			}
			
			$response = MallService::getActivityAllInfo($main_activity_id);
			
			$response['data']['navigation_items'] = $navigation_array;
			
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'cruise id can not be empty');
		}
		
		return  $response;
	}
	
	public function actionGetactivity(){
		$activity_id = isset($_POST['activity_id']) ? $_POST['activity_id'] : '';
		//$shop_id = isset($_POST['shop_id']) ? $_POST['shop_id'] : '';
		$response = array();
		if(!empty($activity_id))
		{
			$response = MallService::getActivityAllInfo($activity_id);
			
// 			if(!empty($shop_id)){
// 				$shop_array = MallService::getShopByActivityId(array($shop_id));
// 				if(!empty($shop_array)){
// 					$response['data']['shop'] = $shop_array[0];
// 				}
// 			}
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'activity id can not be empty');
		}
		return $response;
	}
	
	public function actionGetproductbasicinfo(){
		$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : '';
		$response = array();
		
		if(!empty($product_id)){
			$product = MallService::getProductBasicInfoById($product_id);
			
			$product['img_items'] = MallService::getProductImg($product_id);
			
			$response['data'] = $product;
			
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'product id can not be empty');
		}
		
		return $response;
	}
	
	public function actionGetproductgraphicinfo(){
		$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : '';
		$response = array();
		
		if(!empty($product_id)){
			$response['data']['graphic_items'] = MallService::getProductGraphic($product_id);
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'product id can not be empty');
		}
		
		return $response;
	}
	
	public function actionGetcommect()
	{
		$product_id = isset($_POST['product_id']) ? $_POST['product_id'] : '';
		$comment_type = isset($_POST['comment_type']) ? $_POST['comment_type'] : '1';
		$curr_page = isset($_POST['page']) ? $_POST['page'] : '1';
		$size = isset($_POST['size']) ? $_POST['size'] : '20';
		
		$response = array();
		
		$total_page = 0;
		if(!empty($product_id)){
			$total_count = MallService::getProductComment($product_id, $comment_type,0);
			$total_page = ceil($total_count/$size);
			if($curr_page >$total_page || $curr_page < 1){
				$curr_page = 1;
			}
			
			$main_comment_array = MallService::getProductComment($product_id, $comment_type,1,$curr_page,$size);
			
			$response['data']['comment_items']=$main_comment_array;
			
			$total_count = MallService::getProductComment($product_id, 1,0);
			$good_count = MallService::getProductComment($product_id, 2,0);
			$neutral_count = MallService::getProductComment($product_id, 3,0);
			$bad_count = MallService::getProductComment($product_id, 4,0);
			$is_add_count = MallService::getProductComment($product_id, 5,0);
			$is_img_count = MallService::getProductComment($product_id, 6,0);
			
			$response['data']['comment_result']['total_count']=$total_count;
			$response['data']['comment_result']['good_count']=$good_count;
			$response['data']['comment_result']['neutral_count']=$neutral_count;
			$response['data']['comment_result']['bad_count']=$bad_count;
			$response['data']['comment_result']['is_add_count']=$is_add_count;
			$response['data']['comment_result']['is_img_count']=$is_img_count;
			
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'product id can not be empty');
		}
		
		return $response;
	}
	
	
	public function actionGetmycommect()
	{
		$data = isset($_POST['data']) ? $_POST['data'] : '';
		
		if(!empty($data)){
			$data_json = json_decode($data,true);
			
			$sign = isset($data_json['sign']) ? $data_json['sign'] : '';
			$product_ids = isset($data_json['product_ids']) ? $data_json['product_ids'] : '';
			$order_serial_num = isset($data_json['order_serial_num']) ? $data_json['order_serial_num'] : '';
			$response = array();
			$comment = array();
			$tmp = array();$tmp_arr = array();
			if(!empty($product_ids) && !empty($sign) && !empty($order_serial_num)){
				$member = MemberService::getMemberbysign($sign);
				if(!empty($member)){
					foreach ($product_ids as $k => $product_id_tmp){
						$params = [':product_id'=>$product_id_tmp['product_id'],':member_code'=>$member->member_code,':order_serial_num'=>$order_serial_num];
						$sql = ' SELECT  comment_id,comment_type,product_id,comment_content,crater_time,member_code,
							member_name,score,url_img1,url_img2,url_img3,url_img4,reply_content,reply_create_time,is_add_comment,is_upload_img
							FROM vcos_product_comment
							WHERE product_id=:product_id AND member_code=:member_code AND order_serial_num=:order_serial_num ';
							
						$comment[$k] = Yii::$app->pdb->createCommand($sql,$params)->queryAll();
						if($comment[$k]){
							foreach ($comment[$k] as $kk => $row){
								if($comment[$k][$kk]['url_img1'] === null)
								{
									$comment[$k][$kk]['url_img1'] = '';
								}
								if($comment[$k][$kk]['url_img2'] === null)
								{
									$comment[$k][$kk]['url_img2'] = '';
								}
								if($comment[$k][$kk]['url_img3'] === null)
								{
									$comment[$k][$kk]['url_img3'] = '';
								}
								if($comment[$k][$kk]['url_img4'] === null)
								{
									$comment[$k][$kk]['url_img4'] = '';
								}
							}
						}
					}
					$response['data'] = $comment;
				}else {
					$response['error'] = array('error_code'=>2,'message'=>'Member does not exist');
				}
			
			}else{
				$response['error'] = array('error_code'=>1,'message'=>'sign , product id  and order_time can not be empty');
			}
			
			
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'data can not be empty');
		}
	
		return $response;
	}
	
	
	
	
	public function actionGetnavigationcategoryandbrand()
	{
		$navigation_id = isset($_POST['navigation_id']) ? $_POST['navigation_id'] : '';
		
		$response = array();
		
		if(!empty($navigation_id)){
			$response = MallService::getNavigationCategoryAndBrand($navigation_id);		
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'navigation id can not be empty');
		}
		
		return $response;
	}
	
	
// 	public function actionGetallcategoryproduct()
// 	{
// 		//todo
// 		$navigation_group_id = isset($_POST['navigation_group_id']) ? $_POST['navigation_group_id'] : '';
		
// 		$cruise_id = isset($_POST['cruise_id']) ? $_POST['cruise_id'] : '1';
// 		$where_brand_id = isset($_POST['brand_id']) ? $_POST['brand_id'] : '';
		

// // 		1分类，2品牌，3店铺，4活动，5广告，6商品
// 		$category_type = '1';
		
// 		$order_type = isset($_POST['order_type']) ? $_POST['order_type'] : '1';//1评论数，2销量，3价格
// 		$order_value = isset($_POST['order_value']) ? $_POST['order_value'] : ' ASC';
		
// 		$curr_page = isset($_POST['page']) ? $_POST['page'] : '1';
// 		$limit_size = isset($_POST['size']) ? $_POST['size'] : '20';
		
		
// 		$curr_page = isset($_POST['page']) ? $_POST['page'] : '1';
// 		$limit_size = isset($_POST['size']) ? $_POST['size'] : '20';
		
// 		$shop_id = '';		
// 		$where_category_code='';


// 		$response = array();
// 		$shop_array = array();
// // 		$mapping_id = '';
		
// 		if(!empty($navigation_group_id)){
// 			$nav_category_array = MallService::getNavigationCategoryByGroupid($navigation_group_id);
// 			foreach($nav_category_array as $key=>$nav_category){
// // 				$mapping_id .= $nav_category['mapping_id'].',';
// 				$mapping_id = $nav_category['mapping_id'];
// 				$shop_array[] = MallService::GetProductByWhere($cruise_id, $mapping_id, $category_type, $shop_id, $where_brand_id, $where_category_code, $order_type, $order_value, $curr_page, $limit_size);
// 			}
// // 			$mapping_id = trim($mapping_id,',');
// // 			$response = MallService::GetProductByWhere($cruise_id, $mapping_id, $category_type, $shop_id, $where_brand_id, $where_category_code, $order_type, $order_value, $curr_page, $limit_size);
// 			$response = $shop_array;

// 		}else{
// 			$response['error'] = array('error_code'=>1,'message'=>'navigation id can not be empty');
// 		}
// 		return $response;
// 	}
	
	
	
	
	public function actionGetallcategoryproduct()
	{
		$navigation_group_id = isset($_POST['navigation_group_id']) ? $_POST['navigation_group_id'] : '';
		$cruise_id = isset($_POST['cruise_id']) ? $_POST['cruise_id'] : '1';
		$curr_page = isset($_POST['page']) ? $_POST['page'] : '1';
		$limit_size = isset($_POST['size']) ? $_POST['size'] : '20';
		$where_brand_id = isset($_POST['brand_id']) ? $_POST['brand_id'] : '';
		$mapping_id = '';
		$shop_id = '';
		$where_category_code='';
// 		1分类，2品牌，3店铺，4活动，5广告，6商品
		$category_type = '1';
		
		$order_type = isset($_POST['order_type']) ? $_POST['order_type'] : '1';//1评论数，2销量，3价格
		$order_value = isset($_POST['order_value']) ? $_POST['order_value'] : ' ASC';
		
		if(!empty($navigation_group_id)){
			$nav_category_array = MallService::getNavigationCategoryByGroupid($navigation_group_id);
			foreach($nav_category_array as $nav_category){
				$mapping_id .= $nav_category['mapping_id'].',';
 			}
			$mapping_id = trim($mapping_id,',');
			$response = MallService::GetAllProductByWhere($cruise_id,$mapping_id,$category_type,$shop_id,$where_brand_id,$where_category_code,$order_type,$order_value,$curr_page,$limit_size);

		}else{
			$response['error'] = array('error_code'=>1,'message'=>'navigation id can not be empty');
		}
		return $response;
	}
	
	

	
	public function actionGetcategoryproduct(){
		
		$cruise_id = isset($_POST['cruise_id']) ? $_POST['cruise_id'] : '1';
		$mapping_id = isset($_POST['mapping_id']) ? $_POST['mapping_id'] : '';
		$where_brand_id = isset($_POST['brand_id']) ? $_POST['brand_id'] : '';
		
// 		1分类，2品牌，3店铺，4活动，5广告，6商品
		$category_type = '1';
		
		$order_type = isset($_POST['order_type']) ? $_POST['order_type'] : '1';//1评论数，2销量，3价格
		$order_value = isset($_POST['order_value']) ? $_POST['order_value'] : ' ASC';
		
		$curr_page = isset($_POST['page']) ? $_POST['page'] : '1';
		$limit_size = isset($_POST['size']) ? $_POST['size'] : '20';
		$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
		$shop_id = '';
		$where_category_code = '';
		
		$response = MallService::GetProductByWhere($cruise_id, $mapping_id, $category_type, $shop_id, $where_brand_id, $where_category_code, $order_type, $order_value, $keyword,$curr_page, $limit_size);
		
		return $response;
	}
	
	public function actionGetbrandproduct(){
	
		$cruise_id = isset($_POST['cruise_id']) ? $_POST['cruise_id'] : '1';
		$mapping_id = isset($_POST['brand_id']) ? $_POST['brand_id'] : '';
		$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
		$where_category_code = isset($_POST['category_code']) ? $_POST['category_code'] : '';
	
		//1分类，2品牌，3店铺，4活动，5广告，6商品
		$category_type = 2;
	
		$order_type = isset($_POST['order_type']) ? $_POST['order_type'] : '1';//1评论数，2销量，3价格
		$order_value = isset($_POST['order_value']) ? $_POST['order_value'] : ' ASC';
	
		$curr_page = isset($_POST['page']) ? $_POST['page'] : '1';
		$limit_size = isset($_POST['size']) ? $_POST['size'] : '20';
		$shop_id = '';
		$where_brand_id = '';
		
		$response = MallService::GetProductByWhere($cruise_id, $mapping_id, $category_type, $shop_id, $where_brand_id, $where_category_code, $order_type, $order_value,$keyword, $curr_page, $limit_size);
	
		return $response;
	}
	
	public function actionGetshopproduct(){
		$shop_id = isset($_POST['shop_id']) ? $_POST['shop_id'] : '';
		$where_category_code = isset($_POST['category_code']) ? $_POST['category_code'] : '';
		
		$curr_page = isset($_POST['page']) ? $_POST['page'] : '1';
		$limit_size = isset($_POST['size']) ? $_POST['size'] : '20';
		$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
		$category_type = 3;
		$order_type = 1;//1评论数，2销量，3价格
		
		$response = array();
		$cruise_id=$mapping_id=$where_brand_id=$order_value = '';
		$response = MallService::GetProductByWhere($cruise_id, $mapping_id, $category_type, $shop_id, $where_brand_id, $where_category_code, $order_type, $order_value,$keyword, $curr_page, $limit_size);
		
		$shop_category_array = MallService::GetCategoryByProductShop($shop_id);
		$response['data']['category_items'] = $shop_category_array;
		
		$response['data']['shop'] = MallService::getShopByActivityId(array($shop_id));
		return $response;
	}
	
	public function actionHotsearch()
	{
		$response = array();
		$response['data'] = ["iphone 6s","LV","面膜","笔记本","NIKE","NewBalance",];
		return $response;
		
	}
	public function actionSearch()
	{
		$keyword = isset($_POST['keyword']) ? $_POST['keyword'] : '';
		$brand_id = isset($_POST['brand_id']) ? $_POST['brand_id'] : '';
		
		$curr_page = isset($_POST['page']) ? $_POST['page'] : '1';
		$limit_size = isset($_POST['page_size']) ? $_POST['page_size'] : '20';
		
		$order_type = isset($_POST['order_type']) ? $_POST['order_type'] : '1';//1评论数，2销量，3价格
		$order_value = isset($_POST['order_value']) ? $_POST['order_value'] : ' ASC';
		
		$brand_array = array();
		if(!empty($keyword)){
			if(1 == $curr_page && empty($brand_id)){
				$brand_array = MallService::GetSearchProductBrand($keyword);
				$response['data']['product_brand']=$brand_array;
			}
			
			$total_count = MallService::GetSearchProduct($keyword, $brand_id,$order_type, $order_value);
			
			$total_page = ceil($total_count/$limit_size);
			
			if($curr_page>$total_page || $curr_page<1){
				$curr_page = 1;
			}
			
			$product_array = MallService::GetSearchProduct($keyword, $brand_id,$order_type, $order_value,false,$curr_page,$limit_size);
			$response['data']['product_items']=$product_array;
			
			$_page['count'] = $total_count;
			$_page['curr_page'] = $curr_page;
			$_page['total_page'] = $total_page;
			$_page['limit_size'] = $limit_size;
				
			$response['_page'] = $_page;
			
		}else{
			$response['error'] = array('error_code'=>1,'message'=>'keyword id can not be empty');
		}
		
		return $response;
	}
	
	
	public function actionSubmitcommect()
	{
		try {
			$sign = isset ( $_POST ['sign'] ) ? $_POST ['sign'] : '';
			$comment_type = isset ( $_POST ['comment_type'] ) ? $_POST ['comment_type'] : '';
			$product_id = isset ( $_POST ['product_id'] ) ? $_POST ['product_id'] : '';
			$comment_content = isset ( $_POST ['comment_content'] ) ? $_POST ['comment_content'] : '';
			$score = isset ( $_POST ['score'] ) ? $_POST ['score'] : '';
// 			$crater_time = isset ( $_POST ['crater_time'] ) ? $_POST ['crater_time'] : '';
			$crater_time = date('Y-m-d H:i:s',time());
			$order_serial_num = isset ( $_POST ['order_serial_num'] ) ? $_POST ['order_serial_num'] : '';
			$member_code = '';
			$member_name = '';
			if (!empty($sign) && !empty($product_id) && !empty($order_serial_num)) {
				$member = MemberService::getMemberbysign ( $sign );
				if ($member) {
					
					$member_code = $member->member_code;
					$member_name = $member->cn_name;
					
					$_url_img1 = isset ( $_POST ['url_img1'] ) ? $_POST ['url_img1'] : '';
					$_url_img2 = isset ( $_POST ['url_img2'] ) ? $_POST ['url_img2'] : '';
					$_url_img3 = isset ( $_POST ['url_img3'] ) ? $_POST ['url_img3'] : '';
					$_url_img4 = isset ( $_POST ['url_img4'] ) ? $_POST ['url_img4'] : '';
					
					$is_upload_img = '0';
					$is_add_comment = '0';
					$reply_content = '回复1';
					$reply_create_time = date ( 'Y-m-d H:i:s', time () );
					
					// if($_FILES)
					// {
					// // //第一张图片
					// if($_FILES['url_img1']['error'] != 4){
					// $result = UploadImgService::upload_file('url_img1',Yii::$app->params['img_save_url'].'Mall/'.$product_id,'image',3);
					// $photo = $result['filename'];
					// $url_img1 = 'Mall/'.$product_id.'/'.$photo;
					// }
					
					// //第二张图片
					// if($_FILES['url_img2']['error'] != 4){
					// $result = UploadImgService::upload_file('url_img2',Yii::$app->params['img_save_url'].'Mall/'.$product_id,'image',3);
					// $photo = $result['filename'];
					// $url_img2 = 'Mall/'.$product_id.'/'.$photo;
					// }
					
					// //第三张图片
					// if($_FILES['url_img3']['error'] != 4){
					// $result = UploadImgService::upload_file('url_img3',Yii::$app->params['img_save_url'].'Mall/'.$product_id,'image',3);
					// $photo = $result['filename'];
					// $url_img3 = 'Mall/'.$product_id.'/'.$photo;
					// }
					
					// //第四张图片
					// if($_FILES['url_img4']['error'] != 4){
					// $result = UploadImgService::upload_file('url_img4',Yii::$app->params['img_save_url'].'Mall/'.$product_id,'image',3);
					// $photo = $result['filename'];
					// $url_img4 = 'Mall/'.$product_id.'/'.$photo;
					// }
					
					// if(!empty($url_img1) || !empty($url_img2) || !empty($url_img3) || !empty($url_img4)){
					// if(!empty($url_img1)){
					// $is_upload_img = '1';
					// }
					// }
					
					// if($url_img1 != '')
					// {
					// $url_img1_body = substr(strstr($url_img1,','),1); //去头部信息 data:image/jpg;base64,
					// $data = base64_decode($url_img1);
					
					// $file_path = "./img/abc";
					// $file_name = "url_img2.jpg";
					
					// if ( !file_exists($file_path) ) {
					// mkdir($file_path, 0777, true);
					// }
					
					// $file = fopen($file_name,'w');
					// fclose($file);
					
					// file_put_contents($file_path.'/'.$file_name,$data);
					
					// }
					
					// $url_111  =Yii::$app->params ['img_save_url'] . 'mall/';
					// return $url_111;exit;

					if ($_url_img1 != '') {
						$result = UploadImgBase64::upload_file ( $_url_img1, Yii::$app->params ['img_save_url'] . 'mall/' );
						$photo = $result ['filename'];
						$url_img1 = 'mall/' . $photo;
					}
					
					if ($_url_img2 != '') {
						$result = UploadImgBase64::upload_file ( $_url_img2, Yii::$app->params ['img_save_url'] . 'mall/'  );
						$photo = $result ['filename'];
						$url_img2 = 'mall/' . $photo;
					}
					
					if ($_url_img3 != '') {
						$result = UploadImgBase64::upload_file ( $_url_img3, Yii::$app->params ['img_save_url'] . 'mall/'  );
						$photo = $result ['filename'];
						$url_img3 = 'mall/' . $photo;
					}
					
					if ($_url_img4 != '') {
						$result = UploadImgBase64::upload_file ( $_url_img4, Yii::$app->params ['img_save_url'] . 'mall/' );
						$photo = $result ['filename'];
						$url_img4 = 'mall/'  . $photo;
					}
					
					if ($_url_img1 != '' || $_url_img2 != '' || $_url_img3 != '' || $_url_img4 != '') {
						$is_upload_img = '1';
					}
					
					// 查找是否符合追加评论的条件
					$sql = " SELECT comment_id FROM vcos_product_comment WHERE product_id = '{$product_id}' AND member_code = '{$member_code}' AND member_name = '{$member_name}' AND order_serial_num='{$order_serial_num}' AND comment_type=1 ";
					$comment = Yii::$app->pdb->createCommand ( $sql )->queryOne ();
					if ($comment) {
						$update_sql = "UPDATE vcos_product_comment SET is_add_comment=1 WHERE product_id = '{$product_id}' AND member_code = '{$member_code}' AND member_name = '{$member_name}' AND order_serial_num='{$order_serial_num}' AND comment_type=1 ";
						Yii::$app->pdb->createCommand ( $update_sql )->execute ();
					}
					
					$productComment = new ProductComment ();
					$productComment->comment_type = $comment_type;
					$productComment->product_id = $product_id;
					$productComment->comment_content = $comment_content;
					$productComment->crater_time = $crater_time;
					$productComment->member_code = $member_code;
					$productComment->member_name = $member_name;
					$productComment->order_serial_num = $order_serial_num;
					$productComment->score = $score;
					$productComment->url_img1 = isset ( $url_img1 ) ? $url_img1 : '';
					$productComment->url_img2 = isset ( $url_img2 ) ? $url_img2 : '';
					$productComment->url_img3 = isset ( $url_img3 ) ? $url_img3 : '';
					$productComment->url_img4 = isset ( $url_img4 ) ? $url_img4 : '';
					$productComment->is_upload_img = $is_upload_img;
					$productComment->is_add_comment = $is_add_comment;
					$productComment->reply_content = $reply_content;
					$productComment->reply_create_time = $reply_create_time;
					$productComment->save ();
					
					$response = array ();
					$response ['data'] = array (
							'code' => 1,
							'message' => 'success' 
					);
				}else{
					$response ['error'] = array (
							'code' => 2,
							'message' => 'member not exist.'
					);
				}
			} else {
				$response ['error'] = array (
						'code' => 1,
						'message' => 'sign and product_id can not be empty.'
				);
			}
			return $response;
		} catch ( Exception $e ) {
			return $e;
		}
	}
	
	
}
