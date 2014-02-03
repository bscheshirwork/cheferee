<?php
$this->breadcrumbs=array(
	'Игроки'=>array('index'),
	$model->name=>array('view','id'=>$model->id),
	'Обновить запись о игроке',
);

$this->menu=array(
	array('label'=>'Список игроков','url'=>array('index')),
	array('label'=>'Создать запись о игроке','url'=>array('create')),
	array('label'=>'Просмотр записи о игроке','url'=>array('view','id'=>$model->id)),
	array('label'=>'Управление записями','url'=>array('admin')),
);
?>

<h1>Обновить запись о игроке <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>