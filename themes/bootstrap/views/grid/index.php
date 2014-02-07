<?php
/* @var $this GridController */
/* @var data array */
/* @var gridIsClear bool */
/* @var maxTour int */
/* @var resultGrid CArrayDataProvider */
?>
<?php
$this->breadcrumbs=array(
	'Сетка турнира',
);
?>

<?php 
	$tourId=null;
	foreach($data as $tourId=>$tour){?>

	<div class='tour-header'>Тур <?=$tourId?></div>

	<?php foreach($tour as $pair){
		 echo $this->renderPartial('view', array(
					'model'=>$pair['model'],//last valid model of pair
					'modelBlack'=>$pair['modelBlack'],
					'modelWhite'=>$pair['modelWhite'],
				));
	?>

		<div class='pair-separator'></div>
	<?php } ?>
	<div class='tour-footer'></div>
<?php } ?>
	
	
<?php if( !Yii::app()->user->isGuest ){ ?>
	<div class='compose-button'>

	<?php $this->widget('bootstrap.widgets.TbMenu', array(
		'type'=>'pills', // '', 'tabs', 'pills' (or 'list')
		'stacked'=>false, // whether this is a stacked menu
		'items'=>array(
			
			array(
				'label'=>'Генерировать начальное распределение игроков',
				'visible'=>!Yii::app()->user->isGuest && $gridIsClear,
				'url'=>'#',
				'linkOptions'=>array('submit'=>array('compose','id'=>1),
					'confirm'=>'Вы действительно желаете генерировать сетку?')
				),
			array(
				'label'=>'Генерировать распределение игроков тура №'.($tourId+1),
				'visible'=>!Yii::app()->user->isGuest && !$gridIsClear && $tourId && $maxTour>$tourId,
				'url'=>'#',
				'linkOptions'=>array('submit'=>array('compose','id'=>$tourId+1),
					'confirm'=>'Вы действительно желаете генерировать сетку? Результаты всех матчей предыдущего тура должны быть введены!')
				),
			array(
				'label'=>'Удалить распределение туров, вплоть до тура №'.($tourId-1),
				'visible'=>Yii::app()->user->name=='admin' && !$gridIsClear && $tourId,
				'url'=>'#',
				'linkOptions'=>array('submit'=>array('removeafter','id'=>$tourId-1),
					'confirm'=>'Вы действительно желаете удалить ВСЕ ЭТИ записи?')
				),
		),
	)); ?>
		
	</div>
<?php } ?>

<?php if(isset($resultGrid)&&isset($maxTour)){ ?>
	<div class='tour-header'>Сетка</div>
	<?php echo $this->renderPartial('grid', ['dataProvider'=>$resultGrid,'maxTour'=>$maxTour]); ?>
<?php } ?>