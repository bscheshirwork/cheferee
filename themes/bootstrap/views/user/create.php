<?php
$this->breadcrumbs=array(
	'Судьи'=>array('index'),
	'Создать запись для входа судьи',
);

$this->menu=array(
	array('label'=>'Список судей','url'=>array('index')),
	array('label'=>'Управление логинами судей','url'=>array('admin')),
);
?>

<h1>Создать запись для входа судьи</h1>

<?php echo $this->renderPartial('_form', array('model'=>$model)); ?>