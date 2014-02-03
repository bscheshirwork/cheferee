<?php

/**
 * This is the model class for table "rival".
 *
 * The followings are the available columns in table 'rival':
 * @property integer $playerId
 * @property integer $rivalId
 */
class Rival extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return Yii::app()->params['tablename'.__CLASS__];
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('playerId, rivalId', 'required'),
			array('playerId, rivalId', 'numerical', 'integerOnly'=>true),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'player'=>array(self::HAS_ONE, 'Player', ['id'=>'playerId']),
			'rival'=>array(self::HAS_ONE, 'Player', ['id'=>'rivalId']),
		);
	}

	/**
	 * append player and rival to rival item (one of pair)
	 * and save record
	 * @param int $playerId active player id.
	 * @param int $rivalId active player opponent id.
	 * @return bool result of the save action
	 */
	public function formAndSave($playerId,$rivalId)
	{
		$this->playerId=$playerId;
		$this->rivalId=$rivalId;
		return $this->save()||Yii::log(CHtml::errorSummary($this).'!BSC! AR save() return false: '.__FILE__.'['.__LINE__.']', 'error', 'protected.models.Rival');
	}
	
	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Rival the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
