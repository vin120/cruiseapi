<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "vcos_im_member".
 *
 * @property integer $id
 * @property string $member_id
 * @property string $name
 * @property string $signature
 * @property string $icon
 * @property string $update_time
 */
class ImMember extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vcos_im_member';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id'], 'string', 'max' => 32],
            [['name'], 'string', 'max' => 100],
            [['signature', 'icon'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => 'Member ID',
            'name' => 'Name',
            'signature' => 'Signature',
            'icon' => 'Icon',
        	'update_time'=>'UpdateTime',
        ];
    }
}
