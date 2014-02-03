<?php

/**
 * This is the model class for table "grid".
 *
 * The followings are the available columns in table 'grid':
 * @property integer $id
 * @property integer $pairId
 * @property integer $playerId
 * @property integer $startElo
 * @property integer $startScore
 * @property string $color
 * @property string $lastColor
 * @property integer $lastColorCount
 * @property integer $tour
 * @property integer $scoreGroup
 * @property integer $rivalId
 * @property integer $rivalElo
 * @property integer $tourDone
 * @property integer $resultScore
 * @property integer $resultElo
 */
class Grid extends CActiveRecord
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
			//array('pairId, playerId, startElo, startScore, color, lastColor, lastColorCount, tour, scoreGroup, rivalId, rivalElo, tourDone, resultScore, resultElo', 'required'),
			array('pairId, playerId, startElo, tour, scoreGroup', 'required'),
			array('pairId, playerId, startElo, startScore, lastColorCount, tour, scoreGroup, rivalId, rivalElo, tourDone, resultScore, resultElo', 'numerical', 'integerOnly'=>true),
			array('color, lastColor', 'length', 'max'=>5),
			array('color, lastColor', 'in', 'range'=>['black','white']),
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
			'rivals'=>array(self::HAS_MANY, 'Rival', ['playerId'=>'playerId'])
		);
	}
	
	/**
	 * append player and rival to grid item (one of pair)
	 * @param Player $player active player.
	 * @param Player $rival active player opponent.
	 * @return $this exemplar of the Grid
	 */
	public function appendPlayers(Player $player, Player $rival = NULL)
	{
		$this->playerId=$player->id;
		$this->startElo=$player->elo;
		if($rival){
			$this->rivalId=$rival->id;
			$this->rivalElo=$rival->elo;
			$rival=new Rival;
			$rival->formAndSave($this->playerId,$this->rivalId);
		}
		return $this;
	}
	
	/**
	 * append player grid and rival grid to grid item (one of pair)
	 * @param Grid $playerGrid last tour player.
	 * @param Grid $rivalGrid active player opponent, last tour player.
	 * @return $this exemplar of the Grid
	 */
	public function appendGrids(Grid $playerGrid, Grid $rivalGrid = NULL)
	{
		$this->playerId=$playerGrid->playerId;
		$this->startElo=$playerGrid->resultElo;
		if($rivalGrid){
			$this->rivalId=$rivalGrid->playerId;
			$this->rivalElo=$rivalGrid->resultElo;
			$rival=new Rival;
			$rival->formAndSave($this->playerId,$this->rivalId);
		}
		return $this;
	}
	
	/**
	 * append player grid and to grid item (copy field)
	 * @param Grid $playerGrid last tour player.
	 * @return $this exemplar of the Grid
	 */
	public function appendGrid(Grid $playerGrid)
	{
		$this->rivals=$playerGrid->rivals;//copy link
		$this->playerId=$playerGrid->playerId;
		$this->startElo=$playerGrid->startElo;
		$this->startScore=$playerGrid->startScore;
		$this->lastColor=$playerGrid->lastColor;
		$this->lastColorCount=$playerGrid->lastColorCount;
		return $this;
	}
	
	/**
	 * append player grid Color and to grid item Color 
	 * @param Grid $playerGrid last tour player.
	 * @param Grid $rivalGrid active player opponent, last tour player.
	 * @param bool $isMainColor get the opposite color in $playerGrid (if true) / $rivalGrid (if false).
	 * @return $this exemplar of the Grid
	 */
	public function appendColors(Grid $playerGrid, Grid $rivalGrid = NULL,$isMainColor=true)
	{
		if ($isMainColor){
			if($playerGrid->lastColor=='white')
				$this->color='black';
			else
				$this->color='white';
		}else{
			if($rivalGrid->lastColor=='white')
				$this->color='white';
			else
				$this->color='black';
		}
		
		$this->lastColor=$this->color;
		
		if($this->color==$playerGrid->color)
			$this->lastColorCount=$playerGrid->lastColorCount+1;
		else
			$this->lastColorCount=1;

		return $this;
	}
	
	/**
	 * get the result grid table
	 * @return CArrayDataProvider $dataProvider specifity array
	 * [playerName,scoreTour1,scoreTour2,..]
	 */
	public static function getResultTable()
	{
		$items=[];
		$modelPlayer=Player::model()->findAll();
		foreach ($modelPlayer as $model)
			$items[$model->id]=['id'=>$model->id,'playerName'=>$model->name];
		$dataReader = Yii::app()->db->createCommand()
			->select('playerId,tour,ROUND(resultScore/10,1),resultElo')
			->from(Grid::tableName())
			->order('tour')
			->query();
		while(($row=$dataReader->read())!==false){
			$items[$id=array_shift($row)]['scoreTour'.($tour=array_shift($row))]=array_shift($row);
			$items[$id]['eloTour'.$tour]=array_shift($row);
		}
		$dataProvider=new CArrayDataProvider($items);
		return $dataProvider;
	}
	
	/**
	 * get the max(pairId) 
	 * @return int max of pairId
	 */
	public static function getMaxPairId()
	{
		$pairId = Yii::app()->db->createCommand()
			->select('max(pairId)')
			->from(Grid::tableName())
			->queryScalar();
		return $pairId;
	}
	
	/**
	 * get the count of tour, depend of count player
	 * Yii params accuracyCount set the accuracity
	 * @return int max tour
	 */
	public static function getApproxMaxTour()
	{
		$playersCount = Yii::app()->db->createCommand()
			->select('count(*)')
			->from(Player::tableName())
			->queryScalar();
		$tourCount=ceil(log($playersCount,2)) + ceil(log(Yii::app()->params['accuracyCount'],2));
		return $tourCount;
	}
	
	/**
	 * get the new ELO rating
	 * @param int player ELO rating
	 * @param int rival ELO rating
	 * @param int score - result of match
	 * @return int $resultElo new ELO
	 */
	public static function getNewElo($playerElo,$rivalElo,$score)
	{
		//k is [10|15|30] $score is [1|0.5|0]
		$k = ( $playerElo >= 2400 ? 10 : $playerElo >= 1000 ? 15 : 30 );//first 30 mach? so close 1000
		$resultElo = round( $playerElo + $k * ( $score - 1 / ( 1 + pow( 10, ( $rivalElo - $playerElo ) / 400 ) ) ) );
		return $resultElo;
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Grid the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
}
