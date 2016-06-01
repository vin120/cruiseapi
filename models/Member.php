<?php

namespace app\models;

use Yii;
use app\components\MymemberActiveRecord;
/**
 * This is the model class for table "vcos_member".
 *
 * @property integer $member_id
 * @property string $smart_card_number
 * @property string $member_code
 * @property string $member_name
 * @property string $member_email
 * @property string $member_password
 * @property string $cn_name
 * @property string $last_name
 * @property string $first_name
 * @property string $sex
 * @property integer $date_of_birth
 * @property string $birth_place
 * @property string $nationality
 * @property string $country_code
 * @property string $nation_code
 * @property string $mobile_number
 * @property string $fixed_telephone
 * @property integer $email_verification
 * @property integer $mobile_verification
 * @property integer $member_level
 * @property double $member_money
 * @property integer $member_credit
 * @property integer $member_verification
 * @property string $passport_number
 * @property integer $passport_date_issue
 * @property string $passport_place_issue
 * @property string $passport_issue_country_code
 * @property integer $passport_expiry_date
 * @property string $resident_id_card
 * @property string $id_card_issuing_authority
 * @property string $id_card_address
 * @property integer $id_card_start_date
 * @property integer $id_card_expiry_date
 * @property integer $reg_time
 * @property string $reg_ip
 * @property string $create_by
 * @property integer $create_time
 * @property integer $is_online_booking
 * @property string $sign
 */
class Member extends MymemberActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vcos_member';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sex'], 'string'],
            [['date_of_birth', 'email_verification', 'mobile_verification', 'member_level', 'member_credit', 'member_verification', 'passport_date_issue', 'passport_expiry_date', 'id_card_start_date', 'id_card_expiry_date', 'reg_time', 'create_time', 'is_online_booking'], 'integer'],
            [['member_money'], 'number'],
            [['smart_card_number', 'member_name'], 'string', 'max' => 50],
            [['member_code', 'resident_id_card', 'create_by'], 'string', 'max' => 32],
            [['member_email', 'member_password', 'cn_name', 'last_name', 'first_name', 'passport_place_issue', 'id_card_issuing_authority', 'id_card_address', 'reg_ip'], 'string', 'max' => 100],
            [['birth_place'], 'string', 'max' => 250],
            [['nationality'], 'string', 'max' => 150],
            [['country_code'], 'string', 'max' => 16],
            [['nation_code'], 'string', 'max' => 2],
            [['mobile_number', 'fixed_telephone', 'passport_number'], 'string', 'max' => 20],
            [['passport_issue_country_code'], 'string', 'max' => 10],
            [['sign'], 'string', 'max' => 128],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'member_id' => 'Member ID',
            'smart_card_number' => 'Smart Card Number',
            'member_code' => 'Member Code',
            'member_name' => 'Member Name',
            'member_email' => 'Member Email',
            'member_password' => 'Member Password',
            'cn_name' => 'Cn Name',
            'last_name' => 'Last Name',
            'first_name' => 'First Name',
            'sex' => 'Sex',
            'date_of_birth' => 'Date Of Birth',
            'birth_place' => 'Birth Place',
            'nationality' => 'Nationality',
            'country_code' => 'Country Code',
            'nation_code' => 'Nation Code',
            'mobile_number' => 'Mobile Number',
            'fixed_telephone' => 'Fixed Telephone',
            'email_verification' => 'Email Verification',
            'mobile_verification' => 'Mobile Verification',
            'member_level' => 'Member Level',
            'member_money' => 'Member Money',
            'member_credit' => 'Member Credit',
            'member_verification' => 'Member Verification',
            'passport_number' => 'Passport Number',
            'passport_date_issue' => 'Passport Date Issue',
            'passport_place_issue' => 'Passport Place Issue',
            'passport_issue_country_code' => 'Passport Issue Country Code',
            'passport_expiry_date' => 'Passport Expiry Date',
            'resident_id_card' => 'Resident Id Card',
            'id_card_issuing_authority' => 'Id Card Issuing Authority',
            'id_card_address' => 'Id Card Address',
            'id_card_start_date' => 'Id Card Start Date',
            'id_card_expiry_date' => 'Id Card Expiry Date',
            'reg_time' => 'Reg Time',
            'reg_ip' => 'Reg Ip',
            'create_by' => 'Create By',
            'create_time' => 'Create Time',
            'is_online_booking' => 'Is Online Booking',
            'sign' => 'Sign',
        ];
    }
}
