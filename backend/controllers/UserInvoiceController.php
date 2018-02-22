<?php

namespace backend\ controllers;

use Yii;
use app\models\UserInvoice;
use app\models\Up;
use yii\web\UploadedFile;
use app\models\OrderBasic;
use app\models\CommunityBasic;
use app\models\CommunityBuilding;
use app\models\CommunityRealestate;
use app\models\CostName;
use app\models\CostRelation;
use app\models\UserInvoiceSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use kartik\grid\EditableColumnAction;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;

/**
 * UserInvoiceController implements the CRUD actions for UserInvoice model.
 */
class UserInvoiceController extends Controller {
	/**
	 * @inheritdoc
	 */
	public function behaviors() 
	{
		return [
			'verbs' => [
				'class' => VerbFilter::className(),
				'actions' => [
					'delete' => [ 'POST' ],
				],
			],
		];
	}

	/**
	 * Lists all UserInvoice models.
	 * @return mixed
	 */
	public function actionIndex() {
		$searchModel = new UserInvoiceSearch();
		$dataProvider = $searchModel->search( Yii::$app->request->queryParams );

		return $this->render( 'index', [
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
		] );
	}
	
	//检查用户是否登录
	public function beforeAction( $action ) 
	{
		if ( Yii::$app->user->isGuest ) {
			$this->redirect( [ '/login' ] );
			return false;
		}
		return true;
	}

	//下载业主资料模板
	public function actionDownload()
    {
        return \Yii::$app->response->sendFile('./uplaod/template/invoice.xlsx');
    }
	
	//Excel文件导入
	public function actionImport() {
		$model = new Up();
		//echo '代码运行正常，请继续编写';
		return $this->renderAjax( 'impo', [
			'model' => $model,
		] );
	}

