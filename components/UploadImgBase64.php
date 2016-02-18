<?php

namespace app\components;
use Yii;

class UploadImgBase64
{
	public static function upload_file($input_name, $file_path='./',$allow_size=1){
		
		// 上传的文件
		$file=$input_name;
		
		
		// 错误信息
		$error='';

	
		//Base64解密 --HTML上传需要这两行
// 		$file_body = substr(strstr($file,','),1);	//去头部信息  data:image/jpg;base64,
// 		$data = base64_decode($file_body);
		
		//Base64解密--IOS上传需要这行
		$data = base64_decode($file);
		
		
		$filesize = strlen($data);
		
		// 检查上传文件的大小是否超过指定大小
		$size=$allow_size*1024*1024;
		if( $filesize > $size ){
// 			$error="你上传的文件大小请不要超过{$allow_size}MB";
// 			Helper::show_message($error);
// 			return $response['error'] = array('error_code'=>1,'message'=>'please keep the picture in '.$allow_size.' MB');
			die;
		}
		
		
		
		// 自动生成目录
		if ( !file_exists($file_path) ) {
			mkdir($file_path, 0777, true);
		}
	
		// 生成保存到服务器的文件名 ，后缀为.png
		$filename=date('YmdHis').mt_rand(1000,9999)."."."jpg";
		
		
		if(file_put_contents($file_path.'/'.$filename,$data))
		{
			return [
					'error'=>0,
					'filename'=>$filename,
			];
		}
		
	}
}