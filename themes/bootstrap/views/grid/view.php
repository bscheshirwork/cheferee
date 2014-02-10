<div class="view">

<div class="pair <?php echo $model->tourDone?'pair-finished':''; ?>">
	
	<div class='player-white'>
	
	<?php if(isset($modelWhite->player)){ ?>
		
		<div>Белые</div>
		
		<div style="float: left; margin: 5px;">
		<?php echo CHtml::image(!$modelWhite->player->logo ? Yii::app()->request->baseUrl."/images/nologo.png" : $modelWhite->player->logo,"",Array('width'=>'64','height'=>'64')); ?>
		</div>

		<b><?php echo CHtml::encode($modelWhite->player->getAttributeLabel('name')); ?></b>
		<?php echo CHtml::encode($modelWhite->player->name); ?>
		<br />

		<b><?php echo CHtml::encode($modelWhite->player->getAttributeLabel('nickname')); ?></b>
		<?php echo CHtml::encode($modelWhite->player->nickname); ?>
		<br />

		<b><?php echo CHtml::encode($modelWhite->player->getAttributeLabel('birthyear')); ?></b>
		<?php echo CHtml::encode($modelWhite->player->birthyear); ?>
		<br />

		<b><?php echo CHtml::encode($modelWhite->player->getAttributeLabel('elo')); ?></b>
		<?php echo CHtml::encode($modelWhite->startElo); ?>
		<?php if($model->tourDone)
			echo '-> '.CHtml::encode($modelWhite->resultElo); ?>
		<br />
		
	<?php }else{//if(isset($modelWhite->player)) ?>
	
		<div>Игрок не заявлен / вышел из игры</div>
	
	<?php }//if(isset($modelWhite->player)) ?>
	
	</div>
	
	<div class='player-vs'>
	VS
	</div>
	
	<div class='player-black'>
		
		<div>Чёрные</div>
		
		<?php if(isset($modelBlack->player)){ ?>

		<div style="float: right; margin: 5px;">
		<?php echo CHtml::image(!$modelBlack->player->logo ? Yii::app()->request->baseUrl."/images/nologo.png" : $modelBlack->player->logo,"",Array('width'=>'64','height'=>'64')); ?>
		</div>

		<?php echo CHtml::encode($modelBlack->player->name); ?>
		<b><?php echo CHtml::encode($modelBlack->player->getAttributeLabel('name')); ?></b>
		<br />

		<?php echo CHtml::encode($modelBlack->player->nickname); ?>
		<b><?php echo CHtml::encode($modelBlack->player->getAttributeLabel('nickname')); ?></b>
		<br />

		<?php echo CHtml::encode($modelBlack->player->birthyear); ?>
		<b><?php echo CHtml::encode($modelBlack->player->getAttributeLabel('birthyear')); ?></b>
		<br />

		<?php echo CHtml::encode($modelBlack->startElo); ?>
		<?php if($model->tourDone)
			echo '-> '.CHtml::encode($modelBlack->resultElo); ?>
		<b><?php echo CHtml::encode($modelBlack->player->getAttributeLabel('elo')); ?></b>
		<br />
	
	<?php }else{//if(isset($modelBlack->player)) ?>
	
		<div>Игрок не заявлен / вышел из игры</div>
	
	<?php }//if(isset($modelBlack->player)) ?>
	
	</div>
	
	<?php if($model->tourDone){ ?>
		<div class='pair-result'><a id="j<?=$model->pairId?>"></a>
		<?php if(isset($modelWhite)&&$modelWhite->resultScore-$modelWhite->startScore==Yii::app()->params['scoreWining']){ ?>
			<div>Белые выиграли</div>
		<?php }elseif(isset($modelBlack)&&$modelBlack->resultScore-$modelBlack->startScore==Yii::app()->params['scoreWining']){ ?>
			<div>Чёрные выиграли</div>
		<?php }else{ ?>
			<div>Ничья</div>
		<?php } ?>
		</div>
	<?php } ?>
	
<?php if( !Yii::app()->user->isGuest && !$model->tourDone ){ ?>
	<div class='judge-button'>
	<div class='btn-toolbar'>
    <?php $this->widget('bootstrap.widgets.TbButtonGroup', array(
        'buttons'=>array(
            array('label'=>'Результат матча', 'items'=>array(
                array('label'=>'Победа белых', 'url'=>Yii::app()->createUrl('grid/judge', array('id'=>$model->pairId,'winner'=>'white','#'=>'j'.$model->pairId))),
                array('label'=>'Победа чёрных', 'url'=>Yii::app()->createUrl('grid/judge', array('id'=>$model->pairId,'winner'=>'black','#'=>'j'.$model->pairId))),
                array('label'=>'Ничья', 'url'=>Yii::app()->createUrl('grid/judge', array('id'=>$model->pairId,'#'=>'j'.$model->pairId))),
            )),
        ),
    )); ?>
	</div>
	</div>
<?php } ?>

</div>	
	


</div>