	//接收并导入excel文件
	public function actionRead() 
	{
		$model = new Up();
		$session = Yii::$app->session;
		ini_set( 'memory_limit', '2048M' ); 
		
		if ( Yii::$app->request->isPost ) {
			$model->file = UploadedFile::getInstance( $model, 'file' );
			$name = $_FILES[ 'Up' ][ 'name' ][ 'file' ]; //保存文件名
			$g = pathinfo( $name, PATHINFO_EXTENSION );

			$n = date(time()).".$g";//新文件名
			//判断文件格式，确定是否上传
			if ( $g == 'xls' || $g == 'xlsx' ) {
				if ( $model->upload() ) {
					$path = "./uplaod/$name"; //保存的文件路径
					rename("uplaod/$name","uplaod/$n"); //修改文件名称
					if ( $g == 'xls' ) { // 判断文件格式
						$inputFileType = 'Xls';
					} elseif ( $g == 'xlsx' ) {
						$inputFileType = 'Xlsx';
					} else {
						echo '文件类型错误';
						exit;
					}

					$inputFileName = "uplaod/$n"; //文件路径
					$sheetname = 'Sheet1'; //设置工作表名称
					$reader = IOFactory::createReader( $inputFileType ); //读取Excel文件

					$reader->setReadDataOnly( true );
					$reader->setLoadSheetsOnly( $sheetname );
					$spreadsheet = $reader->load( $inputFileName );

					$sheetData = $spreadsheet->getActiveSheet()->toArray( null, true, true, true );
					unset( $sheetData[ '1' ] ); //去掉表头
				}

				//查找费项信息
				if ( $sheetData ) {
					foreach ( $sheetData as $sheet ) {
						
						if(count($sheet) != 9){
							$session->setFlash('fail','3');
							unlink($inputFileName);
							return $this->redirect( Yii::$app->request->referrer );
						}
						
						//获取小区
						$c_id = CommunityBasic::find()->select( 'community_id' )->where( [ 'community_name' => $sheet[ 'A' ] ] )->asArray()->one();
						if ( $c_id ) {
							//获取楼宇
							$b_id = CommunityBuilding::find()->select( [ 'building_id' ] )
								->andwhere(['community_id' => $c_id['community_id']])
								->andwhere( [ 'community_building.building_name' => $sheet[ 'B' ] ] )			->asArray()
								->one();
							
							if ( $b_id ) {
								//获取房号
								$r_id = CommunityRealestate::find()->select( [ 'realestate_id' ] )
									->andwhere(['community_id' => $c_id['community_id']])
									->andwhere( [ 'building_id' => $b_id[ 'building_id' ] ] )
									->andwhere( [ 'community_realestate.room_name' => $sheet[ 'C' ]] )
									->asArray()
									->one();
				
								//获取费项
								if ( $r_id ) {
									//获取费项
									$cost_id = CostName::find()->select( 'cost_id,cost_name' )->where( [ 'cost_name' => $sheet[ 'F' ] ] )->asArray()->one();
									if ( $cost_id ) {
										$y = (int)$sheet[ 'D' ];  //将年份转换为整数型，保证数据的单一性
									    $m = (int)$sheet[ 'E' ]; //将月份转换为整数型，保证数据的单一性
									    $d = $sheet[ 'F' ];
									    $price = (float)$sheet[ 'G' ]; //将金额数据进行处理，除了保证数据的单一性外只保留两位数
									    $f = date( time() );
									    $c = $c_id[ 'community_id' ];
									    $b = $b_id[ 'building_id' ];
									    $r = $r_id[ 'realestate_id' ];
										
										//插入数据库
										$transaction = Yii::$app->db->beginTransaction(); //事务开始标记
										try {
											$sql = "insert ignore into user_invoice(community_id,building_id,realestate_id,description, year, month, invoice_amount,create_time,invoice_status)
									values ($c,$b, $r, '$d', $y, $m, $price,$f,'0')";
											$result = Yii::$app->db->createCommand( $sql )->execute();
											$transaction->commit(); //事务提交标记

										} catch ( \Exception $e ) {
											print_r( $e );
											exit;
											$transaction->rollback(); // 事务滚回标记
											return $this->redirect( Yii::$app->request->referrer ); //返回请求页面
										}
									} else {
										echo "<script>alert('费项有误，请修改')</script>";
										return $this->redirect( Yii::$app->request->referrer ); //返回请求页面
									}
								} else {
									echo "<script>alert('房号为空，请修改数据')</script>";
									unlink($inputFileName);
									return $this->redirect( Yii::$app->request->referrer ); //返回请求页面
								}
							} else {
								echo "<script>alert('楼宇为空，请修改数据')</script>";
								unlink($inputFileName);
								return $this->redirect( Yii::$app->request->referrer ); //返回请求页面
							}
						} else {
							echo "<script>alert('小区为空，请修改数据')</script>";
							unlink($inputFileName);
							return $this->redirect( Yii::$app->request->referrer ); //返回请求页面
						}
					}
				} else {
					return $this->redirect( Yii::$app->request->referrer ); //返回请求页面
				}
			}else{
				$session->setFlash('fail','2');
				return $this->redirect( Yii::$app->request->referrer ); //返回请求页面
			}
		}
		unlink($inputFileName);
		return $this->redirect( Yii::$app->request->referrer);
	}

	//批量删除
	public function actionDel() 
	{
		//$this->enableCsrfValidation = false; //去掉yii2的post验证
		$ids = Yii::$app->request->post();

		if ( $_SESSION[ 'user' ][ 'name' ] != 'admin' ) {
			return $this->redirect( Yii::$app->request->referrer );
		} else {
			foreach ( $ids as $id );
			//删除代码
			foreach ( $id as $i ) {
				$this->findModel( $id )->delete();
			}
		}

		return $this->redirect( Yii::$app->request->referrer );//返回请求页面
	}

	//缴费
	public function actionPay() {
		$id = Yii::$app->request->post();

		if ( empty( $id ) ) {
			$session = Yii::$app->session;
			$session->setFlash('fail','1');
			return $this->redirect( Yii::$app->request->referrer );
		} else {
			return $this->redirect( [ 'order/affirm', 'id' => $id ] );
		}
	}

