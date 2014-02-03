<?php
$this->breadcrumbs=array(
	'Судьи'=>array('index'),
	$model->id=>array('view','id'=>$model->id),
	'Обновить запись для входа судьи',
);

$this->menu=array(
	array('label'=>'Список судей','url'=>array('index')),
	array('label'=>'Новая запись','url'=>array('create')),
	array('label'=>'Просмотр записи','url'=>array('view','id'=>$model->id)),
	array('label'=>'Управление логинами судей','url'=>array('admin')),
);
?>

<h1>Обновить запись для входа судьи <?php echo $model->id; ?></h1>

<?php echo $this->renderPartial('_form',array('model'=>$model)); ?>