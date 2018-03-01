<?php

namespace backend\controllers;

use Yii;
use app\models\OrderBasic;
use app\models\UserInvoice;
use app\models\CommunityBasic;
use app\models\CommunityBuilding;
use app\models\CommunityRealestate;
use app\models\OrderRelationshipAddress;
use app\models\OrderSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use kartik\grid\EditableColumnAction;

/**
 * OrderController implements the CRUD actions for OrderBasic model.
 */
class OrderController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }
	
	//检查用户是否登录
	public function  beforeAction($action)
    {
        if(Yii::$app->user->isGuest){
            $this->redirect(['/login']);
            return false;
        }
        return true;
    }

    /**
     * Lists all OrderBasic models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new OrderSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
	
	//来自缴费页面订单查询
	public function actionIndex1($order_id)
    {
        $searchModel = new OrderSearch();
		$searchModel -> order_id = $order_id;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

	public function actions()
   {
       return ArrayHelper::merge(parent::actions(), [
           'order' => [                                       // identifier for your editable action
               'class' => EditableColumnAction::className(),     // action class name
               'modelClass' => OrderBasic::className(),                // the update model class
               'outputValue' => function ($model, $attribute, $key, $index) {
               },
               'ajaxOnly' => true,
           ]
       ]);
   }
	
	//打印信息
	public function actionPrint($order_id)
	{
		$session = Yii::$app->session;
		$user_id = $_SESSION['user']['community']; //小区
		$user_name = $_SESSION['user']['name']; //用户名
		
		//获取订单信息
		$order = OrderBasic::find()
			->select('order_id,payment_time,payment_gateway,payment_time')
			->where(['order_id' => $order_id])
			->asArray()
			->one();
		 
		if($order['payment_time'] || $order['payment_gateway']){
			//查询缴费费项信息
			$i = UserInvoice::find()->select('community_id,building_id,realestate_id,description,year,month,invoice_amount')->where(['order_id'=>$order_id]);
			$invoice = $i->asArray()->all();
			
		    $in = array_column($invoice, 'invoice_amount');// 选择费项金额列
		    $de = array_column($invoice, 'description');// 选择费详情列
		    $i_a = array_sum($in);  //费项总和
		    $n = count($in); //费项条数
			$dc = array_unique($de);//去重复
									
			if($invoice){
				$inv = $i->asArray()->one();
				//查询小区
				$comm = CommunityBasic::find()
					->select('community_name')
					->where(['community_id' => $inv['community_id']])
					->asArray()
					->one();
				
				//查询楼宇名称
				$building = CommunityBuilding::find()
					->select('building_name')
					->where(['building_id' => $inv['building_id']])
					->asArray()
					->one();
				//查询房屋单元及房号
				$r_name = CommunityRealestate::find()
					->select('room_name as name, realestate_id as id, room_number as number, owners_name as n')
					->where(['realestate_id' => $inv['realestate_id']])
					->asArray()
					->one();
				$e = [ 1 => '支付宝', 2 => '微信', 3 => '刷卡', 4 => '银行', '5' => '政府', 6 => '现金' ];
				return $this->render('print',[
			                      'dc' => $dc,
			                      'comm' => $comm,
			                      'building' => $building,
			                      'r_name' => $r_name,
					              'order_id' => $order_id,
			                      'n' => $n,
			                      'i_a'=> $i_a,
					              'e' => $e,
					              'order' => $order,
			                      'user_id' => $user_id,
			                      'user_name' => $user_name]);
			}else{
				$session->setFlash('m','1');
				return $this->redirect(Yii::$app->request->referrer);
			}		 
		}else{
			$session->setFlash('m','2');
			return $this->redirect(Yii::$app->request->referrer);
		}
	}
	
    /**
     * Displays a single OrderBasic model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
		$model = OrderBasic::find()->where(['id' => $id])->asArray()->one();
		$a_id = $model['account_id'];
		$o_id = $model['order_id'];
		$len = strlen($a_id);
		
		if($len < 10){
			$ad = CommunityBasic::find()
				->select('community_name as ad')
			    ->where(['community_id' => $a_id])
				->asArray()
			    ->one();
		}else{		
		    $ad = OrderRelationshipAddress::find()
		    	->select('address as ad')
		    	->where(['order_id' => $o_id])
		    	->asArray()
		    	->one();
		}
		
		$model = array_merge($ad,$model);
		
        return $this->renderAjax('view', [
            'model' => $model,
        ]);
    }
	
	//生成订单后转跳到这
	public function actionView1($order_id)
    {
		$model = OrderBasic::find()
			//->select('id,account_id')
			->where(['order_id' => $order_id])
			->asArray()
			->one();
		
		$c_id = $model['account_id'];
		$ad = CommunityBasic::find()
				->select('community_name as ad,community_id')
			    ->where(['community_id' => $c_id])
				->asArray()
			    ->one();
		
		$model = array_merge($model,$ad);

		return $this->render('view', [
            'model' => $model,
        ]);
	}
	
	//接收微信支付二维码
	public function actionWx($url)
	{
		//生成支付二维码
		$img = '<img src=http://paysdk.weixin.qq.com/example/qrcode.php?data='.urlencode($url).' style="width:300px;height:300px;"/>';
		
		return $this->render('wx',[
			'img' => $img
		]);
	}

	//缴费成功后转到这里
	public function actionV($out_trade_no)
	{
		$model = OrderBasic::find()
			//->select('id,account_id')
			->where(['order_id' => $out_trade_no])
			->asArray()
			->one();
		return $this->render('view', [
            'model' =>$model,
        ]);
	}
	
    /**
     * Creates a new OrderBasic model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new OrderBasic();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
	
	//确认缴费详情
	public function actionAffirm()
    {
		$id = Yii::$app->request->get();
		foreach($id as $k => $s)
		{
		    //$i 为费项编号
		    foreach($s as $ids){
				//获取缴费项目
		    	$invoice = UserInvoice::find()->select('realestate_id, community_id, building_id, invoice_id,year,month,invoice_amount,description')
		        	->andwhere(['invoice_id' => $ids,'invoice_status' => '0'])
		    		->limit(100)
		        	->asArray()
		        	->all();
				if(empty($invoice)){
					$session = Yii::$app->session;
					$session->setFlash('fail','4');
			        return $this->redirect( Yii::$app->request->referrer );
				}else{
			    	//获取房屋
			    	foreach($invoice as $inv);
			    	//$i_id = $inv['invoice_id']; //费项编号
			    	$r_id = $inv['realestate_id']; //房屋编号
			    	
			    	$realestate = CommunityRealestate::find()  //获取单元和房号
			    		->select('room_number as number,room_name as name')
			    		->where(['realestate_id' => $r_id])
			    		->asArray()
			    		->one();
			    	
			    	$com = $inv['community_id']; //小区编号
			    	$community = CommunityBasic ::find()
			    		->select('community_name')
			    		->where(['community_id' => $com])
			    		->asArray()
			    		->one();
			    	foreach($community as $comm);//小区名称
			    	
			    	$bui = $inv['building_id']; //楼宇名称
			    	$build = CommunityBuilding::find()
			    		->select('building_name')
			    		->where(['building_id' => $bui])
			    		->asArray()
			    		->one();
			    	foreach($build as $building);
			    	
			    	$number = $realestate['number'];echo "<hr />";//房屋单元
			    	$name = $realestate['name']; //房号
		        }
		    }
	    }
 
		// 选择费项合计金额
		$in = array_column($invoice, 'invoice_amount');
		$m = array_sum($in);
		$n = count($in);
		
		$user_id = $_SESSION['user']['community']; //小区
		if(empty($user_id)){
			$user_id = 0;
		}
		$user_name = $_SESSION['user']['name']; //用户名
		
		return $this->render('add', [
			    'invoic' => $invoice,
			    'comm' => $comm,
			    'building' => $building,
			    'number' => $number,
			    'name' => $name,
			    'n' => $n,
			    'm'=> $m,
			    'ids' => $ids,
			    'r_id' => $r_id,
			    'user_id' => $user_id,
			    'user_name' => $user_name,
            ]);
    }

	public function actionAdd($c, $user_id,$r_id,$user_name)
	{
		$a = Yii::$app->request->get();
		
		foreach($a as $b);//print_r($a);exit;
		
		//随机产生12位数订单号，格式为年+月+日+1到999999随机获取6位数
		$order_id = date('ymd').str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
		$time = date(time());//订单类型
		$des = '物业相关费用'; //订单描述
		$phone = $_SESSION['user']['phone'];
		
		if($user_id != 0){
			$transaction = Yii::$app->db->beginTransaction();
			try{
				$sql = "insert into order_basic(account_id,order_id,create_time,order_type,description, order_amount)
				values ('$user_id','$order_id','$time','1','$des','$c')";
				$result = Yii::$app->db->createCommand($sql)->execute();
				if($result){
					foreach($b as $d){
						$sql1 = "insert into order_products(order_id,product_id,product_quantity)value('$order_id','$d','1')";
						$result1 = Yii::$app->db->createCommand($sql1)->execute();
					}
					if($result1){
						$sql2 = "insert into order_relationship_address(order_id,address,mobile_phone,name)
						value('$order_id','$r_id', '$phone','$user_name')";
						$result2 = Yii::$app->db->createCommand($sql2)->execute();
					}
				}
				$transaction->commit();
			}catch(\Exception $e) {
			print_r($e);die;
            $transaction->rollback();
            return $this->redirect(Yii::$app->request->referrer);
        }
			$model = new OrderBasic;
			$model ->order_id = $order_id;
        return $this->redirect(['view1', 'order_id'=>$order_id]); //跳到支付通道选择页面
		}
		$m ='非收银员没有收费权限，请返回！';
		echo "<script> alert('$m');parent.location.href='/user-invoice'; </script>";
	}
		
    /**
     * Updates an existing OrderBasic model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing OrderBasic model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the OrderBasic model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return OrderBasic the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = OrderBasic::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
