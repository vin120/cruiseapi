<?php

return [
    'adminEmail' => 'rocklei@vonechina.com',
	'img_save_url'=>"http://tsimg.cruisetone.com/",
// 	'wifi_url'=>'http://192.168.9.250/jsp/comstserver.awm?',
// 	'wifi_url' => 'http://192.168.8.107/jsp/',
	'wifi_url'=>'http://192.168.9.250/jsp/',
	'wifi_login_name'=>'bisheng',
	'wifi_login_password'=>'bs566570',
	'on_cruise' => false,//是否在船上
	'deny_action' =>[	//403的页面
			
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
