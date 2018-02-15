<?php

namespace backend\controllers;

use Yii;
use app\models\CommunityRealestate;
use app\models\Up;
use app\models\CommunityRealestatenSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use kartik\grid\EditableColumnAction;
use yii\helpers\ArrayHelper;
use yii\web\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;
use app\models\CommunityBasic;
use app\models\CommunityBuilding;

/**
 * CommunityRealestateController implements the CRUD actions for CommunityRealestate model.
 */
class CommunityRealestateController extends Controller
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

    /**
     * Lists all CommunityRealestate models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new CommunityRealestatenSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
	
	//导入业主资料
	public function actionImport()
	{
		$model = new Up();
		return $this->renderAjax( 'impo',[
			'model' => $model] );
	}
	
	//接受导入文件并导入数据
	public function actionRead()
	{
		$model = new Up();
		$session = Yii::$app->session;
		if ( Yii::$app->request->isPost ) {
			$model->file = UploadedFile::getInstance( $model, 'file' );
			$name = $_FILES[ 'Up' ][ 'name' ][ 'file' ]; //保存文件名
			$g = pathinfo( $name, PATHINFO_EXTENSION );
			
			$n = date(time()).".$g"; //新文件名
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
				
				foreach($sheetData as $sheet){
					
					if(count($sheet) != 5){
							$session->setFlash('fail','3');
						    unlink($inputFileName);
							return $this->redirect( Yii::$app->request->referrer );
						}
					
					//获取小区
					$c_id = CommunityBasic::find()->select( 'community_id' )->where( [ 'community_name' => $sheet[ 'A' ] ] )->asArray()->one();
					if($c_id){
						//获取楼宇
						$b_id = CommunityBuilding::find()->select( [ 'building_id' ] )
								->andwhere( [ 'community_building.building_name' => $sheet[ 'B' ] ] )
								->andwhere(['community_id' => $c_id['community_id']])->asArray()->one();
						if($b_id){
							//获取房屋编号
							$r_id = CommunityRealestate::find()->select( [ 'realestate_id' ] )
                                ->andwhere(['community_id' => $c_id['community_id']])
                                ->where( [ 'building_id' => $b_id['building_id'] ] )
                                ->andwhere( [ 'room_name' => $sheet[ 'C' ] ] )
                                ->asArray()
                                ->one();
							if($r_id){
								//更新数据库记录
								$transaction = Yii::$app->db->beginTransaction(); //事务开始标记
								try {
									CommunityRealestate::updateAll(['acreage' => $sheet[ 'D' ]], 'realestate_id = :id',[':id' => $r_id['realestate_id']]);
									$transaction->commit(); //事务提交标记
								} catch ( \Exception $e ) {
									print_r( $e );
									$transaction->rollback(); // 事务滚回标记
									return $this->redirect( Yii::$app->request->referrer ); //返回请求页面
								}
							}else{
                                unlink($inputFileName);
                                return $this->redirect( Yii::$app->request->referrer);
							}
						}else{
							unlink($inputFileName);
		                    return $this->redirect( Yii::$app->request->referrer);
						}
					}else{
						unlink($inputFileName);
						return $this->redirect( Yii::$app->request->referrer);
					}
				}
			}else{
				$session->setFlash('fail','2');//设置文件格式错误提醒
				return $this->redirect( Yii::$app->request->referrer ); //返回请求页面
			}
		}
		unlink($inputFileName);
		return $this->redirect( Yii::$app->request->referrer);
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
	
	//下载业主资料模板
	public function actionDownload()
    {
        return \Yii::$app->response->sendFile('./uplaod/template/info.xlsx');
    }
	
	//GridView 页面直接编辑代码
	public function actions()
   {
       return ArrayHelper::merge(parent::actions(), [
           'reale' => [                                       // identifier for your editable action
               'class' => EditableColumnAction::className(),     // action class name
               'modelClass' => CommunityRealestate::className(),                // the update model class
               'outputValue' => function ($model, $attribute, $key, $index) {
               },
               'ajaxOnly' => true,
           ]
       ]);
   }

	//批量删除
	public function actionDel()
	{
        //$this->enableCsrfValidation = false;//去掉yii2的post验证
        /*$ids = Yii::$app->request->post();

		foreach($ids as $id);
		//删除代码(一开始就不应该存在)
		foreach($id as $i){
			$this->findModel($id)->delete();
		}
		return $this->redirect(Yii::$app->request->referrer);*/
   }
	
    /**
     * Displays a single CommunityRealestate model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->renderAjax('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new CommunityRealestate model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CommunityRealestate();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->realestate_id]);
        } else {
            return $this->renderAjax('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing CommunityRealestate model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
		$b = CommunityBuilding::find()->select('building_id,building_name')->where(['building_id' => $model['building_id']])->asArray()->all();
	    $c = CommunityBasic::find()->select('community_name,community_id')->where(['community_id' => $model['community_id']])->asArray()->all();
	    $comm = ArrayHelper::map($c,'community_id','community_name');
	    $bu = ArrayHelper::map($b,'building_id','building_name');

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->realestate_id]);
        } else {
            return $this->renderAjax('form', [
                'model' => $model,
				'comm' => $comm,
				'bu' => $bu
            ]);
        }
    }

    /**
     * Deletes an existing CommunityRealestate model.
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
     * Finds the CommunityRealestate model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return CommunityRealestate the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CommunityRealestate::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
