<?php

namespace backend\controllers;

use Yii;
use app\models\User;
use app\models\LoginForm;

class LoginController extends \yii\web\Controller
{
    public $layout = 'main2';
    
    public function actionIndex()
    {
        {
		$this->layout = 'main1';
        if (!\Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }
		
        return $this->render('index', [
            'model' => $model,
        ]);
    }
    }
}
