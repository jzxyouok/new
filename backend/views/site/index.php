<?php

use app\models\UserInvoice;
use yii\jui\DatePicker;
use yii\widgets\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = '裕达物业';
?>
  <style>
	  h3{
		  text-align:center;
		  font-weight:bold;
	  }
	  h4{
		  color: #0DE842;
		  font-size: 18px;
		  text-align:center;
		  font-style: italic;
	  }
</style>
  
<div style="background-color: #E5F5F3;border-radius: 20px; height: 100vh">
  <br>
   <h3>广西裕达物业服务有限公司</center>
   <h4>祝您工作愉快！</h4>
   
   <div class="jumbotron">

    </div>

<?php
   $a = Yii::$app->request->userIP;
   echo '用户名：'.$_SESSION['user']['name'].'<br />';
   echo 'IP地址：'.$a;
   
?>
</div>