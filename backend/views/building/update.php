<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\CommunityBuilding */

$this->title = 'Update Community Building: ' . $model->building_id;
$this->params['breadcrumbs'][] = ['label' => 'Community Buildings', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->building_id, 'url' => ['view', 'id' => $model->building_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="community-building-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
