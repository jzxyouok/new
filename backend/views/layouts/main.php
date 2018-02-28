<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use app\models\TicketBasic;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body>
<?php $this->beginBody() ?>

<div class="wrap">
        <?php
    NavBar::begin([
        'brandLabel' => "<image class='header-logo' src='/image/logo01.png' width = '30' height = '34' />",
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-default navbar-fixed-top',
        ],
    ]);
        echo Nav::widget([
            'options' => ['class' => 'navbar-nav navbar-right'], //<nav class="navbar navbar-default navbar-fixed-bottom">
            'items' => [
                [
                    'label' => '小区管理',
                    'items'=>[
                        ['label' => '房屋管理','url' => ['/community-realestate/index']],
                        ['label' => '投诉/建议','url' => ['/ticket/index']],
                        '<li><span class="badge badge-inverse">'.TicketBasic::getPengdingCommentCount().'</span></li>',
                    ]
                ],

                [
                    'label' => '物业缴费',
                    'items'=>[
                        ['label' => '缴费管理','url' => ['/user-invoice/index']],
                        ['label' => '订单管理','url' => ['/order/index']],
                        ['label' => '费项设置','url' => ['/cost-name/index']],
                        ['label' => '费项关联','url' => ['/costrelation/index']],
                        ['label' => '费表抄表','url' => ['/water/index']],
                        ['label' => '手机抄表','url' => ['/water/phone']],
                    ]
                ],

                [
                    'label' => '用户管理',
                    'items'=>[
                        ['label' => '账户管理','url' => ['/user']],
                    ],
                ],
                ['label' => $_SESSION['user']['name'],
                    'items' => [
                            ['label' => '修改密码','url' => ['/sysuser/change']],
                            ['label' => '退出','url' => ['/site/logout']],
                    ]
                ],
            ],
        ]);

        NavBar::end();
    ?>
   
          <div class="container">
             <?= Breadcrumbs::widget([
                 'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
             ]) ?>
             <?= $content ?>
          </div>
    </div>

 <footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; 裕达物业 <?= date('Y') ?></p>

        <p class="pull-right"><a href="http://www.gxydwy.com">裕家人 2.0</a></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
