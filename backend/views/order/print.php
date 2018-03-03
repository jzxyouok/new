<?php

use yii\ helpers\ Html;
use yii\ helpers\ Url;
use app\models\UserInvoice;
use app\models\CostName;
use app\models\CostRelation;
use app\models\WaterMeter;

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
        		width: 800px;
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
						->andwhere(['description' => $c])
						->andwhere(['realestate_id' => $r_name['id']])
						->orderBy('year,month ASC');
					$s = $ds->asArray();
					foreach ($s->batch(100) as $des);
					
					//获取绑定水费编码
					$c_id = CostRelation::find()->select('cost_id')->where(['realestate_id' => $r_name['id']])->asArray()->all();
	                //获取绑定费项价格
					if(!empty($c_id)){
						$cost = CostName::find()->select('price')->andwhere(['cost_name' => $c])->andwhere(['cost_id' => $c_id])->asArray()->one();
						foreach($cost as $name);
					}else{
						$name = '';
					}
										
	                $k ++;//记录遍历次数
					
					$y = $ds->min('year'); // 最小年
					$Y = $ds->max('year'); // 最大年
					
					$m = reset($des); // 最小月
					$h = $m['month'];//提取月份
					$M = end($des); // 最大月
					$H = $M['month']; //提取月份
					$count = count($des); //统计数量
										
					//$day = cal_days_in_month(CAL_GREGORIAN, $H, $Y); //获取最后月的天数
					$day = date("t",strtotime("$Y-$H"));
					
					//预交信息
					$ys = date('Y',$order['payment_time']);
					$ms = date('m',$order['payment_time'])+0;
										
					$yj = $ds->andwhere([ '>=', 'year', $ys])
						->orderBy('year,month ASC')
						->asArray()
						->all();
					
					$yc = count($yj); //预交数量统计
					
					$f = reset($yj); //第一条
					$l = end($yj); //后一条
					
					$yy = $f['year']; //预交起始年
					$ym = $f['month']; //预交起始月
					
					$YS = $l['year']; //预交末年
					$YM = $l['month']; //预交末月
					
			    	$amount = array_sum(array_column($des,'invoice_amount')); //费项金额
					
					echo ("<tr height=10>
	                          <td width = 5%>$k</td>
							  <td width = 15%>$c</td>
							  ");
					
					if($c == '水费'){
					    //获取费表读数
					    $readout = WaterMeter::find()
					    	->select('readout')
					    	->where(['realestate_id' => $r_name['id']])
					    	->limit(2)
					    	->orderBy('year,month DESC')
					    	->asArray()
					    	->all();
					    if($readout){
							$read = array_column($readout,'readout'); //提取水费读数
					        $one = reset($read);
					        $two = end($read);
					        $d = $one-$two; //水费差值
						}else{
							$one = '';
							$two = '';
						}
					    
					    echo ("
					            <td width = 9%>$two</td>
					            <td width = 9%>$one</td>
					    		<td width = 6%>$d </td>
					         ");
					}else{
						echo ("
						    <td width = 9%></td>
						    <td width = 9%></td>
							<td width = 6%>$count</td>
						");
					}
					echo (" 
	                        <td width = 5%>$name</td>
	                        <td width = 9%>$y/$h/1</td>
	                        <td width = 10%>$Y/$H/$day</td>
	                        <td width = 7% align = right>$amount</td>
						  ");
					if(isset($yy)){
						echo ("<td>预收:$yy/$ym/-$YS/$YM 共:$yc 条</td></tr>");
					}else{
						echo ("<td></td></tr>");
					}
			    }?>
								
				<tr>
					<td colspan="9" align="right">合计：  &nbsp;&nbsp;&nbsp;<?php echo $i_a; ?></td>
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