	//缴费统计列表
	public function actionSum() {
		$model = new UserInvoice;

		$comm = $_SESSION['user']['community'];
		if(empty($comm)){
            $community = CommunityBasic::find()->All();
        }else{
            $community = CommunityBasic::find()->where(['community_id' => $comm])->All();
        }

		$from = date( time() );
		$to = $from;
		$in = 0;

		return $this->render( 'sum', [
			'model' => $model,
			'community' => $community,
			'from' => $from,
			'to' => $to,
			'in' => $in,
		] );
	}

	//缴费统计查询
	public function actionSearch() {
		$model = new UserInvoice;

		$from = Yii::$app->request->post();
		foreach ( $from as $f );
		$from = strtotime( $f[ 'from' ] );
		$to = strtotime( $f[ 'to' ] );

        $comm = $_SESSION['user']['community'];
        if(empty($comm)){
            $community = CommunityBasic::find()->All();
        }else{
            $community = CommunityBasic::find()->where(['community_id' => $comm])->All();
        }

		if ( !$from ) {
			$from = date( time() );
		}

		if ( !$to ) {
			$to = date( time() );
		}
		$in = UserInvoice::find()->andwhere( [ 'between', 'payment_time', $from, $to ] )->sum( 'invoice_amount' );


		return $this->render( 'sum', [
			'model' => $model,
			'community' => $community,
			'from' => $from,
			'to' => $to,
			'in' => $in,
			//'invoice' => $invoice,
		] );
	}

	//有费项ID查询缴费详情
	public function actionIndex1( $order_id ) 
    {
    	$searchModel = new UserInvoiceSearch();
    	$searchModel->order_id = $order_id;
    	$dataProvider = $searchModel->search( Yii::$app->request->queryParams );
		//查询费项列表中订单是否存在
    	$s = UserInvoice::find()->select('order_id')->where(['order_id' => $order_id])->asArray()->one();
		
    	if ( empty( $s ) ) {
    		$session = Yii::$app->session;
    		$session->setFlash( 'm_order', '2' );
    		return $this->redirect( Yii::$app->request->referrer ); //request->referrer
    	} else {
    		return $this->render( 'index', [
    			'searchModel' => $searchModel,
    			'dataProvider' => $dataProvider,
    		] );
    	}
    }

	//批量生产费项预览
	public function actionNew() 
	{
		if($_SESSION[ 'user' ][ 'name' ] == 'admin'){
			$query = ( new\ yii\ db\ Query() )->select( [
			'community_realestate.community_id',
			'community_basic.community_name',
			'community_realestate.building_id',
			'community_building.building_name',
			'community_realestate.acreage',
			'community_realestate.room_name',
			'community_realestate.room_number',
			'cost_relation.realestate_id',
			'cost_relation.cost_id',
			'cost_name.cost_name',
			'cost_name.parent',
			'cost_name.price',
		] )->from( 'cost_relation' )
			->join( 'left join', 'community_realestate', 'cost_relation.realestate_id = community_realestate.realestate_id' )
			->join( 'left join', 'community_building', 'community_building.building_id = community_realestate.building_id' )
			->join( 'left join', 'community_basic', 'community_basic.community_id = community_realestate.community_id' )
			->join( 'left join', 'cost_name', 'cost_relation.cost_id = cost_name.cost_id' )
			->where(['or not like','cost_name.cost_name',['水费']]) // 去除水费
			//->where( [ 'community_realestate.community_id' => $_SESSION[ 'user' ][ 'community' ] ] )
		    ->limit( 20 )
		    ->all();
		}else{
			$query = ( new\ yii\ db\ Query() )->select( [
			'community_realestate.community_id',
			'community_basic.community_name',
			'community_realestate.building_id',
			'community_building.building_name',
			'community_realestate.acreage',
			'community_realestate.room_name',
			'community_realestate.room_number',
			'cost_relation.realestate_id',
			'cost_relation.cost_id',
			'cost_name.cost_name',
			'cost_name.parent',
			'cost_name.price',
		] )->from( 'cost_relation' )
			->join( 'left join', 'community_realestate', 'cost_relation.realestate_id = community_realestate.realestate_id' )
			->join( 'left join', 'community_building', 'community_building.building_id = community_realestate.building_id' )
			->join( 'left join', 'community_basic', 'community_basic.community_id = community_realestate.community_id' )
			->join( 'left join', 'cost_name', 'cost_relation.cost_id = cost_name.cost_id' )
			->andwhere( [ 'community_realestate.community_id' => $_SESSION[ 'user' ][ 'community' ] ] )
			->andwhere(['or not like','cost_name.cost_name',['水费']])
		    ->limit( 20 )
		    ->all();
		}

		return $this->renderAjax( 'new', [
			'query' => $query,
		] );
	}

