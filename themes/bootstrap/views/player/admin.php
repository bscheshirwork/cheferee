<?php
$this->breadcrumbs=array(
	'Игроки'=>array('index'),
	'Управление записями о игроках',
);

$this->menu=array(
	array('label'=>'Список игроков','url'=>array('index')),
	array('label'=>'Создать запись о игроке','url'=>array('create')),
);

?>

<h1>Управление записями о игроках</h1>

<p>
Для поиска можно использовать операторы (<b>&lt;</b>, <b>&lt;=</b>, <b>&gt;</b>, <b>&gt;=</b>, <b>&lt;&gt;</b>
или <b>=</b>) поместив их в начале строки.
</p>


<?php $this->widget('bootstrap.widgets.TbGridView',array(
	'id'=>'player-grid',
	'dataProvider'=>$model->search(),
	'filter'=>$model,
	'columns'=>array(
		'id',
		'name',
		'nickname',
		'birthyear',
		'elo',
		array(
			'class'=>'bscheshir.widgets.BscImageColumn',
			'header'=>'Лого',
			'imageHtmlOptions'=>Array('width'=>'64px','height'=>'64px'),
			'urlExpression' => '!$data->logo ? "/images/nologo.png" : $data->logo',
		),		
		array(
			'class'=>'bootstrap.widgets.TbButtonColumn',
		),
	),
)); ?>
