<?php
$this->breadcrumbs=array(
	'Судьи'=>array('index'),
	'Управление логинами судей',
);

$this->menu=array(
	array('label'=>'Список судей','url'=>array('index')),
	array('label'=>'Новая запись','url'=>array('create')),
);

?>

<h1>Управление логинами судей</h1>

<p>
Для поиска можно использовать операторы (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b>
или <b>=</b>) поместив их в начале строки.
</p>


<?php $this->widget('bootstrap.widgets.TbGridView',array(
	'id'=>'user-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		'id',
		'username',
		array(
			'class'=>'bootstrap.widgets.TbButtonColumn',
		),
	),
)); ?>
