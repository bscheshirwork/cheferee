<?php
$this->breadcrumbs=array(
	'Игроки',
);

if(!Yii::app()->user->isGuest)
	$this->menu=array(
		array('label'=>'Создать запись о игроке','url'=>array('create')),
		array('label'=>'Управление записями','url'=>array('admin')),
	);
?>

<h1>Игроки</h1>

<?php $this->widget('bootstrap.widgets.TbListView',array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
