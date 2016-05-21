<?php
	namespace app\modules\wifiservice\components;
	use Yii;
	
class MyCurl {
	
	//curl 发送请求
    public static function vcurl($url, $post = '', $cookie = '', $cookiejar = '', $referer = '')
    {   
		$tmpInfo = '';
	    $curl = curl_init();   
	    curl_setopt($curl, CURLOPT_URL, $url);   
	    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);   
	    
	    if($referer) {   
	   		curl_setopt($curl, CURLOPT_REFERER, $referer);   
	    } else {   
	    	curl_setopt($curl, CURLOPT_AUTOREFERER, 1);    
	    }   
	    if($post) {   
	    	curl_setopt($curl, CURLOPT_POST, 1);    
	    	curl_setopt($curl, CURLOPT_POSTFIELDS, $post);   
	    }   
	    if($cookie) {   
	    	curl_setopt($curl, CURLOPT_COOKIE, $cookie);   
	    }  
	    curl_setopt($curl, CURLOPT_TIMEOUT, 100);   
	    curl_setopt($curl, CURLOPT_HEADER, 0);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	    $tmpInfo = curl_exec($curl);
	    if (curl_errno($curl)) {
	    	echo '<pre><b>错误:</b><br />'.curl_error($curl);
	    }
	    curl_close($curl);
	    return $tmpInfo;
	}

     // 删除Cookie函数
	public static function delcookie($cookie_file)
	{ 
		@unlink($cookie_file); // 执行删除
	}
    
	//查询流量
	public static function CheckFlow($member)
	{
		MyCurl::vcurl(Yii::$app->params['wifi_url'],'status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
		
		$check_out_params = "status=manage&opt=dbcs&subopt=checkout&dbName=usermanage_umb&admin=bisheng&account=".$member['passport_number'];
		$check_out_json = MyCurl::vcurl(Yii::$app->params['wifi_url'],$check_out_params);
		$check_out_json = iconv('GB2312', 'UTF-8', $check_out_json);
		return $check_out_json;
	}
	
	
	
	//网络链接
    public static function Connect($member)
    {
    	MyCurl::vcurl(Yii::$app->params['wifi_url'],'status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
    	
    	$online_param = 'status=login&opt=login&IsAjaxClient=1&account='.$member['passport_number'].'&pwd='.$member['passport_number'];
    	$online_json = MyCurl::vcurl(Yii::$app->params['wifi_url'],$online_param);
    	$online_json = iconv('GB2312', 'UTF-8', $online_json);
    	return $online_json;
    }
    
    //查找comst中$passport对应的idRec
    public static function FindidRec($member)
    {
    	MyCurl::vcurl(Yii::$app->params['wifi_url'],'status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
    	
    	$url = "http://192.168.9.250/jsp/fee_checkout/comstserver.awm?";
    	$find_params = "status=manage&subopt=checkout&opt=dbcs&dbName=usermanage_umb&admin=".Yii::$app->params['wifi_login_name']."&account=".$member['passport_number'];
    	$find_json = MyCurl::vcurl($url,$find_params);
    	$find_json = iconv('GB2312', 'UTF-8', $find_json);
    	$res = json_decode($find_json,true);
    	$idRec = $res['data']['userId'];
    	return $idRec;
    }
    
    
    //断开网络
    public static function DisConnect($idRec)
    {
    	MyCurl::vcurl(Yii::$app->params['wifi_url'],'status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
    	
    	$disc_param = 'status=manage&opt=dbcs&subopt=disc&dbName=usermanage_umb&idRec='.$idRec;
    	$disc_json = MyCurl::vcurl(Yii::$app->params['wifi_url'],$disc_param);
    	$disc_json = iconv('GB2312', 'UTF-8', $disc_json);
    	return $disc_json;
    }
    
    
    
}
