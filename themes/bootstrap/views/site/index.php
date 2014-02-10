<?php
/* @var $this SiteController */

$this->pageTitle=Yii::app()->name;
?>

<?php $this->widget('bootstrap.widgets.TbCarousel', array(
    'items'=>array(
        array('image'=>Yii::app()->request->baseUrl.'/images/chefereehower04.jpg', 'label'=>'Сетка турнира', 'caption'=>CHtml::link('Посмотреть сетку турнира',Yii::app()->createUrl('grid'))),
        array('image'=>Yii::app()->request->baseUrl.'/images/chefereehower05.jpg', 'label'=>'Учасники', 'caption'=>CHtml::link('Посмотреть участников турнира',Yii::app()->createUrl('player'))),
    ),
)); ?>
