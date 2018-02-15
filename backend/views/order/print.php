<?php

use yii\ helpers\ Html;
use yii\ helpers\ Url;

/* @var $this yii\web\View */
/* @var $model app\models\UserInvoice */

$this->title = '订单详情';
$this->params[ 'breadcrumbs' ][] = [ 'label' => '订单列表', 'url' => [ 'index' ] ];
$this->params[ 'breadcrumbs' ][] = $this->title;
?>

<script>
	function printme() {
		document.body.innerHTML = document.getElementById( 'div1' ).innerHTML + '<br/>';
		window.print();
	}
</script>

<div class="user-order-pay">

	<span id='div1'>
		
		<center>
			<h3>广西裕达物业服务有限公司缴费单</h3>
		</center>
		<table align="center" width="768" border="0">
			<tr>
				<td>
					<?php echo '&nbsp'; ?>
				</td>
				<td><strong>房号:</strong>
					<?php echo $comm['community_name']; echo '&nbsp'; echo $building['building_name'];echo '&nbsp'; echo $r_name['number']; echo '&nbsp'; echo $r_name['name']; ?>
				</td>
				<td align="center">
					<?php echo '支付方式：'.$e[$order['payment_gateway']]; ?>
				</td>
			</tr>
		</table>
		<table width="768" border="1" align="center" cellspacing="0" cellpadding="0">
			<tbody>
				<tr>
					<th>
						<center>序号</center>
					</th>
					<th colspan="3">
						<center>名称</center>
					</th>
					<th>
						<center>应收</center>
					</th>
					<th>
						<center>实收</center>
					</th>
					<th>
						<center>备注</center>
					</th>
				</tr>
				<?php foreach($invoic as $k =>$i): $i = (object) $i?>
				<tr>
					<td width="12%">
						<center>
							<?php echo $k+1; ?>
						</center>
					</td>
					<td align="center" width="9%">
						<?php echo $i->year; ?>年</td>
					<td align="center" width="6%">
						<?php echo $i->month; ?>月</td>
					<td width="30%">
						<?php echo $i->description; ?>
					</td>
					<td width="9%">
						<center>
							<?php echo $i->invoice_amount; ?>
						</center>
					</td>
					<td width="9%">
						<center>
							<?php echo $i->invoice_amount; ?>
						</center>
					</td>
					<td width=""></td>
				</tr>
				<?php endforeach; ?>
				<tr align="right">
					<td colspan="3">
						<center>共：
							<?php echo $n; ?>条</center>
					</td>
					<td colspan="1">活动优惠：
						<?php $Y = '0%'; echo $Y; ?>
					</td>
					<td colspan="2" align="right">合计：</td>
					<td colspan="1">
						<center>
							<?php $c = $m; echo $c; ?>元</center>
					</td>
				</tr>
			</tbody>
		</table>
		<table width="768" border="0" align="center">
			<tr>

			</tr>
			<tr>
				<td>收款人：
					<?php echo $user_name; echo '('.Yii::$app->request->userIP.')'; ?>
				</td>
				<td align="right">
					<?php echo '时间：'.date('Y:m:d H:i:s'); ?>
				</td>
			</tr>
		</table>
	</span>
	<table width="768" border="0" align="center">
		<tr>
			<td align="right"><a href="javascript:printme()" rel="external nofollow" target="_self">打印</a></td>
		</tr>
	</table>
</div>