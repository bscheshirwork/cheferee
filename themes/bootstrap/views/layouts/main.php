<?php /* @var $this Controller */ ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />

    <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->theme->baseUrl; ?>/css/styles.css" />

	<title><?php echo CHtml::encode($this->pageTitle); ?></title>

	<?php Yii::app()->bootstrap->register(); ?>
	<?php Yii::app()->clientScript->registerScript('bscfooterfix', "
		$(document).ready(function(){
			if ($(document).height() <= $(window).height())
				$('#footer').addClass('container navbar-fixed-bottom');
		});
	"); ?>
	
</head>

<body>

<?php $this->widget('bootstrap.widgets.TbNavbar',array(
    'items'=>array(
        array(
            'class'=>'bootstrap.widgets.TbMenu',
            'items'=>array(
                array('label'=>'Главная', 'icon'=>'home', 'url'=>array('/site/index')),
                array('label'=>'Сетка турнира', 'url'=>array('/grid')),
                array('label'=>'Игроки', 'url'=>array('/player')),
                array('label'=>'Управление пользователями', 'url'=>array('/user'), 'visible'=>(Yii::app()->user->name=='admin')),
                array('label'=>'О сайте', 'url'=>array('/site/page', 'view'=>'about')),
                array('label'=>'Обратная связь', 'url'=>array('/site/contact')),
                array('label'=>'Вход', 'icon'=>'user', 'url'=>array('/site/login'), 'visible'=>Yii::app()->user->isGuest),
                array('label'=>'Выход ('.Yii::app()->user->name.')', 'icon'=>'off', 'url'=>array('/site/logout'), 'visible'=>!Yii::app()->user->isGuest)
            ),
        ),
    ),
)); ?>

<div class="container" id="page">

	<?php if(isset($this->breadcrumbs)):?>
		<?php $this->widget('bootstrap.widgets.TbBreadcrumbs', array(
			'links'=>$this->breadcrumbs,
		)); ?><!-- breadcrumbs -->
	<?php endif?>

	<?php echo $content; ?>

	<div class="clear"></div>

	<div id="footer">
		<div style="float:left;">Copyright &copy; <?php echo date('Y'); ?> by BSCheshir.</div>
		<div style="float:left; margin:0 10px;">All Rights Reserved.</div>
		<div style="float:left;"><?php echo Yii::powered(); ?></div>
	</div><!-- footer -->

</div><!-- page -->

</body>
</html>
