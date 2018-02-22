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
		<style>
        	h3{
        		text-align: center;
        	}
           th{
           		text-align:center;
           	}
            
        	table{
        		text-align: center;
        		margin:auto;
        		width: 768px;
        	}
        </style>
        
			<h3>广西裕达物业服务有限公司缴费单</h3>
			
		<table>
			<tr>
				<td align="left" colspan="2"><strong>房号:</strong>
					<?php echo $comm['community_name']; echo '&nbsp'; echo $building['building_name'];echo '&nbsp'; echo $r_name['number']; echo '&nbsp'; echo $r_name['name']; ?>
				</td>
				<td align="right">
					<?php echo '支付方式：'.$e[$order['payment_gateway']]; ?>
				</td>
			</tr>
		</table>
		<table border="1" cellspacing="0" cellpadding="0">
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
					<td width="12%">
						<center>
							<?php echo $k+1; ?>
						</center>
					</td>
					<td width="9%">
						<?php echo $i->year; ?>年</td>
					<td width="6%">
						<?php echo $i->month; ?>月</td>
					<td width="30%" align="left">
						<?php echo $i->description; ?>
					</td>
					<td width="9%">
							<?php echo $i->invoice_amount; ?>
					</td>
					<td width="9%">
							<?php echo $i->invoice_amount; ?>
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
							<?php $c = $m; echo $c; ?>元
					</td>
				</tr>
			</tbody>
		</table>
		<table>
			<tr>
				<td align="left">收款人：
					<?php echo $user_name; echo '('.Yii::$app->request->userIP.')'; ?>
				</td>
				<td align="right">
					<?php echo '时间：'.date('Y:m:d H:i:s'); ?>
				</td>
			</tr>
		</table>
	</span>
	<table>
		<tr>
			<td>
				<a href="javascript:printme()" rel="external nofollow" target="_self"><image src='/image/print.png' width = '10%' height = '10%'></image></a>
			</td>
		</tr>
	</table>
</div>