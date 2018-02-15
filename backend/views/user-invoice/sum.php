<?php

use app\models\UserInvoice;
use yii\jui\DatePicker;
use kartik\form\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
?>

<style type="text/css">
    div{
        margin: auto
    }
</style>

<?php $form = ActiveForm::begin([
    'action' => ['search'],
    'method' => 'post',
]); ?>
<table width="500" border="0" align="center">
	<tr>
		<td>
			<?php
			echo $form->field( $model, 'from' )->widget( \yii\ jui\ DatePicker::classname(), [
				'language' => 'zh-CN',
				//'type' => ActiveForm::TYPE_INLINE,
				'dateFormat' => 'yyyy-MM-dd',
			] );
			?>
		</td>
		<td>
			<?php
			echo $form->field( $model, 'to' )->widget( \yii\ jui\ DatePicker::classname(), [
				'language' => 'zh-CN',
				'dateFormat' => 'yyyy-MM-dd'
			] );
			?>
		</td>
		<td>
			<?= Html::submitButton('GOing...', ['class' => 'btn btn-primary']) ?>

		</td>
	</tr>
	<tr>
		<td>
			<?php echo '起始日期：'.date('Y-m-d',  $from); ?>
		</td>
		<td align="right">
			<?php echo '截止日期：'.date('Y-m-d',  $to); ?>
		</td>
	</tr>
</table>
<?php ActiveForm::end(); ?>
     <table width="600" border="1" align="center">
		 <thead>
		 	<tr align="center">
		 		<td>序号</td>
		 		<td>小区</td>
		 		<td>订单/条</td>
		 		<td>计数/条</td>
		 		<td>合计/元</td>
		 	</tr>
		 </thead>
         <tbody>
            <?php  foreach($community as $key => $com): ?>
             <tr>
              <td align="center">
              	<?php  echo $key+1; ?>
              </td>

               <td align="" width="150">
                 <?php  echo $com->community_name; ?>
               </td>

                 <td align="right">
                     <?php  echo $sum = UserInvoice::find()
                         ->select('order_id')
                         ->distinct()
                         ->andwhere(['community_id' => $com->community_id])
                         ->andwhere(['between', 'payment_time', $from,$to])
                         ->count();
                     ?>
               </td>
               <td align="right">
               	  <?php  echo $sum = UserInvoice::find()
	                         ->andwhere(['community_id' => $com->community_id])
	                         ->andwhere(['between', 'payment_time', $from,$to])
	                         ->count(); 
				   ?>
               </td>
               <td align="right">
               	  <?php $sum = UserInvoice::find()
	                         ->andwhere(['community_id' => $com->community_id])
	                         ->andwhere(['between', 'payment_time', $from,$to])
	                         ->sum('invoice_amount');
				   //where('status=:status', [':status' => $status]);
				   if(!$sum){
					   $sum = 0;
				   }
				   echo $sum; 
				   ?>
               </td>
             </tr>
             <tr>
             	
             </tr>
             <?php  endforeach; ?>
                <td colspan="2">
             	</td>
                <td align="center" colspan="2">
                	<strong>合计：</strong>
                </td>
                <td align="right">
                	<?php echo $in; ?>
                </td>
         </tbody>
     </table>