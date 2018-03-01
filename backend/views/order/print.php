<?php

use yii\ helpers\ Html;
use yii\ helpers\ Url;
use app\models\UserInvoice;
use app\models\CostName;
use app\models\CostRelation;

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
			
			tr{
				height:27px;
			}
        	table{
        		text-align: center;
        		margin:auto;
        		width: 768px;
        	}
        </style>
        
		<h3><?php echo $comm['community_name'];  ?></h3>
		
			
		<table border="0">
			<tr>
				<td align="left"><strong>房号:</strong>
					<?php echo $building['building_name'].'&nbsp'. $r_name['number']. '&nbsp'. $r_name['name']; ?>
				</td>
				<td align="center"><?php echo '业主姓名：'.$r_name['n']?></td>
				<td align="center"><?php echo '订单号：'.$order_id ?></td>
				<td align="right"><?php echo '收款方式：'.$e[$order['payment_gateway']]; ?></td>
			</tr>
			<tr style="height: 5px;">
				<td></td>
			</tr>
		</table>
		<table border="1" cellspacing="0" cellpadding="0">
			<tbody>
				<tr>
					<th>序号</th>
					<th>项目</th>
					<th>上期读数</th>
					<th>本期读数</th>
					<th>数量</th>
					<th>单价</th>
					<th colspan="2">起始日期</th>
					<th>金额</th>
					<th>备注</th>
				</tr>
				<?php 
				$k = 0;
				foreach($dc as $key => $c){
			    	$ds = UserInvoice::find()
			    		->select('description,year,month,invoice_amount')
			    		->andwhere(['order_id'=>$order_id])
						->andwhere(['realestate_id' => $r_name['id']])
			    		->andwhere(['description' => $c]);
			    	$des = $ds->asArray()->All();
					
					$c_id = CostRelation::find()->select('cost_id')->where(['realestate_id' => $r_name['id']])->asArray()->all();
	                $cost = CostName::find()->select('price')->andwhere(['cost_name' => $c])->andwhere(['in', 'cost_id', $c_id])->asArray()->one();
					foreach($cost as $name);
					
	                $k ++;//记录遍历次数
					$y = $ds->min('year'); // 最小年
					$Y = $ds->max('year'); // 最大年
					$m = $ds->where(['year' => $y])->asArray()->all(); // 最小月
					$h = min(array_column($m,'month')); //提取月份
					$M = $ds->where(['year' => $Y])->asArray()->all();//->max('month'); // 最大月
					$H = max(array_column($M,'month')); //提取月份
					$count = count($des);
					
			    	$amount = array_sum(array_column($des,'invoice_amount'));
	                echo ("<tr height=10>
	                          <td width = 5%>$k</td>
							  <td width = 15%>$c</td>
							  <td width = 10%></td>
							  <td width = 10%></td>
							  <td width = 6%>$count </td>
	                          <td width = 5%>$name</td>
	                          <td width = 8%>$y/$h</td>
	                          <td width = 8%>$Y/$H </td>
	                          <td width = 7% align = right>$amount</td>
	                          <td>预收:$Y/$H/-$Y/$H</td>
	                       </tr>
	                	  ");
			    }?>
								
				<tr>
					<td colspan="7" align="left">
					<table style="width: 450px;">
                         <tbody>
                           <tr style="height: 20px">
                             <td align="left">合计：   <?php echo $i_a; ?></td>
							   <td align="right"><?php echo ucwords($i_a) ?></td>
                           </tr>
                         </tbody>
                   </table>

					</td>
					<td colspan="3"></td>
				</tr>
			</tbody>
		</table>
		<table border="0">
			<tr style="height: 5px;">
				<td></td>
			</tr>
			<tr>
				<td align="left" width = "256px">收款人：<?php echo $user_name; echo '('.Yii::$app->request->userIP.')'; ?></td>
				<td width = "256px"><?php echo '时间：'.date('Y-m-d H:i:s', $order['payment_time']) ?></td>
				<td align="right" width = "256px">广西裕达集团物业服务有限公司(盖章)</td>
			</tr>
		</table>
	</span>
	<table>
		<tr>
			<td>
			<br />
				<a href="javascript:printme()" rel="external nofollow" target="_self"><image src='/image/print.png' width = '10%' height = '10%'></image></a>
			</td>
		</tr>
	</table>
</div>