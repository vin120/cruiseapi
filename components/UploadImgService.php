<?php

namespace app\components;
use Yii;

class UploadImgService 
{
	public static function upload_file($input_name, $file_path='./', $type='image', $allow_size=2){
		// 上传的文件
		$file=$_FILES[$input_name];
	
		// 错误信息
		$error='';
		 
		// 允许上传的文件类型数组
		$allow_type=[
				'image'=>[
						'jpg'=>'image/jpeg',
						'png'=>'image/png',
						'gif'=>'image/gif',
				],
				// 这里可以继续添加文件类型
		];
	
		// 检查上传文件的类型是否在允许的文件类型数组里
		if( !in_array($file['type'], $allow_type[$type]) ){
			$error="请上传".implode('、', array_keys($allow_type[$type]) )."格式的文件";
			Helper::show_message($error);die;
		}
	
		// 检查上传文件的大小是否超过指定大小
		$size=$allow_size*1024*1024;
		if( $file['size'] > $size ){
			$error="你上传的文件大小请不要超过{$allow_size}MB";
			Helper::show_message($error);die;
		}
	
		// 错误状态
		switch($file['error']){
			case 1:
				$error='你所上传的文件大小超过了服务器配置的大小';
				Helper::show_message($error);die;
			case 2:
				$error='你所上传的文件大小超过了表单设置的大小';
				Helper::show_message($error);die;
			case 3:
				$error='网络出现问题，请检查你的网络是否连接？';
				Helper::show_message($error);die;
			case 4:
				$error='请选择你要上传的文件';
				Helper::show_message($error);die;
		}
	
		// 自动生成目录
		if ( !file_exists($file_path) ) {
			mkdir($file_path, 0777, true);
		}
	
		if($error){
			return [
					'error'=>1,
					'warning'=>$error,
			];
		}
	
		// 生成保存到服务器的文件名
		$filename=date('YmdHis').mt_rand(1000,9999).".".array_search($file['type'], $allow_type[$type]);
		// 保存上传文件到本地目录
		if( move_uploaded_file($file['tmp_name'], $file_path."/".$filename) ){
			return [
					'error'=>0,
					'filename'=>$filename,
			];
		}
	}
}