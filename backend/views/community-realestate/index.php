<?php

use kartik\ grid\ GridView;
use yii\ helpers\ Html;
use app\ models\ CommunityBasic;
use app\ models\ CommunityBuilding;
use yii\ bootstrap\ Modal;
use yii\ helpers\ Url;
use kartik\ dialog\ Dialog;

Modal::begin( [
	'id' => 'view-modal',
	'header' => '<h4 class="modal-title">房屋操作</h4>',
	//'footer' => '<a href="#" class="btn btn-primary" data-dismiss="modal">Close</a>',
] );
$V_Url = Url::toRoute( [ 'view' ] );
$u_Url = Url::toRoute( [ 'update' ] );
$n_Url = Url::toRoute( [ '/costrelation/create1' ] );
$c_Url = Url::toRoute( [ '/user-invoice/c' ] );
$i_Url = Url::toRoute(['import']);
$cr_Url = Url::toRoute(['create']);

$vJs = <<<JS
    $('.view').on('click', function () {
        $.get('{$V_Url}', { id: $(this).closest('tr').data('key') },
            function (data) {
                $('.modal-body').html(data);
            }  
        );
    });
JS;
$this->registerJs( $vJs );

$uJs = <<<JS
    $('.update').on('click', function () {
        $.get('{$u_Url}', { id: $(this).closest('tr').data('key') },
            function (data) {
                $('.modal-body').html(data);
            }  
        );
    });
JS;
$this->registerJs( $uJs );

$nJs = <<<JS
    $('.create1').on('click', function () {
        $.get('{$n_Url}', { id: $(this).closest('tr').data('key') },
           function(data){
              $('.modal-body').html(data);
           }
        );
    });
JS;
$this->registerJs( $nJs );

$cJs = <<<JS
    $('.c').on('click', function () {
        $.get('{$c_Url}', { id: $(this).closest('tr').data('key') },
           function(data){
              $('.modal-body').html(data);
           }
        );
    });
JS;
$this->registerJs( $cJs );

$cJs = <<<JS
    $('.i').on('click', function () {
        $.get('{$i_Url}', { id: $(this).closest('tr').data('key') },
           function(data){
              $('.modal-body').html(data);
           }
        );
    });
JS;
$this->registerJs( $cJs );

$cJs = <<<JS
    $('.cr').on('click', function () {
        $.get('{$cr_Url}', { id: $(this).closest('tr').data('key') },
           function(data){
              $('.modal-body').html(data);
           }
        );
    });
JS;
$this->registerJs( $cJs );

Modal::end();

