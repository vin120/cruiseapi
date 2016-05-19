<?php
	namespace app\modules\components;

class MyCurl {

	// 模拟登录获取Cookie函数
	public static function vlogin($url,$data){ 
//         $cookiepath = tempnam('./tmp','cookie');
		$cookiepath = tempnam('/Users/cc/Sites/tmp/','cookie');
        // 检测Cookie是否存在
        if(!file_exists($cookiepath)) { 
            $curl = curl_init(); 	// 启动一个CURL会话
            curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址           
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
//        	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
            curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
            curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
            curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
            curl_setopt($curl, CURLOPT_COOKIEJAR, $cookiepath); // 存放Cookie信息的文件名称
            curl_setopt($curl, CURLOPT_COOKIEFILE, $cookiepath); // 读取上面所储存的Cookie信息
            curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
            curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
            
            $tmpInfo = curl_exec($curl); // 执行操作
            if (curl_errno($curl)) {
               echo 'Errno'.curl_error($curl);
            }
            curl_close($curl); // 关闭CURL会话
            return $tmpInfo; // 返回数据
        }else{
//      	echo '200';
//      	echo $cookiepath;
        }
    }

    
    // 模拟获取内容函数
     public static function vget($url){ 
//         $cookiepath = tempnam('./tmp','cookie');
     	$cookiepath = tempnam('/Users/cc/Sites/tmp/','cookie');
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址           
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
//      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_HTTPGET, 1); // 发送一个常规的Post请求
//      curl_setopt($curl, CURLOPT_COOKIEFILE, $GLOBALS['cookie_file']); // 读取上面所储存的Cookie信息
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookiepath); // 读取上面所储存的Cookie信息
        
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
           echo 'Errno'.curl_error($curl);
        }
        curl_close($curl); // 关闭CURL会话
        return $tmpInfo; // 返回数据
    }

    // 模拟提交数据函数
     public static function vpost($url,$data){ 
//         $cookiepath = tempnam('./tmp','cookie');
     	$cookiepath = tempnam('/Users/cc/Sites/tmp/','cookie');
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址           
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
//      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1); // 从证书中检查SSL加密算法是否存在
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
        curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
        curl_setopt($curl, CURLOPT_COOKIEFILE, $cookiepath); // 读取上面所储存的Cookie信息
        curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
        curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
        $tmpInfo = curl_exec($curl); // 执行操作
        if (curl_errno($curl)) {
           echo 'Errno'.curl_error($curl);
        }
        curl_close($curl); // 关键CURL会话
        return $tmpInfo; // 返回数据
    }

    // 删除Cookie函数
     public static function delcookie($cookie_file){ 
     	@unlink($cookie_file); // 执行删除
    }
    
    
    public static function vcurl($url, $post = '', $cookie = '', $cookiejar = '', $referer = ''){   
		$tmpInfo = '';   
//    	$cookiepath = getcwd().'./'.$cookiejar;
// 	    $cookiepath = tempnam('./tmp','cookie'); 
		$cookiepath = tempnam('/Users/cc/Sites/tmp/','cookie');
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
	    if($cookiejar) {   
	    	curl_setopt($curl, CURLOPT_COOKIEJAR, $cookiepath);   
	    	curl_setopt($curl, CURLOPT_COOKIEFILE, $cookiepath);   
	    }
	    
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
}
