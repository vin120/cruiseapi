<?php

return [
    'adminEmail' => 'rocklei@vonechina.com',
	'img_save_url'=>"http://tsimg.cruisetone.com/",							//图片存放的地址
	'wifi_addr' =>"http://tsapi.cruisetone.com/wifi/wifi/index?mcode=",		//app中使用web-view嵌套的wifi上网链接
	'wifi_url'=>'http://192.168.8.173/jsp/',		//comst 请求地址
	'wifi_login_name'=>'bisheng',					//comst 后台登录帐号
	'wifi_login_password'=>'bs566570',				//comst 后台登录密码
	'ios_address'=>'https://appsto.re/cn/Uuva_.i',		//ios-app的下载地址
	'android_address'=>'http://tsimg.cruisetone.com/apk/ctone.apk',	//android-app的下载地址
	'on_cruise' => true,				//是否在船上 ，true表示在船上，false表示在岸上
 	'limit_ip'	=> '10.2.0.0,172.16.8.0,172.16.9.0',	//限制会员上网ip
 	'half_price_day' => 15,						//船员半折的日期,初定为15号
	'deny_action' =>[							//403的页面，在岸上显示的
			
		//餐饮服务
		'restaurant/findall',				
		'restaurant/findrestaruantbyid',
		'restaurant/findallfoodcategory',
		'restaurant/findfoodbycategoryid',
			
		//休闲服务
		'lifeservice/findallcategory',
		'lifeservice/findlifeservicebycategoryid',
		'lifeservice/findlifeservicebyid',
		
			
		//托管行李
		'baggage/findbaggage',
		'baggage/findbaggageinfo',
		'baggage/baggagefiling',
		'baggage/getbaggagefiling',
		'baggage/delbaggagefiling',
			
			
		//客舱服务
		'cruise/getcruiseservice',
		'cruise/commitcabinservice',
		'cruise/findcruiseservice',
			
			
		//免税精品
		'mall/mainpage',
		'mall/getactivity',
		'mall/getnavigationcategoryandbrand',
		'mall/getcategoryproduct',
		'mall/getbrandproduct',
		'mall/getshopproduct',
		'mall/getproductbasicinfo',
		'mall/getproductgraphicinfo',
		'mall/getcommect',
		'mall/submitcommect',
		'mall/getallcategoryproduct',
			
		//邮轮动态
		'cruise/findallarticle',
		'cruise/findarticlebyid',
		'cruise/findsurvey',
		'cruise/commitsurvey',
		
			
		//评价反馈	
		'cruise/findsurvey',
		'cruise/commitsurvey',
			
	]
];
