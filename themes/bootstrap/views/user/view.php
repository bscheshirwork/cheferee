<?php
$this->breadcrumbs=array(
	'Судьи'=>array('index'),
	$model->id,
);

$this->menu=array(
	array('label'=>'Список судей','url'=>array('index')),
	array('label'=>'Новая запись','url'=>array('create')),
	array('label'=>'Обновить запись','url'=>array('update','id'=>$model->id)),
	array('label'=>'Удалить запись','url'=>'#','linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Вы действительно хотите удалить запись для входа судьи?')),
	array('label'=>'Управление логинами судей','url'=>array('admin')),
);
?>

<h1>Просмотр записи №<?php echo $model->id; ?></h1>

<?php $this->widget('bootstrap.widgets.TbDetailView',array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'username',
	),
)); ?>
