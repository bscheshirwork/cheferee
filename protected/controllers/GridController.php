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

	/*
	 * Connect a behavior
	 */
    public function behaviors()
    {
        return array(
            'GJBehavior'=>array(
                'class'=>'GridJudeBehavior',
               // 'accuracyCount'=>Yii::app()->params['accuracyCount'],
                'scoreWining'=>Yii::app()->params['scoreWining'],
                'scoreDeadHeat'=>Yii::app()->params['scoreDeadHeat'],
                'scoreLosing'=>Yii::app()->params['scoreLosing'],
                'maxSemicolorPass'=>Yii::app()->params['maxSemicolorPass'],
                'minSemicolorCheck'=>Yii::app()->params['minSemicolorCheck'],
             ),
        );
    }

	/**
	 * Create a distribution of players for the tour.
	 * The players will be distributed for pair
	 * note: previous tour should be finished
	 * @param integer $id the tour of the models
	 * "$id" similar to urlManager
	 */
	public function actionCompose($id)
	{
		$this->Compose($id);
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
		$this->Judge($id,$winner);
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

}
