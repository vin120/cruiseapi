<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "vcos_im_group".
 *
 * @property integer $id
 * @property string $group_name
 * @property string $signature
 * @property string $icon
 * @property string $create_time
 * @property string $create_member_id
 */
class ImGroup extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vcos_im_group';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time'], 'safe'],
            [['group_name'], 'string', 'max' => 100],
            [['signature', 'icon'], 'string', 'max' => 255],
            [['create_member_id'], 'string', 'max' => 32],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'group_name' => 'Group Name',
            'signature' => 'Signature',
            'icon' => 'Icon',
            'create_time' => 'Create Time',
            'create_member_id' => 'Create Member ID',
        ];
    }
}
