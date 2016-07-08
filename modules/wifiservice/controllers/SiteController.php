<?php

namespace app\modules\wifiservice\controllers;

use Yii;
use app\modules\wifiservice\models\LoginForm;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\helpers\Url;
use yii\filters\VerbFilter;
use app\modules\wifiservice\components\MyCurl;


/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
	public $enableCsrfValidation = false; // csrf validation can't work
    public function behaviors_()
    {
		return [
			'access' => [
 				'class' => MyAccessControl::className(),
				'rules' => [
					[
    					'actions' => ['login'],
    					'allow' => true,
    				],
    				[
    					'allow' => true,
    					'roles' => ['@'],
    				],
    			],
					
    		],
    		'verbs' => [
    			'class' => VerbFilter::className(),
    				'actions' => [
    					'logout' => ['get'],
    				],
    		],
    	];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $this->layout = 'myloyout';
        return $this->render('travelagent');
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
		$active = Yii::$app->request->get("active",'1');
        $this->layout = 'login_loyout';
       
        if (!\Yii::$app->admin->isGuest) {
        	
        	$type = Yii::$app->admin->identity->member_type;
        	$member_code = Yii::$app->admin->identity->member_code;
        	$passport_number = Yii::$app->admin->identity->passport_number;
        	$member_password = Yii::$app->admin->identity->member_password;
        	
        	if(empty(Yii::$app->admin->identity->sign)){
        		$sign = md5 ( md5 ( $member_code ) . md5 ( $passport_number ) . md5 ($member_password) );
        		if($type == 1){
        			//会员
        			$sql = " UPDATE `vcos_member` SET `sign`='$sign' WHERE `member_code`='$member_code'";
        			Yii::$app->mdb->createCommand($sql)->execute();
        		}else {
        			//船员
        			$sql = " UPDATE `vcos_wifi_crew` SET `sign`='$sign' WHERE `crew_code`='$member_code' ";
        			Yii::$app->mdb->createCommand($sql)->execute();
        		}
        	}
        	
        	if($type == 2){
        		//船员
        		
        		//获取数据库中的limit_ip
        		//获取船员登录的crew_onlie_ip
        		//1.截取limit_ip,如果最后一个是0，则匹配前3个段，如192.168.1，如果和crew_onlie_ip的前3段不匹配，则通不过。
        		//2.截取limit_ip,如果最后一个不是0，则limit_ip与crew_online_ip对比，如果不匹配，则通不过。
        		
        		$sql = "SELECT `limit_ip` FROM `vcos_wifi_crew` WHERE `passport_number`='$passport_number'";
        		$limit_ip = Yii::$app->mdb->createCommand($sql)->queryOne()['limit_ip'];
        		if(!empty($limit_ip)){
        			//获取船员上网的ip，
        			$crew_onlie_ip = MyCurl::getIp();
        			$limit_ip_arr  =explode(",", $limit_ip);
        			$tag = 0;	//标志，用于判断ip是否相等
        			foreach ($limit_ip_arr as $limit_ip_tmp){
        				$limit_ip_tmp_arr =  explode(".",$limit_ip_tmp);	//通过. 分割limit_ip
        				
        				if($limit_ip_tmp_arr[2] == 0){
        					//截取前面的2段
        					$limit_ip_segment = $limit_ip_tmp_arr[0].$limit_ip_tmp_arr[1];
        					 
        					//截取crew_online_ip
        					$crew_onlie_ip_arr = explode(".", $crew_onlie_ip);
        					$crew_onlie_ip_segment = $crew_onlie_ip_arr[0].$crew_onlie_ip_arr[1];
        					//与船员的ip前面3段对比
        					if($limit_ip_segment === $crew_onlie_ip_segment){
        						$tag = 1;
        						break;
        					}
        				}else if($limit_ip_tmp_arr[2] != 0 && $limit_ip_tmp_arr[3] == 0){
        					//截取到的是ip段
        					//截取前面的3段
        					$limit_ip_segment = $limit_ip_tmp_arr[0].$limit_ip_tmp_arr[1].$limit_ip_tmp_arr[2];
        					
        					//截取crew_online_ip
        					$crew_onlie_ip_arr = explode(".", $crew_onlie_ip);
        					$crew_onlie_ip_segment = $crew_onlie_ip_arr[0].$crew_onlie_ip_arr[1].$crew_onlie_ip_arr[2];
        					//与船员的ip前面3段对比
        					if($limit_ip_segment === $crew_onlie_ip_segment){
        						$tag = 1;
        						break;
        					}
        				}else {
        					//截取到的是ip，对比ip是否与船员ip相等，如果相等则通过，否则出错
        					if($limit_ip_tmp === $crew_onlie_ip){
        						$tag = 1;
        						break;
        					}
        				}
        			}
        			if($tag == 0){
        				//两个ip不相同
        				$model = new LoginForm();
        				Yii::$app->admin->logout();
        				$model->addError("ip","ip wrong");
        				return $this->render('agent_login', [
        						'model' => $model,
        						'active'=> $active,
        				]);
        			}
        		}
        	}else{
        		//会员
        		$limit_ip = Yii::$app->params['limit_ip'];
        		$limit_ip_arr = explode(",", $limit_ip);
        		$member_online_ip = MyCurl::getIp();
        		$tag = 0;
        		foreach ($limit_ip_arr as $limit_ip_tmp){
        			$limit_ip_tmp_arr =  explode(".",$limit_ip_tmp);	//通过. 分割limit_ip
        			$limit_ip_segment = $limit_ip_tmp_arr[0].$limit_ip_tmp_arr[1].$limit_ip_tmp_arr[2];
        			
        			//截取member_online_ip
        			$member_online_ip_arr = explode(".", $member_online_ip);
        			$member_onlie_ip_segment = $member_online_ip_arr[0].$member_online_ip_arr[1].$member_online_ip_arr[2];
        			
        			if($member_onlie_ip_segment == $limit_ip_segment){
        				$tag = 1;
        			}
        		}
        		
        		if($tag == 1){
        			$model = new LoginForm();
        			Yii::$app->admin->logout();
        			$model->addError("ip","ip wrong");
        			return $this->render('agent_login', [
        					'model' => $model,
        					'active'=> $active,
        			]);
        		}
        	}
        	
        	if(Yii::$app->admin->identity->member_password === '888888'){
        		return Yii::$app->getResponse()->redirect(Url::to("/wifiservice/wifi/resetpassword"));
        	}else{
        		return Yii::$app->getResponse()->redirect(Url::to("/wifiservice/wifi/index"));
        	}
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
        	$type = Yii::$app->admin->identity->member_type;
        	$member_code = Yii::$app->admin->identity->member_code;
        	$passport_number = Yii::$app->admin->identity->passport_number;
        	$member_password = Yii::$app->admin->identity->member_password;
        	
        	if(empty(Yii::$app->admin->identity->sign)){
        		$sign = md5 ( md5 ( $member_code ) . md5 ( $passport_number ) . md5 ($member_password) );
        		if($type == 1){
        			//会员
        			$sql = " UPDATE `vcos_member` SET `sign`='$sign' WHERE `member_code`='$member_code'";
        			Yii::$app->mdb->createCommand($sql)->execute();
        		}else {
        			//船员
        			$sql = " UPDATE `vcos_wifi_crew` SET `sign`='$sign' WHERE `crew_code`='$member_code' ";
        			Yii::$app->mdb->createCommand($sql)->execute();
        		}
        	}
        	
        	if($type == 2){
        		//船员
        		
        		//获取数据库中的limit_ip
        		//获取船员登录的crew_onlie_ip
        		//1.截取limit_ip,如果最后一个是0，则匹配前3个段，如192.168.1，如果和crew_onlie_ip的前3段不匹配，则通不过。
        		//2.截取limit_ip,如果最后一个不是0，则limit_ip与crew_online_ip对比，如果不匹配，则通不过。
        		
        		$sql = "SELECT `limit_ip` FROM `vcos_wifi_crew` WHERE `passport_number`='$passport_number'";
        		$limit_ip = Yii::$app->mdb->createCommand($sql)->queryOne()['limit_ip'];
        		if(!empty($limit_ip)){
        			//获取船员上网的ip，
        			$crew_onlie_ip = MyCurl::getIp();
        			$limit_ip_arr  =explode(",", $limit_ip);
        			$tag = 0;	//标志，用于判断ip是否相等
        			foreach ($limit_ip_arr as $limit_ip_tmp){
        				$limit_ip_tmp_arr =  explode(".",$limit_ip_tmp);	//通过. 分割limit_ip
        				
        				if($limit_ip_tmp_arr[2] == 0){
        					//截取前面的2段
        					$limit_ip_segment = $limit_ip_tmp_arr[0].$limit_ip_tmp_arr[1];
        					 
        					//截取crew_online_ip
        					$crew_onlie_ip_arr = explode(".", $crew_onlie_ip);
        					$crew_onlie_ip_segment = $crew_onlie_ip_arr[0].$crew_onlie_ip_arr[1];
        					//与船员的ip前面3段对比
        					if($limit_ip_segment === $crew_onlie_ip_segment){
        						$tag = 1;
        						break;
        					}
        				}else if($limit_ip_tmp_arr[2] != 0 && $limit_ip_tmp_arr[3] == 0){
        					//截取到的是ip段
        					//截取前面的3段
        					$limit_ip_segment = $limit_ip_tmp_arr[0].$limit_ip_tmp_arr[1].$limit_ip_tmp_arr[2];
        					
        					//截取crew_online_ip
        					$crew_onlie_ip_arr = explode(".", $crew_onlie_ip);
        					$crew_onlie_ip_segment = $crew_onlie_ip_arr[0].$crew_onlie_ip_arr[1].$crew_onlie_ip_arr[2];
        					//与船员的ip前面3段对比
        					if($limit_ip_segment === $crew_onlie_ip_segment){
        						$tag = 1;
        						break;
        					}
        				}else {
        					//截取到的是ip，对比ip是否与船员ip相等，如果相等则通过，否则出错
        					if($limit_ip_tmp === $crew_onlie_ip){
        						$tag = 1;
        						break;
        					}
        				}
        			}
        			if($tag == 0){
        				//两个ip不相同
        				Yii::$app->admin->logout();
        				$model->addError("ip","ip wrong");
        				return $this->render('agent_login', [
        						'model' => $model,
        						'active'=> $active,
        				]);
        			}
        		}
        	}else{
        		//会员
        		$limit_ip = Yii::$app->params['limit_ip'];
        		$limit_ip_arr = explode(",", $limit_ip);
        		$member_online_ip = MyCurl::getIp();
        		$tag = 0;
        		foreach ($limit_ip_arr as $limit_ip_tmp){
        			$limit_ip_tmp_arr =  explode(".",$limit_ip_tmp);	//通过. 分割limit_ip
        			$limit_ip_segment = $limit_ip_tmp_arr[0].$limit_ip_tmp_arr[1].$limit_ip_tmp_arr[2];
        			 
        			//截取member_online_ip
        			$member_online_ip_arr = explode(".", $member_online_ip);
        			$member_onlie_ip_segment = $member_online_ip_arr[0].$member_online_ip_arr[1].$member_online_ip_arr[2];
        			 
        			if($member_onlie_ip_segment == $limit_ip_segment){
        				$tag = 1;
        			}
        		}
        		
        		if($tag == 1){
        			$model = new LoginForm();
        			Yii::$app->admin->logout();
        			$model->addError("ip","ip wrong");
        			return $this->render('agent_login', [
        					'model' => $model,
        					'active'=> $active,
        			]);
        		}
        	}
        	
        	if(Yii::$app->admin->identity->member_password === '888888'){
        		return Yii::$app->getResponse()->redirect(Url::to("/wifiservice/wifi/resetpassword"));
        	}else{
        		return Yii::$app->getResponse()->redirect(Url::to("/wifiservice/wifi/index"));
        	}
        } else {
            return $this->render('agent_login', [
                'model' => $model,
            	'active'=> $active,
            ]);
        }
    }
   
    

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->admin->logout();
        return Yii::$app->getResponse()->redirect(Url::to("/wifiservice/site/login"));
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending email.');
            }

            return $this->refresh();
        } else {
            return $this->render('contact', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    return $this->goHome();
                }
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            } else {
                Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for email provided.');
            }
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidParamException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password was saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }
    
    public function actionTest()
    {
    	Header("Access-Control-Allow-Origin: * ");
    	Header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
    	echo "About cruise ";
    }
}
