<?php

use yii\helpers\Html;
use kartik\grid\GridView;
use yii\helpers\ArrayHelper;
use app\models\UserAccount;
use app\models\CommunityBasic;
use app\models\Status;

/* @var $this yii\web\View */
/* @var $searchModel app\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '用户管理';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-account-index">

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

       <?php
	     $gridColumn = [     
            ['class' => 'kartik\grid\SerialColumn',
			'header' =>'序<br />号'],

	        ['attribute' => 'community_name',
			 'hAlign' => 'center',
			 'filterType'=>GridView::FILTER_SELECT2,
			 'filter' => CommunityBasic::find() 
			          -> select(['community_name'])
			          -> orderBy('community_name')
			          -> indexBy('community_name')
			          -> column(),
			 'filterInputOptions'=>['placeholder'=>'请选择'],
			 'filterWidgetOptions'=>[
                'pluginOptions'=>['allowClear'=>true],
             ],
			 // 'hidden'=>true,
			'label' => '小区',
			 //'group' =>true,
			'width' => '150px'],
	    	['attribute' => 'building_name',
	    	 'hAlign' => 'center',
			 'width'=>'80px',
	    	 'label' => '楼宇'],
	    	['attribute' => 'room_name',
	    	 'hAlign' => 'center',
			 'width'=>'80px',
	    	 'label' => '单元'],
	    	['attribute' => 'room_number',
	    	 'hAlign' => 'center',
			 'width'=>'80px',
	    	 'label' => '房号'],
	    	['attribute' => 'real_name',
	    	 'hAlign' => 'center',
	    	 'label' => '姓名'],
	    	 
	    	['attribute' => 'mobile_phone',
	    	 'hAlign' => 'center',
	    	 'label' => '手机号码'],
	    	['attribute' => 'reg_time',
	    	 'format' =>['date','php:Y-m-d H:i:s'],
	    	 'hAlign' => 'center',
	    	 'label' => '注册时间'],
	    	['attribute' => 'account_role',
			 'filterType' => GridView::FILTER_SELECT2,
	    	 'filter' => [0=> '业主', 1=>'物业'],
		     'filterInputOptions'=>['placeholder'=>'请选择'],
			 'filterWidgetOptions'=>[
                             'pluginOptions'=>['allowClear'=>true],
		                 ],
		   'hAlign' => 'center',
		   /* 'value' => function($model) {
			$role = [0=>'业主',1 => '物业'];
                      return $dataProvider->account_role;
                      },*/
		  	'label' => '角色'],
		    ['attribute' => 'name',
		   'hAlign' => 'center',
		   'filterType'=>GridView::FILTER_SELECT2,
		   'filter' => Status::find()
		            -> select(['name'])
		            -> where(['property'=>'1'])
		            -> orderBy('name')
	                -> indexBy('name')
	                -> column(),
			 'filterInputOptions'=>['placeholder'=>'请选择'],
			 'filterWidgetOptions'=>[
                'pluginOptions'=>['allowClear'=>true],
             ],
			'label' => '状态'],

            /*['class' => 'kartik\grid\CheckBoxColumn',
			 'name'=>'id',
			 'template' => '{delete}',
			'header' => '操<br />作'],*/
        ];
	     echo GridView::widget([
			'options' =>['id'=>'grid'],
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
			'panel' => ['type' => 'primary','heading' => '用户管理'],
            'columns' => $gridColumn,
			 'hover' => true
        ]); 	
	?>
   
</div>
