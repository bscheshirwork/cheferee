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
			$scoreGroups = Yii::app()->db->createCommand()
				->selectDistinct('scoreGroup, count(*) count')
				->from(Grid::tableName())
				->where('tour=:prevTour and tourDone=1',[':prevTour'=>$id-1])
				->group('scoreGroup')
				->order('scoreGroup desc, resultElo desc')
				->queryAll();
			//determine the group size in the array
			$indexMask=[];
			$endIndex=-1;
			$fromPrev=0;
			$oddIndex=false;
			foreach ($scoreGroups as $sgKey=>$scoreGroup){
				$startIndex=$endIndex+1;
				$endIndex=$startIndex+$fromPrev+$scoreGroup['count']-1;
				//change "endIndex" of the current and "startIndex" of the next group if an odd number of elements in the group
				if(fmod($endIndex-$startIndex+1,2)){
					//auto winner index
					if($sgKey==count($scoreGroups)-1)
						$oddIndex=$endIndex;
					$endIndex--;
					$fromPrev=1;
				}else
					$fromPrev=0;
				$indexMask[$scoreGroup['scoreGroup']]['startIndex']=$startIndex;
				$indexMask[$scoreGroup['scoreGroup']]['endIndex']=$endIndex;
			}
			
			$criteria=new CDbCriteria;
			$criteria->addCondition('tour=:prevTour and tourDone=1');
			$criteria->with=['rivals'=>['together'=>true,'index'=>'rivalId']];
			$criteria->order='scoreGroup desc, resultElo desc';
			$criteria->params=[':prevTour'=>$id-1];
			$model=Grid::model()->findAll($criteria);
			//slice odd
			if($oddIndex){
				$model2=$model[$oddIndex];
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
				unset($model[$oddIndex]);
			}
			
			$lIndex=0;
			$maxIndex=count($model)-1;
			
			//fill grid
			while(!empty($model)){
				
				$passFlag=false;
				//"left" index
				$lIndex=BscFor::skipEmpty($model, $lIndex, $maxIndex-1);
				//"right" index

				foreach($indexMask as $sgKey => $value)
					if((int)$sgKey <= (int)$model[$lIndex]->scoreGroup){
						$startIndex=max($lIndex,$value['startIndex']);
						$rIndex=BscFor::skipEmpty($model, $value['endIndex'], $startIndex, true);

						//rules
						while ($rIndex>$startIndex) {
							
							$rIndex=BscFor::skipEmpty($model, $rIndex, $startIndex, true);
							if(empty($model[$rIndex]))//bound destination, but all model is empty
								continue 2;//foreach($indexMask as $sgKey => $value)
							
							//strong layer - already playing pair 
							if(isset($model[$rIndex]->rivals[$model[$lIndex]->playerId])){
								$rIndex--;
								continue;//while ($rIndex>$startIndex) {
							}
							
							//soft layer - semicolor 3x 
							if($model[$lIndex]->lastColorCount>2){
								$cIndex=$rIndex;
								while ($cIndex>$startIndex){
									$cIndex=BscFor::skipEmpty($model, $cIndex, $startIndex, true);
									if(empty($model[$cIndex]))//bound destination, but all model is empty
										continue 3;//foreach($indexMask as $sgKey => $value)
									if(isset($model[$cIndex]->rivals[$model[$lIndex]->playerId])){
										$cIndex--;
										continue;//while ($cIndex>$startIndex) {
									}
									if($model[$cIndex]->lastColorCount>2 && $model[$lIndex]->lastColor==$model[$cIndex]->lastColor && !$lastTourFlag){
										$cIndex--;
										continue;//while ($cIndex>$startIndex)
									}
									//color pass
									$passFlag=true;
									$rIndex=$cIndex;
									break 3;//foreach($indexMask as $sgKey => $value)
								}//while ($cIndex>$startIndex){
								
								//color dont pass
								if($lastTourFlag){//this is last tour?
									$passFlag=true;
									//$rIndex=$rIndex;
									break 2;//foreach($indexMask as $sgKey => $value)
								}
								//like a strong layer
								$rIndex--;
								continue;//while ($rIndex>$startIndex) {
							}//if($model[$j]->lastColorCount>2){
							
							//notice layer - semicolor 2x
							if($model[$lIndex]->lastColorCount>1){
								$cIndex=$rIndex;
								$cBound=$cIndex-Yii::app()->params['semicolorNotice'];//delta notice
								while ( $cIndex>$startIndex && $cIndex>$cBound ){
									while(empty($model[$cIndex]) && $cIndex>$startIndex && $cIndex>$cBound ){
										$cIndex--;
										$cBound--;
									}
									if(empty($model[$cIndex])){//bound destination, but all model is empty
										//color dont pass... but its notify -> return to $rIndex
										$passFlag=true;
										break 3;//foreach($indexMask as $sgKey => $value)
									}
									if(isset($model[$cIndex]->rivals[$model[$lIndex]->playerId])){
										$cIndex--;
										$cBound--;
										continue;//while ( $cIndex>$startIndex && $cIndex>$cBound ){
									}
									if($model[$cIndex]->lastColorCount>1 && $model[$lIndex]->lastColor==$model[$cIndex]->lastColor){
										$cIndex--;
										continue;//while ( $cIndex>$startIndex && $cIndex>$cBound ){
									}
									//color pass
									$passFlag=true;
									$rIndex=$cIndex;
									break 3;//foreach($indexMask as $sgKey => $value)
								}//while ( $cIndex>$startIndex && $cIndex>$cBound ){
								
								//color dont pass... but its notify
								$passFlag=true;
								//$rIndex=$rIndex;
								break 2;//foreach($indexMask as $sgKey => $value)
								
							}//if($model[$lIndex]->lastColorCount>1){
							
							//if not any break/continue then rIndex correct
							$passFlag=true;
							//$rIndex=$rIndex;
							break 2;//foreach($indexMask as $sgKey => $value)
							
						}//while ($rIndex>$startIndex) {
						//overfull bound
						
					}//foreach($indexMask as $sgKey => $value)
				
				//$lIndex and $rIndex ready. $passFlag===true;
				if(!$passFlag||$lIndex>=$rIndex||empty($model[$lIndex])||empty($rIndex)){
					//may be last choice or error in rules / mismatch any rules
				}
				
				//select element
				$model1=$model[$lIndex];
				$model2=$model[$rIndex];
				//and unset in list
				unset($model[$lIndex]);
				unset($model[$rIndex]);				
				
				//fill pair
				
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
					throw new CHttpException(500,'Ошибка распределения. Невозможно применить правила распределения.');
				}
				
			}//while(!empty($model)){
			
		}//}else{//another tour
		
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
				$value->resultElo=Grid::calcNewElo($value->startElo,${$mask[$value->color]}->startElo,0.5);//dead heat
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
			
			$model1->resultElo=Grid::calcNewElo($model1->startElo,$model2->startElo,1);//winner
			$model2->resultElo=Grid::calcNewElo($model2->startElo,$model1->startElo,0);//loser

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
		$this->render('index',[
				'data'=>$data,
				'gridIsClear'=>!Grid::getMaxPairId(),
				'maxTour'=>Grid::getApproxMaxTour(),
			]);		
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
