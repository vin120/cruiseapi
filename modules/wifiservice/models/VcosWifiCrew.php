<?php

namespace app\modules\wifiservice\models;

use Yii;

/**
 * This is the model class for table "vcos_wifi_crew".
 *
 * @property integer $crew_id
 * @property string $smart_card_number
 * @property string $crew_code
 * @property string $cn_name
 * @property string $passport_number
 * @property string $crew_password
 * @property string $crew_email
 * @property string $mobile_number
 * @property double $money
 * @property integer $crew_credit
 * @property string $sign
 * @property integer $overdraft_limit
 * @property integer $curr_overdraft_amount
 */
class VcosWifiCrew extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vcos_wifi_crew';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['money'], 'number'],
            [['crew_credit', 'overdraft_limit', 'curr_overdraft_amount'], 'integer'],
            [['smart_card_number'], 'string', 'max' => 50],
            [['crew_code'], 'string', 'max' => 32],
            [['cn_name', 'crew_password', 'crew_email'], 'string', 'max' => 100],
            [['passport_number', 'mobile_number'], 'string', 'max' => 20],
            [['sign'], 'string', 'max' => 128],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'crew_id' => Yii::t('app', 'Crew ID'),
            'smart_card_number' => Yii::t('app', 'Smart Card Number'),
            'crew_code' => Yii::t('app', 'Crew Code'),
            'cn_name' => Yii::t('app', 'Cn Name'),
            'passport_number' => Yii::t('app', 'Passport Number'),
            'crew_password' => Yii::t('app', 'Crew Password'),
            'crew_email' => Yii::t('app', 'Crew Email'),
            'mobile_number' => Yii::t('app', 'Mobile Number'),
            'money' => Yii::t('app', 'Money'),
            'crew_credit' => Yii::t('app', 'Crew Credit'),
            'sign' => Yii::t('app', 'Sign'),
            'overdraft_limit' => Yii::t('app', 'Overdraft Limit'),
            'curr_overdraft_amount' => Yii::t('app', 'Curr Overdraft Amount'),
        ];
    }
}
