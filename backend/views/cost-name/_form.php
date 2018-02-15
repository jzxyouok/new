<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\CommunityBasic;
use app\models\CostName;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\CostName */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="cost-name-form">
    
    <?php 
	   $Array1 = Yii::$app->db->createCommand('select cost_id, cost_name from cost_name where parent =0')->queryAll();
	   $comm = ArrayHelper::map($Array1,'cost_name','cost_name');
	
	   $cost_name = CostName::find()->select('cost_name,cost_id')->where(['level' => 0])->asArray()->all();
	   $cost = ArrayHelper::map($cost_name,'cost_id','cost_name');
	   $c = array_merge(['0' => '父级'],$cost);
	?>

    <?php $form = ActiveForm::begin(); ?>
<table border="0" align="center" style="border-radius: 20px; background-color: #F3F3F3;">
	<tbody>
		<tr>
			<td>

			</td>
			<tr>
				<td width="5%"></td>
				<td>
					<div class="row">
						<div class="col-lg-4">
							<?php //$_SESSION[ 'user' ][ 'role' ] == 1 |||| $_SESSION[ 'user' ][ 'role' ] == 10 
			                if (  $_SESSION[ 'user' ][ 'role' ] == 14 && empty($model['cost_name']) ) {
			                	//父级费项编辑框  财务专用
			                	echo $form->field( $model, 'cost_name' )->textInput();
			                }elseif($_SESSION[ 'user' ][ 'role' ] == 14 & strlen($model['cost_name']) >3) {
			                	//子级费项编辑框
			                	echo $form->field( $model, 'cost_name' )->dropDownList( $comm, [ 'prempt' => '请选择' ] );
			                }elseif($_SESSION[ 'user' ][ 'role' ] != 14 & $model['level'] == 0){
			                	echo "<script>alert('权限不足，请返回')</script>";
    		                    exit;
			                }else{
			                	echo $form->field( $model, 'cost_name' )->dropDownList( $comm, [ 'prempt' => '请选择' ] );
			                }
			                ?>
						</div>
						<div class="col-lg-4">
							<?php 
			                if($model['level'] == 0 & $_SESSION[ 'user' ][ 'role' ] == 14){
			                	echo $form->field($model,'level')->dropDownList(['父级','子级']);
			                }else{
			                	echo $form->field($model, 'level')->dropDownList([1 => '子级'],['prempt' =>'请选择']);
			                }
			                ?>
						</div>
						<div class="col-lg-4">
							<?php 
			                if($_SESSION[ 'user' ][ 'role' ] == 14 && empty($model['cost_name'])){
			                	//添加父级费项框  财务专用
			                	echo $form->field($model, 'parent')->dropDownList($c,['prempt' =>'请选择']);
			                }elseif($_SESSION[ 'user' ][ 'role' ] == 14 & $model['level'] == 0){
			                	//父级费项编辑框  财务专用
			                	echo $form->field($model, 'parent')->dropDownList(['无']);
			                }else{
			                	echo $form->field($model, 'parent')->dropDownList($cost,['prempt' =>'请选择']);
			                }
			             ?>
						</div>
					</div>

					<div class="row">
						<div class="col-lg-4">
							<?= $form->field($model, 'price')->textInput(['maxlength' => true]) ?>
						</div>
						<div class="col-lg-4">
							<?= $form->field($model, 'inv')->dropDownList(['否','是'],['length' => 1]) ?>
						</div>
					</div>

					<div class="row">
						<div class="col-lg-12">
							<?= $form->field($model, 'property')->textArea(['maxlength' => true]) ?>
						</div>
					</div>

					<div class="form-group" align="center">
						<?= Html::submitButton($model->isNewRecord ? '创建' : '更新', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
					</div>
				</td>
				<td width="4%"></td>
			</tr>
	</tbody>
</table>
    <?php ActiveForm::end(); ?>

</div>
