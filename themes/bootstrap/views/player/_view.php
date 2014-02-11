<div class="view">
	

	<b><?php echo CHtml::encode($data->getAttributeLabel('id')); ?>:</b>
	<?php echo CHtml::link(CHtml::encode($data->id),array('view','id'=>$data->id)); ?>
	<br />
	
	<div style="float: left; margin: 5px;">
	<?php echo CHtml::image(!$data->logo ? Yii::app()->request->baseUrl."/images/nologo.png" : $data->logo,"",Array('width'=>'64px','height'=>'64px')); ?>
	</div>

	<b><?php echo CHtml::encode($data->getAttributeLabel('name')); ?>:</b>
	<?php echo CHtml::encode($data->name); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('nickname')); ?>:</b>
	<?php echo CHtml::encode($data->nickname); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('birthyear')); ?>:</b>
	<?php echo CHtml::encode($data->birthyear); ?>
	<br />

	<b><?php echo CHtml::encode($data->getAttributeLabel('elo')); ?>:</b>
	<?php echo CHtml::encode($data->elo); ?>
	<br />
	<?php if($data->leave){?>
	<b><?php echo CHtml::encode($data->getAttributeLabel('leave')); ?></b>
	<br />
	<?php } ?>

</div>