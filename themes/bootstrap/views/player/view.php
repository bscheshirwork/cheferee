<?php
$this->breadcrumbs=array(
	'Игроки'=>array('index'),
	$model->name,
);

if(!Yii::app()->user->isGuest)
	$this->menu=array(
		array('label'=>'Список игроков','url'=>array('index')),
		array('label'=>'Создать запись о игроке','url'=>array('create')),
		array('label'=>'Обновить запись о игроке','url'=>array('update','id'=>$model->id)),
		array('label'=>'Удалить запись о игроке','url'=>'#','linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Вы действительно желаете удалить запись о игроке?')),
		array('label'=>'Управление записями','url'=>array('admin')),
	);
?>

<h1>Просмотр записи №<?php echo $model->id; ?></h1>

<?php $this->widget('bootstrap.widgets.TbDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'name',
		'nickname',
		'birthyear',
		'elo',
		'logo',
	),
)); ?>

<?php if($model->leave){?>
<b><?php echo CHtml::encode($model->getAttributeLabel('leave')); ?></b>
<br />
<?php } ?>