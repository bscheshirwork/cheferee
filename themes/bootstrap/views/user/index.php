<?php
$this->breadcrumbs=array(
	'Судьи',
);

$this->menu=array(
	array('label'=>'Новая запись','url'=>array('create')),
	array('label'=>'Управление логинами судей','url'=>array('admin')),
);
?>

<h1>Список судей</h1>

<?php $this->widget('bootstrap.widgets.TbListView',array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
