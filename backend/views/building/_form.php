<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\CommunityBuilding */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="community-building-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'community_id')->textInput() ?>

    <?= $form->field($model, 'building_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'building_parent')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
