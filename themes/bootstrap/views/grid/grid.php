<?php 
$columns=['playerName:Игрок',];
for($i=1;$i<=$maxTour;$i++){
	$columns[]='scoreTour'.$i.':Тур '.$i;
	$columns[]='eloTour'.$i.':ELO ';
}
?>
<?php $this->widget('bootstrap.widgets.TbGridView',array(
	'id'=>'grid-grid',
	'dataProvider'=>$dataProvider,
	'columns'=>$columns,
)); ?>
