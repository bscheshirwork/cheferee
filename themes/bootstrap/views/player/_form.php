<?php $form=$this->beginWidget('bootstrap.widgets.TbActiveForm',array(
	'id'=>'player-form',
	'enableAjaxValidation'=>false,
)); ?>

	<p class="help-block">Поля со звёздочкой <span class="required">*</span> необходимо заполнить.</p>

	<?php echo $form->errorSummary($model); ?>

	<?php echo $form->textFieldRow($model,'name',array('class'=>'span5','maxlength'=>256)); ?>

	<?php echo $form->textFieldRow($model,'nickname',array('class'=>'span5','maxlength'=>256)); ?>

	<?php echo $form->textFieldRow($model,'birthyear',array('class'=>'span5','maxlength'=>4)); ?>

	<?php echo $form->textFieldRow($model,'elo',array('class'=>'span5')); ?>

	<?php echo $form->textFieldRow($model,'logo',array('class'=>'span5','maxlength'=>1024)); ?>
	
	<?php echo $form->checkBoxRow($model,'leave'); ?>

	<div class="form-actions">
		<?php $this->widget('bootstrap.widgets.TbButton', array(
			'buttonType'=>'submit',
			'type'=>'primary',
			'label'=>$model->isNewRecord ? 'Создать' : 'Сохранить',
		)); ?>
		<?php $this->widget('bootstrap.widgets.TbButton', array(
			'buttonType'=>'link',
			'url'=>$model->isNewRecord ? array('/player') : array('/player/'.$model->id),
			'type'=>'primary',
			'label'=>'Отменить',
		)); ?>
	</div>

<?php $this->endWidget(); ?>
