<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "vcos_im_invitation".
 *
 * @property integer $id
 * @property string $send_from
 * @property string $send_to
 * @property string $content
 * @property integer $type
 * @property integer $result
 * @property string $invitation_time
 */
class ImInvitation extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'vcos_im_invitation';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'result'], 'integer'],
            [['invitation_time'], 'safe'],
            [['send_from', 'send_to'], 'string', 'max' => 32],
            [['content'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'send_from' => 'Send From',
            'send_to' => 'Send To',
            'content' => 'Content',
            'type' => 'Type',
            'result' => 'Result',
            'invitation_time' => 'Invitation Time',
        ];
    }
}
