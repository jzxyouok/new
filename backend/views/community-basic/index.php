<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\CommunityBasicSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Community Basics';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="community-basic-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a('Create Community Basic', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'community_id',
            'community_name',
            'community_logo',
            'province_id',
            'city_id',
            // 'area_id',
            // 'community_address',
            // 'community_longitude',
            // 'community_latitude',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
