<?php
	namespace app\modules\wifiservice\components;

class MyCurl {

    public static function vcurl($url, $post = '', $cookie = '', $cookiejar = '', $referer = '')
    {   
		$tmpInfo = '';   
//    	$cookiepath = getcwd().'./'.$cookiejar;
// 	    $cookiepath = tempnam('./tmp','cookie'); 
		// $cookiepath = tempnam('/Users/cc/Sites/cruiseapi/tmp','cookie');
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
        
	    // if($cookiejar) {   
	    // 	curl_setopt($curl, CURLOPT_COOKIEJAR, $cookiepath);   
	    // 	curl_setopt($curl, CURLOPT_COOKIEFILE, $cookiepath);   
	    // }
	    
	    //curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);   
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
}
