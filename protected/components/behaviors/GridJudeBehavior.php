<?php
/*
 * behavior contains in itself 
 * logic of distribution of the match 
 * and the match refereeing
 * 
 */
class GridJudeBehavior extends CBehavior
{

	//public $accuracyCount;
	public $scoreWining;
	public $scoreDeadHeat;
	public $scoreLosing;
	public $maxSemicolorPass;
	public $minSemicolorCheck;

	/**
	 * Create a distribution of players for the tour.
	 * distribution rules see on http://ru.wikipedia.org/wiki/%D0%A8%D0%B2%D0%B5%D0%B9%D1%86%D0%B0%D1%80%D1%81%D0%BA%D0%B0%D1%8F_%D1%81%D0%B8%D1%81%D1%82%D0%B5%D0%BC%D0%B0
	 * and http://www.shashki.com/Article2424.html
	 * result rules:
	 * For each participant, as busting pointers groups selected possible contenders from the rules of the draw: 
	 * Pair played once; 
	 * Alternate colors desired, except for the last round. 
	 * In case of failure to complete according to the rules of the toss choose 
	 * to previous / ing participant / s next possible busting plan / If this does not work - 
	 * usually weaken the alternating colors. The result of the draw is the first option that 
	 * satisfies the conditions for all participants (from first to last, inclusive).
	 * 
	 * Для каждого участника по мере перебора очковых групп подбираются возможные соперники
	 * исходя из правил системы жеребьёвки: 
	 *   пара играет один раз;
	 *   чередование цветов желательно, кроме последнего тура.
	 * В случае невозможности завершить по данным правилам жеребьёвку
	 * выбрать для предыдущего/щих участника/ов следующий возможный план перебора
	 * Если и это не помогает - ослабить правило чередования цветов.
	 * Результатом жеребьевки является первый вариант, удовлетворяющий 
	 * условиям по всем участникам (от первого до последнего включительно).
	 * 
	 * The players will be distributed for pair
	 * note: previous tour should be finished
	 * @param integer $tour the tour of the models
	 */
	public function Compose($tour)
	{
		//check max tour with depend accurecy and last pairId
		$lastTourFlag = $tour >= Grid::getApproxMaxTour();
		$pairId = Grid::getMaxPairId();
		//if start distribution - get all of player
		if($tour==1){//first tour
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
						$model1->resultScore=$this->scoreWining;
						$model1->scoreGroup=$this->scoreWining;
						$model1->resultElo=$model1->startElo;
						$model1->tourDone=1;
						$model1->save()||Yii::log(CHtml::errorSummary($model1).'!BSC! AR save() return false: '.__FILE__.'['.__LINE__.']', 'error', 'protected.components.behaviors.GridJudeBehavior');
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
							$model1->save()||Yii::log(CHtml::errorSummary($model1).'!BSC! AR save() return false: '.__FILE__.'['.__LINE__.']', 'error', 'protected.components.behaviors.GridJudeBehavior');
							$model2->save()||Yii::log(CHtml::errorSummary($model2).'!BSC! AR save() return false: '.__FILE__.'['.__LINE__.']', 'error', 'protected.components.behaviors.GridJudeBehavior');
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
			/*
			 * contains 3 logik part:
			 * 1. Depend scoreGroup bound array
			 * 2. Check compose variants
			 * 3. Generate another tour on success compose
			 */
			/*
			 * 1. Depend scoreGroup bound array
			 */
			$scoreGroups = Yii::app()->db->createCommand()
				->selectDistinct('scoreGroup, count(*) count')
				->from(Grid::tableName())
				->where('tour=:prevTour and tourDone=1',[':prevTour'=>$tour-1])
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
			foreach ($indexMask as $imKey => $imValue) 
				if($imValue['endIndex']<$imValue['startIndex'])
					unset($indexMask[$imKey]);
			
			/*
			 * 2. Check compose variants
			 */			
			$criteria=new CDbCriteria;
			$criteria->addCondition('tour=:prevTour and tourDone=1');
			$criteria->with=['rivals'=>['together'=>true,'index'=>'rivalId']];
			$criteria->order='scoreGroup desc, resultElo desc';
			$criteria->params=[':prevTour'=>$tour-1];
			$model=Grid::model()->findAll($criteria);
			
			$modelMaxIndex=count($model)-1;
			if($oddIndex)
				$modelMaxIndex--;
			$startMaxPCC=$lastTourFlag?$this->maxSemicolorPass:$this->minSemicolorCheck;
			
			$gridData=[];
			$usingIndexList=[];
			$index=0;
			while($index>=0 && $index<$modelMaxIndex){
				$gridData[$index]['rivalIndex']=false;//index of rival
				for($maxPassColorCount=$startMaxPCC;$maxPassColorCount<=$this->maxSemicolorPass;$maxPassColorCount++){//semicolor rule bound
					$lIndex=$index;
					$maxIndex=$modelMaxIndex;
					//dont clear grid after error
					//$gridData[$index]['wrongIndex'];

					//fill grid
					//while(!empty($model)){
					while(count($usingIndexList)-1<$modelMaxIndex){

						$passFlag=false;
						//"left" index
						$lIndex=$this->skipEmpty($usingIndexList, $lIndex, $maxIndex-1);
						//"right" index

						foreach($indexMask as $sgKey => $value)
							if((int)$sgKey <= (int)$model[$lIndex]->scoreGroup){
								$startIndex=max($lIndex+1,$value['startIndex']);
								$rIndex=$this->skipEmpty($usingIndexList, $value['endIndex'], $startIndex, true);

								//rules
								while ($rIndex>=$startIndex) {

									$rIndex=$this->skipEmpty($usingIndexList, $rIndex, $startIndex, true);
									if(!empty($usingIndexList[$rIndex]))//bound destination, but all model is empty
										continue 2;//foreach($indexMask as $sgKey => $value)

									//strong layer - already playing pair 
									if( isset($model[$rIndex]->rivals[$model[$lIndex]->playerId]) || !empty($gridData[$lIndex]['wrongIndex'][$rIndex]) ){
										$gridData[$lIndex]['wrongIndex'][$rIndex]=true;
										$rIndex--;
										continue;//while ($rIndex>=$startIndex) {
									}

									//soft layer - semicolor 
									if($model[$lIndex]->lastColorCount>=$maxPassColorCount && !$lastTourFlag){
										$cIndex=$rIndex;
										while ($cIndex>=$startIndex){
											$cIndex=$this->skipEmpty($usingIndexList, $cIndex, $startIndex, true);
											if(!empty($usingIndexList[$cIndex]))//bound destination, but all model is empty
												continue 3;//foreach($indexMask as $sgKey => $value)
											if( isset($model[$cIndex]->rivals[$model[$lIndex]->playerId]) || !empty($gridData[$lIndex]['wrongIndex'][$cIndex]) ){
												$cIndex--;
												continue;//while ($cIndex>=$startIndex) {
											}
											if($model[$cIndex]->lastColorCount>=$maxPassColorCount && $model[$lIndex]->lastColor==$model[$cIndex]->lastColor){
												$cIndex--;
												continue;//while ($cIndex>=$startIndex)
											}
											//color pass
											$passFlag=true;
											$rIndex=$cIndex;
											break 3;//foreach($indexMask as $sgKey => $value)
										}//while ($cIndex>=$startIndex){

										//like a strong layer
										$rIndex--;
										continue;//while ($rIndex>=$startIndex) {
									}//if($model[$j]->lastColorCount>...

									//if not any break/continue then rIndex correct
									$passFlag=true;
									//$rIndex=$rIndex;
									break 2;//foreach($indexMask as $sgKey => $value)

								}//while ($rIndex>=$startIndex) {
								//overfull bound - search in next score group
								
							}//foreach($indexMask as $sgKey => $value)

						//any error - skip this set	
						if(
							!$passFlag || 
							$lIndex>=$rIndex ||
							!empty($usingIndexList[$lIndex]) ||//already uses
							!empty($usingIndexList[$rIndex]) ||
							!empty($gridData[$lIndex]['wrongIndex'][$lIndex]) ||//already in ban
							!empty($gridData[$lIndex]['wrongIndex'][$rIndex])
						){
							//may be last choice or error in rules / mismatch any rules
//							unset($usingIndexList[$lIndex]);
//							unset($usingIndexList[$rIndex]);
							if($maxPassColorCount==$this->maxSemicolorPass)//check another color before set error
								$gridData[$lIndex]['wrongIndex'][$rIndex]=true;
							$index=$lIndex;
							continue 2;//for($maxPassColorCount=$startMaxPCC;$maxPassColorCount<=$this->maxSemicolorPass;$maxPassColorCount++){
						}

						//$lIndex and $rIndex ready. $passFlag===true;
						

						//fill pair
						$usingIndexList[$lIndex]=true;//already using index in $index step
						$usingIndexList[$rIndex]=true;//already using index in $index step
						$gridData[$lIndex]['rivalIndex']=$rIndex;//index of rival
						
						$index=$lIndex+1;//success - go next step
						unset($gridData[$index]['wrongIndex']);//falled finished index in set pair with i. clear on next step
						
					}//while(!empty($model)){
					
					//if has error - send continue and skip this block
					//if no any error - then finish algorytm
					break 2;//for($minIndex=0;$minIndex<$modelMaxIndex;$minIndex++){
					
				}//for($maxPassColorCount=$startMaxPCC;$maxPassColorCount<=$this->maxSemicolorPass;$maxPassColorCount++){
				
				//if not skipped then has error and max sameColor.
				if(count($gridData[$index]['wrongIndex'])-1==$modelMaxIndex || $index<0 || $index>$modelMaxIndex){
					$mainErrorFlag=true;//if All is bad
					break;//while($index>=0 && $index<$modelMaxIndex){
				}
				$allOtherUse=true;
				$otherIndexes=$gridData[$index]['wrongIndex']+$usingIndexList;
				for($checkIndex=$index+1;$checkIndex<=$modelMaxIndex;$checkIndex++)
					if(empty($otherIndexes[$checkIndex]))
						$allOtherUse=false;
				//all of next (after index) choice is bad - step back
				if($allOtherUse){
					unset($gridData[$index]);
					$index = key(array_slice($gridData,-1,1,TRUE));//$last_key 
					
					$gridData[$index]['wrongIndex'][$gridData[$index]['rivalIndex']]=true;
					unset($usingIndexList[$gridData[$index]['rivalIndex']]);
					unset($usingIndexList[$index]);
					
					continue;//while($index>=0 && $index<$modelMaxIndex){
				}
				

			}//while($index>=0 && $index<$modelMaxIndex){
			
			/*
			 * 3. Generate another tour on success compose
			 */
			if(!empty($mainErrorFlag))
				throw new CHttpException(500,'Ошибка распределения. Невозможно применить правила распределения!!!');
			
			//slice odd
			if($oddIndex){
				$model2=$model[$oddIndex];
				$model1=new Grid;
				//modify
				$model1->tour=$model2->tour+1;
				$model1->tourDone=1;
				$model1->pairId=++$pairId;
				$model1->resultScore=$model2->resultScore+$this->scoreWining;
				$model1->scoreGroup=$model2->scoreGroup+$this->scoreWining;
				//$model1->scoreGroup=$this->scoreWining;//if only 3 group
				$model1->resultElo=$model2->resultElo;
				//copy
				$model1->appendGrid($model2);
				$model1->save()||Yii::log(CHtml::errorSummary($model1).'!BSC! AR save() return false: '.__FILE__.'['.__LINE__.']', 'error', 'protected.components.behaviors.GridJudeBehavior');
				unset($model[$oddIndex]);
			}
			
			//fill other
			foreach ($gridData as $lIndex => $value) {
				$rIndex=$value['rivalIndex'];
						
				//select element
				$model1=$model[$lIndex];
				$model2=$model[$rIndex];
				
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

					$model3->tour=$model4->tour=$tour;
					//$model1->scoreGroup;

					//$model1->tourDone;
					//$model1->resultScore;
					//$model1->resultElo;
					$model3->save()||Yii::log(CHtml::errorSummary($model3).'!BSC! AR save() return false: '.__FILE__.'['.__LINE__.']', 'error', 'protected.components.behaviors.GridJudeBehavior');
					$model4->save()||Yii::log(CHtml::errorSummary($model4).'!BSC! AR save() return false: '.__FILE__.'['.__LINE__.']', 'error', 'protected.components.behaviors.GridJudeBehavior');
					if($model3->hasErrors()||$model4->hasErrors())
						$transaction->rollback();
					else 
						$transaction->commit();
				}catch(Exception $e){
					$transaction->rollback();
					throw new CHttpException(500,'Ошибка распределения. Невозможно применить правила распределения.');
				}
			 }
				
		}//}else{//another tour
		
	}

	/**
	 * judge match of selectes pair.
	 * and calculate ELO
	 * @param integer $pairId pair for which a verdict
	 * @param string 'black'|'white'|'' $winner the winner of the pair. 
	 * Empty string is iqual deadheat
	 */
	public function Judge($pairId,$winner=null)
	{
		$modelBlack=$modelWhite=null;
		$mask=['black'=>'modelWhite','white'=>'modelBlack'];
		$model=$this->loadPairModel($pairId);
		foreach ($model as $value){
			${'model'.ucfirst($value->color)}=$value;
			$value->tourDone=1;
		}
		
		if(!$winner){
			foreach ($model as $value){
				$value->resultScore=$value->startScore+$this->scoreDeadHeat;
				$value->scoreGroup+=$this->scoreDeadHeat;
				//$value->scoreGroup=$this->scoreDeadHeat;//if only 3 group
				$value->resultElo=$value->startElo;
				$value->resultElo=Grid::calcNewElo($value->startElo,${$mask[$value->color]}->startElo,0.5);//dead heat
				$value->save()||Yii::log(CHtml::errorSummary($value).'!BSC! AR save() return false: '.__FILE__.'['.__LINE__.']', 'error', 'protected.components.behaviors.GridJudeBehavior');
			}
		}elseif($mask[$winner]){
			
			$model1=${'model'.ucfirst($winner)};
			$model1->resultScore=$model1->startScore+$this->scoreWining;
			$model1->scoreGroup+=$this->scoreWining;
			//$model1->scoreGroup=$this->scoreWining;
			$model2=${$mask[$winner]};
			$model2->resultScore=$model2->startScore+$this->scoreLosing;
			$model2->scoreGroup+=$this->scoreLosing;
			//$model2->scoreGroup=$this->scoreLosing;
			
			$model1->resultElo=Grid::calcNewElo($model1->startElo,$model2->startElo,1);//winner
			$model2->resultElo=Grid::calcNewElo($model2->startElo,$model1->startElo,0);//loser

			$model1->save()||Yii::log(CHtml::errorSummary($model1).'!BSC! AR save() return false: '.__FILE__.'['.__LINE__.']', 'error', 'protected.components.behaviors.GridJudeBehavior');
			$model2->save()||Yii::log(CHtml::errorSummary($model2).'!BSC! AR save() return false: '.__FILE__.'['.__LINE__.']', 'error', 'protected.components.behaviors.GridJudeBehavior');
		}
		
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
	
	/**
	 * Get index of first next/prev no using element.
	 * @param array $data the data array
	 * @param int $current start index of element for check
	 * @param int $bound last possible element
	 * @param bool $reverse=false ask (false) / desc (true) direction
	 */
	private function skipEmpty($data,$current,$bound,$reverse=false)
	{
		if($reverse)
			while(!empty($data[$current])&&$current>$bound)
				$current--;
		else
			while(!empty($data[$current])&&$current<$bound)
				$current++;
		return $current;
	}
	
}
?>
