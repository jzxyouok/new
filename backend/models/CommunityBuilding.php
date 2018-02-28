<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "community_building".
 *
 * @property integer $building_id
 * @property integer $community_id
 * @property string $building_name
 * @property string $building_parent
 *
 * @property UserInvoice[] $userInvoices
 */
class CommunityBuilding extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'community_building';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['community_id', 'building_name'], 'required'],
            [['community_id'], 'integer'],
            [['building_name', 'building_parent'], 'string', 'max' => 64],
            [['building_name', 'community_id'], 'unique', 'targetAttribute' => ['building_name', 'community_id'], 'message' => 'The combination of Community ID and Building Name has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'building_id' => '编号',
            'community_id' => '小区',
            'building_name' => '楼宇',
            'building_parent' => '父级',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserInvoices()
    {
        return $this->hasMany(UserInvoice::className(), ['building_id' => 'building_id']);
    }
	
	public function getC()
    {
        return $this->hasOne(CommunityBasic::className(), ['community_id' => 'community_id']);
    }
}
