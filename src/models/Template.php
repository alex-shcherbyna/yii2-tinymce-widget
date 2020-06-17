<?php

namespace ashch\tinymce\models;

use Yii;
use yii\behaviors\SluggableBehavior;
use ashch\sitecore\components\ActiveRecord;

/**
 * This is the model class for table "{{%template}}".
 *
 * @property int $id ID
 * @property string $slug Slug
 * @property int $status Status
 * @property int $sorting Sorting
 * @property string $name Name
 * @property string $content Content
 * @property int $created_by Created by:
 * @property int $updated_by Updated by:
 * @property int $created_at Created at:
 * @property int $updated_at Updated at:
 */
class Template extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%template}}';
    }

    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            [
                'class' => SluggableBehavior::className(),
                'attribute' => 'name',
                'slugAttribute' => 'slug',
                'immutable' => true,
                'ensureUnique' => true,
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status', 'sorting', 'created_by', 'updated_by', 'created_at', 'updated_at'], 'integer'],
            [['content'], 'string'],
            [['slug'], 'string', 'max' => 99],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'slug' => Yii::t('app', 'Slug'),
            'status' => Yii::t('app', 'Status'),
            'sorting' => Yii::t('app', 'Sorting'),
            'name' => Yii::t('app', 'Name'),
            'image' => Yii::t('app', 'Image'),
            'description' => Yii::t('app', 'Description'),
            'content' => Yii::t('app', 'Content'),
            'created_by' => Yii::t('app', 'Created by:'),
            'updated_by' => Yii::t('app', 'Updated by:'),
            'created_at' => Yii::t('app', 'Created at:'),
            'updated_at' => Yii::t('app', 'Updated at:'),
        ];
    }

    public static function getTinyMCETemplates($params = [])
    {
        return self::find()->select(
                        [
                            'title' => 'name',
                            'description' => 'slug',
                            'content' => 'content'
                        ]
                )->where(['status' => 1])->asArray()->all();
    }

}
