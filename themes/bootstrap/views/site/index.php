<?php
/* @var $this SiteController */

$this->pageTitle=Yii::app()->name;
?>

<?php $this->widget('bootstrap.widgets.TbCarousel', array(
    'items'=>array(
        array('image'=>'/images/chefereehower04.jpg', 'label'=>'Сетка турнира', 'caption'=>CHtml::link('Посмотреть сетку турнира','/grid')),
        array('image'=>'/images/chefereehower05.jpg', 'label'=>'Учасники', 'caption'=>CHtml::link('Посмотреть участников турнира','/player')),
    ),
)); ?>