	//批量生成费项
	public function actionAdd() 
	{		
		if($_SESSION[ 'user' ][ 'name' ] == 'admin'){
			$query = ( new\ yii\ db\ Query() )->select( [
			'community_realestate.community_id',
			'community_realestate.building_id',
			'cost_relation.realestate_id',
			'community_realestate.acreage',
			'cost_relation.cost_id',
			'cost_name.cost_name',
			'cost_name.parent',
			'cost_name.price',
		] )
			->from( 'cost_relation' )
			->join( 'left join', 'community_realestate', 'cost_relation.realestate_id = community_realestate.realestate_id' )
			->join( 'left join', 'community_building', 'community_building.building_id = community_realestate.building_id' )
			->join( 'left join', 'community_basic', 'community_basic.community_id = community_realestate.community_id' )
			->join( 'left join', 'cost_name', 'cost_relation.cost_id = cost_name.cost_id' )
			->where(['or not like','cost_name.cost_name',['水费']]) // 去除水费
			->all();
		}else{
			$query = ( new\ yii\ db\ Query() )->select( [
			'community_realestate.community_id',
			'community_realestate.building_id',
			'cost_relation.realestate_id',
			'community_realestate.acreage',
			'cost_relation.cost_id',
			'cost_name.cost_name',
			'cost_name.parent',
			'cost_name.price',
		] )
			->from( 'cost_relation' )
			->join( 'left join', 'community_realestate', 'cost_relation.realestate_id = community_realestate.realestate_id' )
			->join( 'left join', 'community_building', 'community_building.building_id = community_realestate.building_id' )
			->join( 'left join', 'community_basic', 'community_basic.community_id = community_realestate.community_id' )
			->join( 'left join', 'cost_name', 'cost_relation.cost_id = cost_name.cost_id' )
			->andwhere( [ 'community_realestate.community_id' => $_SESSION[ 'user' ][ 'community' ] ] )
			->andwhere(['or not like','cost_name.cost_name',['水费']]) // 去除水费
			->all();
		}
		
		$y = date( 'Y' );
		$m = date( 'm' );
		$m += 0;
		$f = date( time() );
		$a = 0;
		$b = 0;
		$i = count( $query );
		
		foreach ( $query as $q ) {
			$community = $q[ 'community_id' ];
			$building = $q[ 'building_id' ];
			$realestate = $q[ 'realestate_id' ];
			$cost = $q[ 'cost_id' ];
			$description = $q[ 'cost_name' ];
			$d = $y . "年" . $m . "月份" . $description;
			$price = $q[ 'price' ];
			$acreage = $q[ 'acreage' ];
			
			set_time_limit(120);
			ini_set('memory_limit','512M');// 调整PHP由默认占用内存为1024M(1GB)
			
			if ( $description == "物业费" ) {
				$price = $price * $acreage;
				$sql = "insert ignore into user_invoice(community_id,building_id,realestate_id,description, year, month, invoice_amount,create_time,invoice_status)
				values ('$community','$building', '$realestate','$description', '$y', '$m', '$price','$f','0')";
				$result = Yii::$app->db->createCommand( $sql )->execute();
			} else {
				$sql = "insert ignore into user_invoice(community_id,building_id,realestate_id,description, year, month, invoice_amount,create_time,invoice_status)
				values ('$community','$building', '$realestate','$description', '$y', '$m', '$price','$f','0')";
				$result = Yii::$app->db->createCommand( $sql )->execute();
			}

			if ( $result ) {
				$a <= $i;
				$a += 1;
			} else {
				$b <= $i;
				$b += 1;
			}
		}
		$con = "成功生成费项" . $a . "条！-  失败：" . $b . "条 - 合计：" . $i . "条";
		echo "<script> alert('$con');parent.location.href='./'; </script>";

	}

