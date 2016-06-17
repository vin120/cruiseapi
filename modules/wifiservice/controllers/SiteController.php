<?php

namespace app\modules\wifiservice\controllers;

use Yii;
use app\modules\wifiservice\models\LoginForm;
use yii\base\InvalidParamException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\helpers\Url;
use yii\filters\VerbFilter;


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
        	if(empty(Yii::$app->admin->identity->sign)){
        	
        		$type = Yii::$app->admin->identity->member_type;
        		$member_code = Yii::$app->admin->identity->member_code;
        		$passport_number = Yii::$app->admin->identity->passport_number;
        		$member_password = Yii::$app->admin->identity->member_password;
        	
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
//             return Yii::$app->getResponse()->redirect(Url::to("/wifiservice/wifi/index"));
        	if(Yii::$app->admin->identity->member_password === '888888'){
        		return Yii::$app->getResponse()->redirect(Url::to("/wifiservice/wifi/resetpassword"));
        	}else{
        		return Yii::$app->getResponse()->redirect(Url::to("/wifiservice/wifi/index"));
        	}
        }

        $model = new LoginForm();

        if ($model->load(Yii::$app->request->post()) && $model->login()) {
        	if(empty(Yii::$app->admin->identity->sign)){
        	
        		$type = Yii::$app->admin->identity->member_type;
        		$member_code = Yii::$app->admin->identity->member_code;
        		$passport_number = Yii::$app->admin->identity->passport_number;
        		$member_password = Yii::$app->admin->identity->member_password;
        	
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
        	if(Yii::$app->admin->identity->member_password === '888888'){
        		return Yii::$app->getResponse()->redirect(Url::to("/wifiservice/wifi/resetpassword"));
        	}else{
        		return Yii::$app->getResponse()->redirect(Url::to("/wifiservice/wifi/index"));
        	}
//         	return Yii::$app->getResponse()->redirect(Url::to("/wifiservice/wifi/index"));
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
