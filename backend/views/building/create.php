<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\CommunityBuilding */

$this->title = 'Create Community Building';
$this->params['breadcrumbs'][] = ['label' => 'Community Buildings', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="community-building-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
