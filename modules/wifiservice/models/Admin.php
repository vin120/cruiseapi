<?php
namespace app\modules\wifiservice\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
// use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use app\components\MymemberActiveRecord;

class Admin extends MymemberActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;
    public $authKey;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%vcos_member_crew}}';
    }
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }
    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return static::findOne(['member_code' => $id]);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
// 		if((substr($username,0,3) == 'TS@') || (substr($username, 0,3) == 'ts@') || (substr($username, 0,3) == 'TS_') || (substr($username, 0,3) == 'ts_') )
// 		{
// 			//如果存在前缀,则是船员
// 			return static::findOne(['passport_number'=>$username]);
// 		}else{
// 			return static::findOne(['passport_number' => $username]);
// 		}
		return static::findOne(['passport_number' => $username]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return boolean
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
    	return $this->member_code;
//         return $this->getPrimaryKey();
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
//        return $this->auth_key;
    }


    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
//        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
//         return true;
//        return Yii::$app->security->validatePassword($password, $this->travel_agent_password);
//     	  return Yii::$app->getSecurity()->validatePassword($password, $this->password);
    	
		if('888888' == $this->member_password){
			//默认密码
			if ($password == substr($this->passport_number,-6)){
				return true;
			}else{
				return false;
			}
		}else{
			if(md5($password) == $this->member_password){
				return true;
			}else{
				return false;
			}
		}
    }
    
    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }
}

