<?php

class GridController extends Controller
{
	/**
	 * @var string the default layout for the views. 
	 */
	public $layout='//layouts/column1';

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			'accessControl', // perform access control 
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform actions
				'actions'=>array('index','view','tour','compose','judge'),
				'users'=>array('*'),
			),
			array('allow', // allow admin user to perform delete operation
				'actions'=>array('removeafter'),
				'users'=>array('admin'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	/**
	 * Create a distribution of players for the tour.
	 * distribution rules see on http://ru.wikipedia.org/wiki/%D0%A8%D0%B2%D0%B5%D0%B9%D1%86%D0%B0%D1%80%D1%81%D0%BA%D0%B0%D1%8F_%D1%81%D0%B8%D1%81%D1%82%D0%B5%D0%BC%D0%B0
	 * The players will be distributed for pair
	 * note: previous tour should be finished
	 * @param integer $id the tour of the models
	 * "$id" similar to urlManager
	 */
	public function actionCompose($id)
	{
		//check max tour with depend accurecy and last pairId
		$lastTourFlag = $id >= Grid::getApproxMaxTour();
		$pairId = Grid::getMaxPairId();
		//if start distribution - get all of player
		if($id==1){//first tour
			if(empty($pairId)){// check for empty
				$criteria=new CDbCriteria;
				$criteria->condition='`leave`=0';
				$criteria->order='elo desc, name';
				$model=Player::model()->findAll($criteria);
				//if an odd number of players - that the weakest goes to the next round on a technical victory
				if(!empty($model)){
					if(fmod(count($model),2)){
						$player1=$model[count($model)-1];
						$model1=new Grid;
						$model1->tour=1;
						$model1->pairId=1;
						$model1->appendPlayers($player1);
						$model1->resultScore=Yii::app()->params['scoreWining'];
						$model1->scoreGroup=Yii::app()->params['scoreWining'];
						$model1->resultElo=$model1->startElo;
						$model1->tourDone=1;
						$model1->save()||Yii::log(CHtml::errorSummary($model1).'!BSC! AR save() return false: '.__FILE__.'['.__LINE__.']', 'error', 'protected.controllers.GridController');
						unset($model[count($model)-1]);
					}
					for($i=0,$maxIndex=count($model)-1,$semiIndex=count($model)/2-1,$j=$maxIndex; $i<=$semiIndex; $i++,$j=$maxIndex-$i){
						$transaction=Yii::app()->db->beginTransaction();
						try{
							$player1=$model[$i];
							$player2=$model[$j];
							$model1=new Grid;
							$model1->tour=1;
							$model1->scoreGroup=0;
							$model1->pairId=$i+1;
							$model2= clone $model1;
							$model1->appendPlayers($player1,$player2);
							$model2->appendPlayers($player2,$player1);
							$model1->color=$model1->lastColor='black';
							$model2->color=$model2->lastColor='white';
							$model1->lastColorCount=1;
							$model2->lastColorCount=1;
							$model1->save()||Yii::log(CHtml::errorSummary($model1).'!BSC! AR save() return false: '.__FILE__.'['.__LINE__.']', 'error', 'protected.controllers.GridController');
							$model2->save()||Yii::log(CHtml::errorSummary($model2).'!BSC! AR save() return false: '.__FILE__.'['.__LINE__.']', 'error', 'protected.controllers.GridController');
							if($model1->hasErrors()||$model2->hasErrors())
								$transaction->rollback();
							else 
								$transaction->commit();
						}catch(Exception $e){
							$transaction->rollback();
							throw $e;
						}
					}
				}
			}
		}else{//another tour
			//use only grid separate for scoreGroup
			$scoreGroups = Yii::app()->db->createCommand()
				->selectDistinct('scoreGroup')
				->from(Grid::tableName())
				->where('tour=:tour and tourDone=1',[':tour'=>$id-1])
				->order('scoreGroup desc, resultElo desc')
				->queryColumn();
			foreach ($scoreGroups as $scoreGroup){
				$criteria=new CDbCriteria;
				$criteria->addCondition('tour=:prevTour and tourDone=1 and scoreGroup=:scoreGroup');
				$criteria->addCondition('t.playerId NOT IN (SELECT playerId FROM '.Grid::tableName().' WHERE tour=:tour)');
				$criteria->with=['rivals'=>['together'=>true,'index'=>'rivalId']];
				$criteria->params=[':tour'=>$id,':prevTour'=>$id-1,':scoreGroup'=>$scoreGroup];
				$model=Grid::model()->findAll($criteria);
				//if an odd number of record - that the weakest goes to the next scoreGroup
				while(!empty($model)){
					//reset $model
					$model=array_values($model);
					//slice odd
					if(fmod(count($model),2)){
						$model2=$model[count($model)-1];
						if($scoreGroup!=$scoreGroups[count($scoreGroups)-1]){
							$model2->scoreGroup-=Yii::app()->params['scoreDeadHeat'];
							$model2->save()||Yii::log(CHtml::errorSummary($model2).'!BSC! AR save() return false: '.__FILE__.'['.__LINE__.']', 'error', 'protected.controllers.GridController');
						}else{//auto winner +1 & pull in next tour
							$model1=new Grid;
							//modify
							$model1->tour=$model2->tour+1;
							$model1->tourDone=1;
							$model1->pairId=++$pairId;
							$model1->resultScore=$model2->resultScore+Yii::app()->params['scoreWining'];
							//$model1->scoreGroup=$model2->scoreGroup+Yii::app()->params['scoreWining'];
							$model1->scoreGroup=Yii::app()->params['scoreWining'];//if only 3 group
							$model1->resultElo=$model2->resultElo;
							//copy
							$model1->appendGrid($model2);
							$model1->save()||Yii::log(CHtml::errorSummary($model1).'!BSC! AR save() return false: '.__FILE__.'['.__LINE__.']', 'error', 'protected.controllers.GridController');
						}
						unset($model[count($model)-1]);
					}//if(fmod(count($model),2))
					
					//get the 2 keys
					for($i=0,$maxIndex=count($model)-1,$semiIndex=count($model)/2-1,$j=$maxIndex; $i<=$semiIndex; $i++,$j=$maxIndex-$i){
						//subcounter - if wrong criteria find next in pyramid
						//i      j
						// i+1   j
						//  i+2  j
						//   i+3 j
						$k=$i;//asc
						$passFlag=false;
						while ($k<$j) {
							//strong layer - already playing pair 
							if(isset($model[$j]->rivals[$model[$k]->playerId])){
								$k++;
								//if last pair already playing
								if(count($model)==2){
									unset($model[$j]);
									unset($model[$k]);
									break 3;//while(!empty($model))
								}
								continue;//while ($k<$j)
							}
							//soft layer - semicolor 3x 
							if($model[$j]->lastColorCount>2){
								$l=$k;
								while ($l<$j){
									if(isset($model[$j]->rivals[$model[$l]->playerId])){
										$l++;
										continue;//while ($l<$i)
									}
									if($model[$l]->lastColor==$model[$i]->lastColor){
										if($model[$l]->lastColorCount>2){
											$l++;
											continue;//while ($l<$j)
										}
									}
									//color pass
									$k=$l;
									$passFlag=true;
									break 2;//while ($k<$j)
								}
								//color dont pass
								if($lastTourFlag){//this is last tour?
									$passFlag=true;
									break;//while ($k<$j)
								}
								//reset
								$k++;
								continue;//while ($k<$j)
								
							//notice layer - semicolor 2x
							}elseif($model[$j]->lastColorCount>1){
								$l=$k;
								$m=$k+3;//delta notice
								while ( $l<$j && $l<$m ){
									if(isset($model[$j]->rivals[$model[$l]->playerId])){
										$l++;
										$m++;
										continue;//while ( $l<$j && $l<$m )
									}
									if($model[$l]->lastColor==$model[$i]->lastColor){
										if($model[$l]->lastColorCount>1){
											$l++;
											continue;//while ( $l<$j && $l<$m )
										}
									}
									//color pass
									$k=$l;
									$passFlag=true;
									break 2;//while ($k<$j)									
								}
								
								//color dont pass... but its notify
								//$k=$k
								$passFlag=true;
								break;//while ($k<$j)
								
							}
							//if not eny break
							//$k=$k
							$passFlag=true;
							break;//while ($k<$j)
							
						}//while($k<$j)
						
						//if $model[$j] dont have pair mark it as problem
						//select element
						$model1=$model[$k];
						$model2=$model[$j];
						//and unset in list
						unset($model[$k]);
						unset($model[$j]);
						//and reset $model at start
						if(!$passFlag){
							if($scoreGroup!=$scoreGroups[count($scoreGroups)-1]){
								$model2->scoreGroup-=Yii::app()->params['scoreDeadHeat'];
								$model2->save()||Yii::log(CHtml::errorSummary($model2).'!BSC! AR save() return false: '.__FILE__.'['.__LINE__.']', 'error', 'protected.controllers.GridController');
							}else{
								//problem in the last scoreGroup. Is so bad.
								//so... 
								//may be...
								//auto winner +1 & pull in next tour
								$model1=new Grid;
								//modify
								$model1->tour=$model2->tour+1;
								$model1->tourDone=1;
								$model1->pairId=++$pairId;
								$model1->resultScore=$model2->resultScore+Yii::app()->params['scoreWining'];
								//$model1->scoreGroup=$model2->scoreGroup+Yii::app()->params['scoreWining'];
								$model1->scoreGroup=Yii::app()->params['scoreWining'];//if only 3 group
								$model1->resultElo=$model2->startElo;
								//copy
								$model1->appendGrid($model2);
								$model1->save()||Yii::log(CHtml::errorSummary($model1).'!BSC! AR save() return false: '.__FILE__.'['.__LINE__.']', 'error', 'protected.controllers.GridController');								
							}
							$model=array_values($model);//reset array
							continue 2;//while(!empty($model))
						}
							
						$transaction=Yii::app()->db->beginTransaction();
						try{

							//fill new pair
							$model3=new Grid;//from $model1
							$model4=new Grid;//from $model2
							//$model1->id;
							$model3->pairId=$model4->pairId=++$pairId;;

							$model3->appendGrids($model1,$model2);
							$model4->appendGrids($model2,$model1);

							$model3->scoreGroup=$model1->scoreGroup;
							$model4->scoreGroup=$model2->scoreGroup;

							$model3->startScore=$model1->resultScore;
							$model4->startScore=$model2->resultScore;
							
							$flag=$model1->lastColorCount>=$model2->lastColorCount;
							$model3->appendColors($model1,$model2,$flag);
							$model4->appendColors($model2,$model1,!$flag);
							
							$model3->tour=$model4->tour=$id;
							//$model1->scoreGroup;

							//$model1->tourDone;
							//$model1->resultScore;
							//$model1->resultElo;
							$model3->save()||Yii::log(CHtml::errorSummary($model3).'!BSC! AR save() return false: '.__FILE__.'['.__LINE__.']', 'error', 'protected.controllers.GridController');
							$model4->save()||Yii::log(CHtml::errorSummary($model4).'!BSC! AR save() return false: '.__FILE__.'['.__LINE__.']', 'error', 'protected.controllers.GridController');
							if($model3->hasErrors()||$model4->hasErrors())
								$transaction->rollback();
							else 
								$transaction->commit();
						}catch(Exception $e){
							$transaction->rollback();
							throw $e;
						}
						//reset
						continue 2;//while(!empty($model))
					}//for($i=0,$maxIndex=count($model)-1,...
				}//while(!empty($model))
			}//foreach ($scoreGroups as $scoreGroup)
		}
		$this->actionTour($id);
	}

	/**
	 * judge match of selectes pair.
	 * and calculate ELO
	 * @param integer $id after this tour delete all Grid data
	 * @param string 'black'|'white'|'' $winner the winner
	 */
	public function actionJudge($id,$winner=null)
	{
		$modelBlack=$modelWhite=null;
		$mask=['black'=>'modelWhite','white'=>'modelBlack'];
		$model=$this->loadPairModel($id);
		foreach ($model as $value){
			${'model'.ucfirst($value->color)}=$value;
			$value->tourDone=1;
		}
		
		if(!$winner){
			foreach ($model as $value){
				$value->resultScore=$value->startScore+Yii::app()->params['scoreDeadHeat'];
				//$value->scoreGroup+=Yii::app()->params['scoreDeadHeat'];
				$value->scoreGroup=Yii::app()->params['scoreDeadHeat'];//if only 3 group
				$value->resultElo=$value->startElo;
				$value->resultElo=Grid::getNewElo($value->startElo,${$mask[$value->color]}->startElo,0.5);//dead heat
				$value->save()||Yii::log(CHtml::errorSummary($value).'!BSC! AR save() return false: '.__FILE__.'['.__LINE__.']', 'error', 'protected.controllers.GridController');
			}
		}elseif($mask[$winner]){
			
			$model1=${'model'.ucfirst($winner)};
			$model1->resultScore=$model1->startScore+Yii::app()->params['scoreWining'];
			//$model1->scoreGroup+=Yii::app()->params['scoreWining'];
			$model1->scoreGroup=Yii::app()->params['scoreWining'];
			$model2=${$mask[$winner]};
			$model2->resultScore=$model2->startScore+Yii::app()->params['scoreLosing'];
			//$model2->scoreGroup+=Yii::app()->params['scoreLosing'];
			$model2->scoreGroup=Yii::app()->params['scoreLosing'];
			
			$model1->resultElo=Grid::getNewElo($model1->startElo,$model2->startElo,1);//winner
			$model2->resultElo=Grid::getNewElo($model2->startElo,$model1->startElo,0);//loser

			$model1->save()||Yii::log(CHtml::errorSummary($model1).'!BSC! AR save() return false: '.__FILE__.'['.__LINE__.']', 'error', 'protected.controllers.GridController');
			$model2->save()||Yii::log(CHtml::errorSummary($model2).'!BSC! AR save() return false: '.__FILE__.'['.__LINE__.']', 'error', 'protected.controllers.GridController');
		}
		$this->actionIndex();
	}

	/**
	 * Deletes a particular model.
	 * delete all after select tour (0 - delete all)
	 * If deletion is successful, the browser will be redirected to the 'grid' page.
	 * @param integer $id after this tour delete all Grid data
	 */
	public function actionRemoveAfter($id)
	{
		if(Yii::app()->request->isPostRequest){
			// we only allow deletion via POST request
			$command = Yii::app()->db->createCommand();
			$command->delete(Grid::tableName(), 'tour > :tour', [':tour'=>$id]);
			//note: removal "rival" in the trigger database
			$this->redirect(['index']);	
		}else
			throw new CHttpException(400,'Неправельный запрос. Пожалуйста, не надо его повторять, от этого он не станет правильным.');
	}
	
	/**
	 * Displays a particular model.
	 * @param integer $id the pairId of the models to be displayed
	 * "$id" similar to urlManager
	 */
	public function actionView($id)
	{
		$modelBlack=$modelWhite=$value=null;
		$model=$this->loadPairModel($id);
		foreach ($model as $value) 
			${'model'.ucfirst($value->color)}=$value;
		$this->render('view',array(
			'model'=>$value,//last valid model of pair
			'modelBlack'=>$modelBlack,
			'modelWhite'=>$modelWhite,
		));
	}

	/**
	 * Displays a particular model contains pair of tour.
	 * @param integer $id the tour of the models to be displayed
	 * "$id" similar to urlManager
	 */
	public function actionTour($id)
	{
		$model=Grid::model()->with('player')->findAll('tour=?',[$id]);
		$data=$this->formPairModelData($model);
		$this->render('index',['data'=>$data,'gridIsClear'=>!Grid::getMaxPairId()]);		
	}
	
	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
		$model=Grid::model()->with('player')->findAll();
		$data=$this->formPairModelData($model);
		$this->render('index',[
				'data'=>$data,
				'gridIsClear'=>!Grid::getMaxPairId(),
				'maxTour'=>Grid::getApproxMaxTour(),
				'resultGrid'=>Grid::getResultTable(),
			]);
	}
	
	/**
	 * Returns the array in specific format
	 * [
	 *  tour
	 *     |
	 *     |--pairId
	 *             |
	 *             |--"modelBlack"
	 *             |--"modelWhite"
	 * ]
	 * @param Grid model 
	 * @return Array $data  array in specific format
	 */
	public function formPairModelData($model)
	{
		$data=[];
		foreach ($model as $value){ 
			if(!isset($data[$value->tour][$value->pairId]))//init;
				$data[$value->tour][$value->pairId]['modelBlack']=$data[$value->tour][$value->pairId]['modelWhite']=null;
			$data[$value->tour][$value->pairId]['model']=$data[$value->tour][$value->pairId]['model'.ucfirst($value->color)]=$value;
		}
		return $data;
	}
	
	/**
	 * Returns the 2 data model based on the pair key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 * @return Grid $model search result
	 */
	public function loadPairModel($pairId)
	{
		$model=Grid::model()->with('player')->findAll('pairId=?',[$pairId]);
		if(empty($model))//no search result
			throw new CHttpException(404,'Запрошеная страница не существует.');
		return $model;
	}

}
