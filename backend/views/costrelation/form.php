<?php

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use app\models\CostRelation;
use app\models\CommunityBasic;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use kartik\daterange\DateRangePicker;

/* @var $this yii\web\View */
/* @var $model app\models\CostRelation */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="cost-relation-form">
   
    <?php $form = ActiveForm::begin(['id' => 'form-id',
									 'enableAjaxValidation' => true, 
									]); ?>
<table align="center" style="width:550px; background-color:#F3F3F3;border-radius:20px;">
    <tr>
    	<td>
    		<br />
    	</td>
    </tr>
	<tr>
		<td width="3%">

		</td>
		<td>
			<div class="row">
				<div class="col-lg-5">
					<?= $form->field($model, 'community')->dropDownList($community);?>
				</div>
				<div class="col-lg-3">
					<?= $form->field($model, 'building_id')->dropDownList($building) ?>
				</div>

				<div class="col-lg-4">
					<?= $form->field($model, 'realestate_id')->dropDownList($num) ?>
				</div>
			</div>

			<div class="row">
				<div class="col-lg-6">
					<?= $form->field($model, 'price')->dropDownList($cost,['prompt'=>'请选择','id'=>'costrelation-parent'])->label('费项') ?>
				</div>
				<div class="col-lg-4">
					<?= $form->field($model, 'cost_id')->widget(DepDrop::classname(), [
                        'type' => DepDrop::TYPE_SELECT2,
                        'options'=>['id'=>'costrelation-price'],
	                    'select2Options'=>['pluginOptions'=>['allowClear'=>true]],
                        'pluginOptions'=>[
                            'depends'=>['costrelation-parent'],
                            'placeholder'=>'请选择...',
                            'url'=>Url::to(['/costrelation/p'])
                        ]
                    ])->label('单价') ?>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-6">
					<?= $form->field($model, 'from', [
                //'addon'=>['prepend'=>['content'=>'<i class="glyphicon glyphicon-calendar"></i>']],
                'options'=>['class'=>'drp-container']])
	                     ->widget(DateRangePicker::classname(), [
                'useWithAddon'=>true,
				'pluginOptions'=>[
                'singleDatePicker'=>true,
                'showDropdowns'=>true
                ]
            ]) ?>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12">
					<?= $form->field($model, 'property')->textArea(['maxlength' => true]) ?>
				</div>
			</div>

			<div class="form-group" align="center">
				<?= Html::submitButton($model->isNewRecord ? '提交' : '更新', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
			</div>
		</td>
		<td width="3%">

		</td>
	</tr>
</table>
    <?php ActiveForm::end(); ?>
    
</div>