	//单个房号生成费项条件筛选
	public function actionC( $id ) 
	{
		$model = new UserInvoice;
		$model->setScenario('c');

		$i = 1;
		$w = date( 'Y' );
		$y = [ $w - $i * 2 => $w - $i * 2, $w - $i => $w - $i, $w => $w, $w + 1 => $w + 1, $w + 2 => $w + 2, ];
		$m = [ 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5', 6 => '6', 7 => '7', 8 => '8', 9 => '9', 10 => 10, 11 => 11, 12 => 12 ];
		$model->id = $id;

		$cost_id = CostRelation::find()->select( 'cost_id' )->where( [ 'realestate_id' => $id ] )->asArray()->all(); //获取关联费项序号（二维数组）
		$c_id = array_column( $cost_id, 'cost_id' ); //提取关联费项序号
		$cost_info = CostName::find()->select( 'cost_id, cost_name' )->andwhere(['or not like','cost_name.cost_name',['水费']])->andwhere( [ 'cost_id' => $c_id ] )->asArray()->all();//获取关联费项信息
		$cost = ArrayHelper::map( $cost_info, 'cost_id', 'cost_name' );// 重组关联费项信息

		if(empty($cost)){
			echo '此房号暂无关联费项，请点击楼宇关联！';
		}else{
			return $this->renderAjax( 'form', [
		    	'model' => $model,
		    	'id' => $id,
		    	'y' => $y,
		    	'cost' => $cost,
		    	'm' => $m,
		    ] );
		}
	}

	//单个房号生成费项预览
	public function actionV() 
	{
		$i = Yii::$app->request->get();
		
		foreach ( $i as $b );
		$id = $i[ 'id' ];
		$y = $b[ 'year' ];
		$m = $b[ 'month' ];
		$cost = $b[ 'cost' ];//print_r($cost);exit;

		$query = ( new\ yii\ db\ Query() )->select( [
				'community_basic.community_name',
				'community_building.building_name',
				'community_realestate.room_name',
				'community_realestate.room_number',
				'community_realestate.acreage',
				'cost_relation.realestate_id',
				'cost_name.cost_id',
				'cost_name.cost_name',
				'cost_name.parent',
				'cost_name.price',
			] )->from( 'cost_relation' )->join( 'left join', 'community_realestate', 'cost_relation.realestate_id = community_realestate.realestate_id' )->join( 'left join', 'community_building', 'community_building.building_id = community_realestate.building_id' )->join( 'left join', 'community_basic', 'community_basic.community_id = community_realestate.community_id' )->join( 'left join', 'cost_name', 'cost_relation.cost_id = cost_name.cost_id' )->where( [ 'cost_relation.realestate_id' => $id, 'cost_name.cost_id' => $cost ] )
			//->limit(20)
			->all();
		$cost = CostName::find()->select('cost_name,cost_id')->where(['level' => 0])->asArray()->all();
		$c_name = ArrayHelper::map($cost,'cost_id','cost_name');

		return $this->render( 'v', [
			'query' => $query,
			'i' => $i,
			'y' => $y,
			'm' => $m,
			'c_name' => $c_name
		] );
	}

	//单个房号批量生成费项
	public function actionOne() {
		$a = $_GET;
		$name = $a['c_name'];
		foreach ( $a as $b );
		foreach ( $b as $c );
		$id = $a['a']['id']; // 生成费项房号的编号（realestate_id）
		$y = $a['a']['UserInvoice'][ 'year' ]; //生成费项年份
		$m = $a['a']['UserInvoice'][ 'month' ];//生成费项的月份
		$CO = $a[ 'a' ][ 'UserInvoice' ][ 'cost' ]; // 生成费项的编码

		foreach ( $m as $ms ) {
			$query = ( new\ yii\ db\ Query() )->select( [
				'community_realestate.community_id',
				'community_realestate.building_id',
				'community_realestate.acreage',
				'cost_relation.realestate_id',
				'cost_relation.cost_id',
				'cost_name.cost_name',
				'cost_name.parent',
				'cost_name.price',
			] )->from( 'cost_relation' )->join( 'left join', 'community_realestate', 'cost_relation.realestate_id = community_realestate.realestate_id' )->join( 'left join', 'community_building', 'community_building.building_id = community_realestate.building_id' )->join( 'left join', 'community_basic', 'community_basic.community_id = community_realestate.community_id' )->join( 'left join', 'cost_name', 'cost_relation.cost_id = cost_name.cost_id' )->where( [ 'cost_relation.realestate_id' => $id, 'cost_name.cost_id' => $CO ] )->all();
			$f = date( time() );
			$j = 0; //成功插入数据条数
			$h = 0; //失败条数
			$i = count( $query );

			foreach ( $query as $q ) {
				$community = $q[ 'community_id' ];
				$building = $q[ 'building_id' ];
				$realestate = $q[ 'realestate_id' ];
				$cost = $q[ 'cost_id' ];
				$description = $q[ 'cost_name' ];
				$d = $y . "年" . $ms . "月份" . $description; //print_r($d);die;
				$price = $q[ 'price' ];
				$acreage = $q[ 'acreage' ];
				if ( $description == "物业费" ) {
					$price = $price * $acreage;
					$sql = "insert ignore into user_invoice(community_id,building_id,realestate_id,description, year, month, invoice_amount,create_time,invoice_status)
						values ('$community','$building', '$realestate','$description', '$y', '$ms', '$price','$f','0')";
					$result = Yii::$app->db->createCommand( $sql )->execute();
				} else {
					$sql = "insert ignore into user_invoice(community_id,building_id,realestate_id,description, year, month, invoice_amount,create_time,invoice_status)
						values ('$community','$building', '$realestate','$description', '$y', '$ms', '$price','$f','0')";
					$result = Yii::$app->db->createCommand( $sql )->execute();
				}

				if ( $result ) {
					$j <= count( $query );
					$j += 1;
				} else {
					$h <= count( $query );
					$h += 1;
				}
			}
		}

		$con = "成功生成缴费记录" . $j . "条！-  失败：" . $h . "条 - 合计：" . $i . "条";
		echo "<script> alert('$con');parent.location.href='/user-invoice'; </script>";

	}

	public function actions() {
		return ArrayHelper::merge( parent::actions(), [
			'invoice' => [ // identifier for your editable action
				'class' => EditableColumnAction::className(), // action class name
				'modelClass' => UserInvoice::className(), // the update model class
				'outputValue' => function ( $model, $attribute, $key, $index ) {},
				'ajaxOnly' => true,
			]
		] );
	}

	/**
	 * Displays a single UserInvoice model.
	 * @param integer $id
	 * @return mixed
	 */
	public function actionView( $id ) {
		return $this->renderAjax( 'view', [
			'model' => $this->findModel( $id ),
		] );
	}

	/**
	 * Creates a new UserInvoice model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 * @return mixed
	 */
	public function actionCreate() {
		$model = new UserInvoice();
		$model->setScenario('update');

		if ( $model->load( Yii::$app->request->post() ) && $model->save() ) {
			return $this->redirect( 'index' );
		} else {
			return $this->renderAjax( 'update', [
				'model' => $model,
			] );
		}
	}

	/**
	 * Updates an existing UserInvoice model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id
	 * @return mixed
	 */
	public function actionUpdate( $id ) {
		$model = $this->findModel( $id );

		if ( $model->load( Yii::$app->request->post() ) && $model->save() ) {
			return $this->redirect( [ 'view', 'id' => $model->invoice_id ] );
		} else {
			return $this->render( 'update', [
				'model' => $model,
			] );
		}
	}

	/**
	 * Deletes an existing UserInvoice model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * @param integer $id
	 * @return mixed
	 */
	public function actionDelete( $id ) {
		$this->findModel( $id )->delete();

		return $this->redirect( [ 'index' ] );
	}

	/**
	 * Finds the UserInvoice model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param integer $id
	 * @return UserInvoice the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel( $id ) {
		if ( ( $model = UserInvoice::findOne( $id ) ) !== null ) {
			return $model;
		} else {
			throw new NotFoundHttpException( 'The requested page does not exist.' );
		}
	}
}