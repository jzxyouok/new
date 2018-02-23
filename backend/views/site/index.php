<?php

use app\models\UserInvoice;
use yii\jui\DatePicker;
use yii\widgets\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */

$this->title = '裕达物业';
?>
  <style>
	  h1{
		  text-align:center;
		  font-weight:bold;
	  }
	  h4{
		  color: #0DE842;
		  font-size: 24px;
		  text-align:center;
		  font-style: italic;
	  }
	  h5{
		  font-size: 24px;
		  margin-left: 10%;
	  }
	  h{
		  font-size: 20px;
		  color: #C80F12;
	  }
</style>
  
<div style="background-color: #E5F5F3;border-radius: 20px; height: 100vh">
  <br>
   <h1>广西裕达物业服务有限公司</h1>
   <h4>祝您工作愉快！</h4>
   
   <div class="jumbotron">

   </div>

<?php
	if($name == 'admin'){
		echo "<h5>欢迎您，<h>超级管理员！</h></h5>";
	}else{
		echo "<h5>欢迎您，<h>$name ！</h></h5>";
	}
   echo "<br />";
   echo "<h5>您的登录地址是：<h>$a</h></h5>";
?>
</div>