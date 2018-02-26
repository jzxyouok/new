<?php

use yii\ helpers\ Html;
use kartik\ grid\ GridView;
use yii\ widgets\ Pjax;
use app\ models\ CommunityBasic;
use app\ models\ CommunityBuilding;
use app\ models\ Status;
use yii\ bootstrap\ Modal;
use yii\ helpers\ Url;
use kartik\ form\ ActiveForm;

Modal::begin( [
	'id' => 'update-modal',
	'header' => '<h4 class="modal-title">费项管理</h4>',
	//'footer' => '<a href="#" class="btn btn-primary" data-dismiss="modal">Close</a>',
] );
$requestUpdateUrl = Url::toRoute( 'view' );
$importUrl = Url::toRoute( 'import' );
$newUrl = Url::toRoute( 'new' );

$updateJs = <<<JS
    $('.order').on('click', function () {
        $.get('{$requestUpdateUrl}', { id: $(this).closest('tr').data('key') },
            function (data) {
                $('.modal-body').html(data);
            }  
        );
    });
JS;
$this->registerJs( $updateJs );

$updateJs = <<<JS
    $('.import').on('click', function () {
        $.get('{$importUrl}', { id: $(this).closest('tr').data('key') },
            function (data) {
                $('.modal-body').html(data);
            }  
        );
    });
JS;
$this->registerJs( $updateJs );

$updateJs = <<<JS
    $('.new').on('click', function () {
        $.get('{$newUrl}', { id: $(this).closest('tr').data('key') },
            function (data) {
                $('.modal-body').html(data);
            }  
        );
    });
JS;
$this->registerJs( $updateJs );

Modal::end();

$script = <<<SCRIPT

//删除
$(".gridviewdelete").on("click", function () {
if(confirm('您确定要删除吗？')){
    var keys = $("#grid").yiiGridView("getSelectedRows");
     $.ajax({
            url: '/user-invoice/del',
            data: {ids:keys},
            type: 'post',
                   })
    }
});

//缴费
$(".gridviewpay").on("click", function () {
if(confirm('您确定要缴费吗？')){
    var keys = $("#grid").yiiGridView("getSelectedRows");
     $.ajax({
            url: '/user-invoice/pay',
            data: {ids:keys},
            type: 'post',
            success: function (id) {
                t = JSON.parse(id);
                if (isset(t)) {
                    window.location.href= window.location.href;
                }
            },
        })
    }
});
SCRIPT;
$this->registerJs( $script );

