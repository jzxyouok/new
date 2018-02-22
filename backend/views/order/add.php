<?php

use yii\ helpers\ Html;
use yii\ helpers\ Url;

/* @var $this yii\web\View */
/* @var $model app\models\UserInvoice */

$this->title = '订单详情';
$this->params[ 'breadcrumbs' ][] = [ 'label' => '订单列表', 'url' => [ 'index' ] ];
$this->params[ 'breadcrumbs' ][] = $this->title;
?>

<div class="user-order-pay">

	<style type="text/css">
		th{
			text-align:center;
		}
		
		h3{
			text-align:center;
		}
		
		table{
			text-align:center;
			margin:auto;
		}
		</style>
		
		<!-- 传过来 $invoice查询原数据 $in 费项单价，$m 费项总和，$des费项描述 -->
		
			<h3>广西裕达物业服务有限公司缴费单</h3>
			
		<table width="768" border="0">
			<tr>
				<td align="left"><strong>房号:</strong>
					<?php echo $comm; echo '&nbsp'; echo $building;echo '&nbsp'; echo $number; echo '&nbsp'; echo $name; ?>
				</td>
			</tr>
		</table>
		<table width="768" border="1" cellspacing="0" cellpadding="0">
			<tbody>
				<tr>
					<th>序号</th>
					<th colspan="3">详情</th>
					<th>应收</th>
					<th>实收</th>
					<th>备注</th>
				</tr>
				<?php foreach($invoic as $k =>$i): $i = (object) $i?>
				<tr>
					<td width="12%"><?php echo $k+1; ?></td>
					<td width="9%"><?php echo $i->year; ?>年</td>
					<td width="6%"><?php echo $i->month; ?>月</td>
					<td width="30%" align="left"><?php echo $i->description; ?></td>
					<td width="9%"><?php echo $i->invoice_amount; ?></td>
					<td width="9%"><?php echo $i->invoice_amount; ?></td>
					<td width=""></td>
				</tr>
				<?php endforeach; ?>
				<tr>
					<td colspan="3">共：<?php echo $n; ?>条</td>
					<td align="right">活动优惠：<?php $Y = '0%'; echo $Y; ?></td>
					<td colspan="2" align="right">合计：</td>
					<td colspan="1"><?php $c = $m; echo $c; ?>元</td>
				</tr>
			</tbody>
		</table>
		<table width="768">
			<tr>
				<td align="left">收款人：<?php echo $user_name; echo '('.Yii::$app->request->userIP.')'; ?></td>
				<td align="right"><?php echo '时间：'.date('Y:m:d H:i:s'); ?></td>
			</tr>
		</table>

	<table width="768" border="0">
		<tr>
			<td colspan="3"><a href="<?=Url::to(['/order/add','c' => $c,
																'user_name' => $user_name,
																'user_id' => $user_id,
															    'r_id' => $r_id,
																'ids' => $ids,
															    ]); ?>"><h2>GOing...</h2></a>
			</td>
		</tr>
	</table>
</div>