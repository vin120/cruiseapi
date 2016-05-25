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
	public static function CheckFlow($passport)
	{
// 		MyCurl::vcurl(Yii::$app->params['wifi_url'],'status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
		MyCurl::vcurl(Yii::$app->params['wifi_url'].'comstserver.awm?','status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
		$check_out_params = "status=manage&opt=dbcs&subopt=checkout&dbName=usermanage_umb&admin=bisheng&account=$passport";
// 		$check_out_json = MyCurl::vcurl(Yii::$app->params['wifi_url'],$check_out_params);
		$check_out_json = MyCurl::vcurl(Yii::$app->params['wifi_url']."comstserver.awm?",$check_out_params);
		$check_out_json = iconv('GB2312', 'UTF-8', $check_out_json);
		return $check_out_json;
	}
	
	//网络链接
    public static function Connect($passport)
    {
//     	MyCurl::vcurl(Yii::$app->params['wifi_url'],'status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
    	MyCurl::vcurl(Yii::$app->params['wifi_url'].'comstserver.awm?','status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
    	$online_param = "status=login&opt=login&IsAjaxClient=1&account=$passport&pwd=$passport";
//     	$online_json = MyCurl::vcurl(Yii::$app->params['wifi_url'],$disc_param);
    	$online_json = MyCurl::vcurl(Yii::$app->params['wifi_url']."comstserver.awm?",$online_param);
    	$online_json = iconv('GB2312', 'UTF-8', $online_json);
    	return $online_json;
    }
    
    //查找comst中$passport对应的idRec
    public static function FindidRec($passport)
    {
//     	MyCurl::vcurl(Yii::$app->params['wifi_url'],'status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
    	MyCurl::vcurl(Yii::$app->params['wifi_url'].'comstserver.awm?','status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
//     	$url = "http://192.168.9.250/jsp/fee_checkout/comstserver.awm?";
    	$url = Yii::$app->params['wifi_url']."fee_checkout/comstserver.awm?";
    	$find_params = "status=manage&subopt=checkout&opt=dbcs&dbName=usermanage_umb&admin=".Yii::$app->params['wifi_login_name']."&account=$passport";
    	$find_json = MyCurl::vcurl($url,$find_params);
    	$find_json = iconv('GB2312', 'UTF-8', $find_json);
    	$res = json_decode($find_json,true);
    	$idRec = $res['data']['userId'];
    	return $idRec;
    }
    
    
    //断开网络
    public static function DisConnect($idRec)
    {
//     	MyCurl::vcurl(Yii::$app->params['wifi_url'],'status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
    	MyCurl::vcurl(Yii::$app->params['wifi_url'].'comstserver.awm?','status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
    	$disc_param = 'status=manage&opt=dbcs&subopt=disc&dbName=usermanage_umb&idRec='.$idRec;
//     	$disc_json = MyCurl::vcurl(Yii::$app->params['wifi_url'],$disc_param);
    	$disc_json = MyCurl::vcurl(Yii::$app->params['wifi_url']."comstserver.awm?",$disc_param);
    	$disc_json = iconv('GB2312', 'UTF-8', $disc_json);
    	return $disc_json;
    }
    
    //创建用户
    public static function CreateUser($member)
    {
    	//模拟登录
    	MyCurl::vcurl(Yii::$app->params['wifi_url'].'comstserver.awm?','status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
    	
//     	$create_url = "http://192.168.9.250/jsp/um_add/comstserver.awm?";
    	$create_url = Yii::$app->params['wifi_url']."um_add/comstserver.awm?";
    	
    	//UTF-8 转换为 GB2312
    	$date = iconv('UTF-8','GB2312', date('Y年m月d日',time()));
    	$LinkName = iconv('UTF-8','GB2312', $member['cn_name']);
    	
    	$create_user_param = "status=manage&opt=dbcs&dbName=usermanage_umb&subopt=add&Account=".$member['passport_number']."&pwd=".$member['passport_number']."&idUgb=1&isStartAcc=1&LinkName=".$LinkName."&paperType=6&paperNum=".$member['passport_number']."&phone=".$member['mobile_number']."&email=".$member['member_email']."&limitData=".$date;
    	
    	$create_json = MyCurl::vcurl($create_url,$create_user_param);
    	$create_json = iconv('GB2312', 'UTF-8', $create_json);
    	return $create_json;
    }
    
    
    //查找comst里的user
    public static function FindUser($username)
    {
    	//模拟登录
    	MyCurl::vcurl(Yii::$app->params['wifi_url'].'comstserver.awm?','status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
    	
//     	$find_url = "http://192.168.9.250/jsp/um_query/comstserver.awm?";
    	$find_url = Yii::$app->params['wifi_url']."um_query/comstserver.awm?";
    	$find_params = "status=manage&opt=dbcs&dbName=usermanage_umb&subopt=query&account='$username'&IsAccount=1";
    	
    	$find_json = MyCurl::vcurl($find_url,$find_params);
    	$find_json = iconv('GB2312', 'UTF-8', $find_json);
    	return $find_json;
    }
    
    
    //充钱到账户
    public static function RechargeWifi($passport,$price)
    {
    	//模拟登录
    	MyCurl::vcurl(Yii::$app->params['wifi_url'].'comstserver.awm?','status=manage&opt=login&admin='.Yii::$app->params['wifi_login_name'].'&pwd='.Yii::$app->params['wifi_login_password']);
    	
    	//查找comst中$passport对应的idRec
//     	$url = "http://192.168.9.250/jsp/fee_checkout/comstserver.awm?";
    	$url = Yii::$app->params['wifi_url']."fee_checkout/comstserver.awm?";
    	$find_params = "status=manage&subopt=checkout&opt=dbcs&dbName=usermanage_umb&admin=".Yii::$app->params['wifi_login_name']."&account=$passport";
    	$find_json = MyCurl::vcurl($url,$find_params);
    	$find_json = iconv('GB2312', 'UTF-8', $find_json);
    	$res = json_decode($find_json,true);
    	$idRec = $res['data']['userId'];
    	
    	//在comst系统中充钱
    	$pay_params = "admin=".Yii::$app->params['wifi_login_name']."&opt=dbcs&status=manage&subopt=paymoney&dbName=usermanage_umb&idRec=".$idRec."&money=".$price;
    	$pay_json = MyCurl::vcurl($url,$pay_params);
    	$pay_json = iconv('GB2312', 'UTF-8', $pay_json);
    	return $pay_json;
    }
    
    
    
}