/* @var $this yii\web\View */
/* @var $searchModel app\models\UserInvoiceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '缴费管理';
$this->params[ 'breadcrumbs' ][] = $this->title;
?>
<div class="user-invoice-index">

	<?php $this->render('_search', ['model' => $searchModel]);
	
	$message = Yii::$app->getSession()->getFlash('fail');
	if($message == 1){
		echo "<script>alert('选择内容不能为空，请重新选择！')</script>";
	}elseif($message == 2){
		echo "<script>alert('文件格式有误，请重新选择')</script>";
	}elseif($message == 3){
		echo "<script>alert('数据有误，请修改源数据')</script>";
	}elseif($message == 4){
		echo "<script>alert('费项选择有误，请重新选择')</script>";
	}
		
	?>
	<?php Pjax::begin(); ?>
	<?php
	$gridColumn = [
		/*['class' => 'kartik\grid\SerialColumn',
			'header' => '序<br />号'],*/
		[ 'class' => 'kartik\grid\CheckboxColumn',
			'name' => 'id',
			'width' => '30px'
		],

		[ 'attribute' => 'invoice_id',
			//'footer' => Html::a('缴费', "javascript:void(0);", ['class' => 'btn btn-default gridviewpay ']),
			'width' => '70px',
			'hAlign' => 'center'
		],
		[ 'attribute' => 'community.community_name',
			'pageSummary' => '合计：',
			'filterType' => GridView::FILTER_SELECT2,
			'filter' => CommunityBasic::find()->select( [ 'community_name' ] )->orderBy( 'community_name' )->indexBy( 'community_name' )->column(),
			'filterInputOptions' => [ 'placeholder' => '请选择' ],
			'filterWidgetOptions' => [
				'pluginOptions' => [ 'allowClear' => true ],
			],
			'hAlign' => 'center',
			'width' => '150px'
		],

		[ 'attribute' => 'building.building_name',
			'label' => '楼宇',
			//'group' => true,
			//'hidden' => true,
			'hAlign' => 'center',
			'width' => '80px'
		],

		[ 'attribute' => 'room.room_name',
			//'value' => 'room.room_number',
			// 'group' => true,
			'hAlign' => 'center',
			'width' => '90px'
		],
		[ 'attribute' => 'year',
			'value' => function ( $model ) {
				$y = explode( '年', $model->description );
				$l = strlen( reset( $y ) );
				if ( $model->year ) {
					return $model->year . '年';
				} elseif ( $l != 4 ) {
					return '';
				} else {
					return reset( $y ) . '年';
				}
			},
			'hAlign' => 'center',
			'width' => '70px'
		], [ 'attribute' => 'month',
			'value' => function ( $model ) {
				$t = explode( '年', $model->description );
				$str = end( $t );
				if ( $model->month ) {
					return $model->month . '月';
				} elseif ( preg_match( '/\d+/', $str, $arr ) ) {
					return $arr[ 0 ] . '月';
				} else {
					return '';
				};
			},
			'hAlign' => 'center',
			'width' => '55px'
		],
		//'year',

		[ 'attribute' => 'description',
			'value' => function ( $model ) {
				$d = explode( '份', $model->description );
				return end( $d );
			},
			'class' => 'kartik\grid\EditableColumn',
			'readonly' => function ( $model, $key, $index, $widget ) {
				return ( $model->invoice_status != 0 ); // 判断活动列是否可编辑
			},
			'editableOptions' => [
				'header' => '详情',
				'formOptions' => [ 'action' => [ '/user-invoice/invoice' ] ],
				'inputType' => \kartik\ editable\ Editable::INPUT_TEXT,
				'options' => [
					'pluginOptions' => [ 'min' => 0, 'max' => 5000 ]
				]
			],
			'hAlign' => 'left',
			'width' => ''
		],
		[ 'attribute' => 'invoice_amount',
			'refreshGrid' => 'true',
			'pageSummary' => true,
			'class' => 'kartik\grid\EditableColumn',
			'readonly' => function ( $model, $key, $index, $widget ) {
				return ( $model->invoice_status != 0 ); // 判断活动列是否可编辑
			},
			'editableOptions' => [
				'formOptions' => [ 'action' => [ '/user-invoice/invoice' ] ], // point to the new action        
				'inputType' => \kartik\ editable\ Editable::INPUT_TEXT,
			],
			'hAlign' => 'center',
			'width' => '60px'
		],
		/*['attribute' => 'create_time',
			 'mergeHeader' => true,
			 //'group' => true,
			'format' => ['date','php:Y-m-d H:m:s'],
			 'hAlign' => 'center',
			'width' => '150px'
			],*/
		[ 'attribute' => 'order_id',
			'format' => 'raw',
			'value' => function ( $model ) {
				// $url = Yii::$app->urlManager->createUrl(['order/index1','order_id'=>$model->order_id]);
				return Html::a( $model->order_id, '#', [
					'data-toggle' => 'modal',
					'data-target' => '#update-modal',
					'class' => 'order',
				] );
			},
			//'group' => true,
			'hAlign' => 'center',
			'width' => '115px'
		],
		/*['attribute' => 'invoice_notes',
			'class'=>'kartik\grid\EditableColumn',
			 'editableOptions'=>[
              'formOptions'=>['action' => ['/user-invoice/invoice']], // point to the new action        
              'inputType'=>\kartik\editable\Editable::INPUT_TEXT,
              //'data' => '',
			 ],],*/
		[ 'attribute' => 'payment_time',
			//'group' => true,
		    'value' => function($model){
	        	if(empty($model->payment_time)){
	        		return '';
	        	}else{
	        		return date('Y-m-d H:i:s', $model->payment_time);
	        	}
	        },
			'mergeHeader' => true,
			'hAlign' => 'center',
			'width' => '160px'
		],
		[ 'attribute' => 'invoice_status',
			'value' => function ( $model ) {
				$data = [ '0' => '欠费', '1' => '银行', '2' => '线上', '3' => '刷卡', '4' => '优惠', '5' => '政府', '6' => '现金' ];
				return $data[ $model[ 'invoice_status' ] ];
			},
			//'refreshGrid' => 'true',
			'filterType' => GridView::FILTER_SELECT2,
			'filter' => [ '0' => '欠费', '1' => '银行', '2' => '线上', '3' => '刷卡', '4' => '优惠', '5' => '政府', '6' => '现金' ],
			'filterInputOptions' => [ 'placeholder' => '' ],
			'filterWidgetOptions' => [
				'pluginOptions' => [ 'allowClear' => true ],
			],
			'class' => 'kartik\grid\EditableColumn',
			/*'readonly' => function($model, $key, $index, $widget) {
                 return ($model->invoice_status != 0); // 判断活动列是否可编辑
              },*/
			'editableOptions' => [
				'formOptions' => [ 'action' => [ '/user-invoice/invoice' ] ], // point to the new action        
				'inputType' => \kartik\ editable\ Editable::INPUT_DROPDOWN_LIST,
				'data' => [ '0' => '欠费', '1' => '银行', '2' => '线上', '3' => '刷卡', '4' => '优惠', '5' => '政府', '6' => '现金' ],
			],
			'hAlign' => 'center',
			'width' => ''
		],

		/*['attribute' => 'update_time',
			 'mergeHeader' => true,
			'value'=>
                function($model){
                    return  date('Y-m-d H:i:s',$model->update_time);   //主要通过此种方式实现
                },
			 'hAlign' => 'center',
			'width' => '170px'
			],*/

		//'year',
		//'month',

		/*['class' => 'kartik\grid\ActionColumn',
			'header' => '操作',
			'template' =>'{delete}{view}',
			'width' => '80px'
			],*/
	];
	echo GridView::widget( [
		'dataProvider' => $dataProvider,
		'filterModel' => $searchModel,
		'options' => [ 'id' => 'grid' ],
		//'showFooter' => true,
		'showPageSummary' => true,
		'panel' => [ 'type' => 'primary', 'heading' => '缴费管理',
			'before' => Html::a( '新费项', '#', [
				'data-toggle' => 'modal',
				'data-target' => '#update-modal', 
		       'class' => 'btn btn-success new',
			] ) . ' ' .
			Html::a( '导入', 'import', [
				'data-toggle' => 'modal',
				'data-target' => '#update-modal',
				'class' => 'btn btn-info import',
			] ) . ' ' .
			Html::a( '缴费', "javascript:void(0);", [ 'class' => 'btn btn-default gridviewpay ' ] ),
		],

		'toolbar' => [
			[ 'content' =>
				Html::a( '删除', "javascript:void(0);", [ 'class' => 'btn btn-danger gridviewdelete ' ] ) . ' ' .
				Html::a( '统计', [ 'sum' ], [ 'class' => 'btn btn-success' ] )
			],
			'{toggleData}',
			'{export}'
		],
		'columns' => $gridColumn,
		'hover' => true
	] );
	?>
	<?php Pjax::end(); ?>
</div>