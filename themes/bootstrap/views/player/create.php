<?php
$this->breadcrumbs=array(
	'Игроки'=>array('index'),
	'Создать запись с информацией о игроке',
);

$this->menu=array(
	array('label'=>'Список игроков','url'=>array('index')),
	array('label'=>'Управление записями','url'=>array('admin')),
);
?>

<h1>Создать запись с информацией о игроке</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>