/* @var $this yii\web\View */
/* @var $searchModel app\modelsCommunityRealestateSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '房屋管理';
$this->params[ 'breadcrumbs' ][] = $this->title;
?>

<div class="community-realestate-index">
	<?php echo $this->render('_search', ['model' => $searchModel]);
	
	$message = Yii::$app->getSession()->getFlash('fail');
	if($message == 2){
		echo "<script>alert('文件格式有误，请重新选择')</script>";
	}elseif($message == 3){
		echo "<script>alert('数据有误，请修改源数据')</script>";echo '';
	}
	?>

	<?php
	$gridColumn = [
		
		[ 'class' => 'kartik\grid\CheckboxColumn',
			'width' => '30px',
			'name' => 'id',
		],
		
		[ 'class' => 'kartik\grid\SerialColumn',
			'header' => '序号'
		],
		[ 'attribute' => 'community_id',
			'value' => 'community0.community_name',
			/*'filterType'=>GridView::FILTER_SELECT2,
		     'filter'=> CommunityBasic::find()
		             -> select(['community_name','community_id'])
		             -> orderBy('community_name')
		             -> indexBy('community_id')
		             -> column(),
		     'filterInputOptions'=>['placeholder'=>'请选择'],
			 'filterWidgetOptions'=>[
                             'pluginOptions'=>['allowClear'=>true],
		                 ],*/
			'width' => '180px',
			'hAlign' => 'center',
		],
		[ 'attribute' => 'building_id',
			//'value' => 'building0.building_name',
			'format' => 'raw',
			'value' => function ( $model ) {
				$building = CommunityBuilding::find()->select( 'building_name' )->where( [ 'building_id' => $model->building_id ] )->asArray()->one();
				/*$url = Yii::$app->urlManager->createUrl( [ 'costrelation/create',
					'realestate_id' => $model->realestate_id,
				] );*/
				return Html::a( $building[ 'building_name' ], '#', [
					'data-toggle' => 'modal',
					'data-target' => '#view-modal',
					'class' => 'create1',
				] );
			},
			'hAlign' => 'center',
			'width' => '80px',
		],

		[ 'attribute' => 'room_number',
			'format' => 'raw',
			'value' => function ( $model ) {
				$url = Yii::$app->urlManager->createUrl( [ 'costrelation/index1', 'realestate_id' => $model->realestate_id ] );
				$name = explode( '-', $model->room_number );
				$count = count( $name );
				if ( $count == 1 ) {
					return Html::a( '1' . '单元', $url );
				} else {
					return Html::a( reset( $name ) . '单元', $url );
				}

			},
			'hAlign' => 'center',
			'width' => '80px',
		],

		[ 'attribute' => 'room_name',
			'format' => 'raw',
			'value' => function ( $model ) {
				//$url = Yii::$app->urlManager->createUrl( [ 'user-invoice/c', 'id' => $model->realestate_id ] );
				$number = explode( '-', $model->room_name );
				return Html::a( end( $number ), '#', [
					'data-toggle' => 'modal',
					'data-target' => '#view-modal',
					'class' => 'c',
				] );
			},
			'hAlign' => 'center',
			'width' => '90px',
		],

		[ 'attribute' => 'owners_name',
			'class' => 'kartik\grid\EditableColumn',
			'editableOptions' => [
				'formOptions' => [ 'action' => [ '/community-realestate/reale' ] ],
				'inputType' => \kartik\ editable\ Editable::INPUT_TEXT,
			],
			'hAlign' => 'center',
			'width' => 'px',
		],

		[ 'attribute' => 'owners_cellphone',
			'class' => 'kartik\grid\EditableColumn',
			'editableOptions' => [
				'formOptions' => [ 'action' => [ '/community-realestate/reale' ] ],
				'inputType' => \kartik\ editable\ Editable::INPUT_TEXT,
			],
		],

		[ 'attribute' => 'acreage',
			'class' => 'kartik\grid\EditableColumn',
			'editableOptions' => [
				'formOptions' => [ 'action' => [ '/community-realestate/reale' ] ],
				'inputType' => \kartik\ editable\ Editable::INPUT_TEXT,
			],
			'hAlign' => 'center',
			'width' => '70px'
		],

		[ 'class' => 'kartik\grid\ActionColumn',
			'template' => '{update} {view}',
			'buttons' => [
				'view' => function ( $url, $model, $key ) {
					return Html::a( '查看', '#', [
						'data-toggle' => 'modal',
						'data-target' => '#view-modal', //modal 名字
						'class' => 'view', //操作名
						'data-id' => $key,
					] );
				},
				'update' => function ( $url, $model, $key ) {
					return Html::a( '修改', '#', [
						'data-toggle' => 'modal',
						'data-target' => '#view-modal',
						'class' => 'update',
						'data-id' => $key,

					] );
				},
			],
			'width' => '80px',
			'header' => '操作'
		],
	];
	echo GridView::widget( [
		'dataProvider' => $dataProvider,
		//'filterModel' => $searchModel,
		//'showFooter' => true,
		'options' => [ 'id' => 'grid' ],
		'panel' => [ 'type' => 'info', 'heading' => '房屋列表',
			'before' => Html::a( 'New', //[ 'create' ], [ 'class' => 'btn btn-primary' ] )
								'#', [ 
		                'data-toggle' => 'modal',
						'data-target' => '#view-modal',
						'class' => 'btn btn-primary cr' ] )
		],
		'toolbar' => [
			[ 'content' =>
				Html::a( '导入','#', [ 
		                'data-toggle' => 'modal',
						'data-target' => '#view-modal',
						'class' => 'btn btn-info i' ] ).''.
			    Html::a( '<i class="glyphicon glyphicon-repeat"></i>', [ 'index' ], [ 'data-pjax' => 0, 'class' => 'btn btn-default' ] )
			],
			'{export}',
			'{toggleData}',
		],
		'columns' => $gridColumn,
		'hover' => true,
	] );
	?>
</div>