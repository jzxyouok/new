<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\CommunityRealestate */

$this->title = $model->room_number;
$this->params[ 'breadcrumbs' ][] = [ 'label' => '房屋列表', 'url' => [ 'index' ] ];
$this->params[ 'breadcrumbs' ][] = $this->title;
?>
<div class="community-realestate-view">

<table style="width:500px; border-radius:20px;" border="0" align="center">
	<tbody>
		<tr>
			<td>
				<?= DetailView::widget( [
	            	'model' => $model,
	            	'attributes' => [
	            		'realestate_id',
	            		'community0.community_name',
	            		'building0.building_name', [ 'attribute' => 'room_name',
	            			'value' => function ( $model ) {
	            				$name = explode( '-', $model[ 'room_name' ] );
	            				if ( count( $name ) == 1 ) {
	            					return '1' . '单元';
	            				} else {
	            					return $name[ '0' ] . '单元';
	            				}
	            			}
	            		],
	            		[ 'attribute' => 'room_number',
	            			'value' => function ( $model ) {
	            				$number = explode( '-', $model[ 'room_number' ] );
	            				return end( $number );
	            			}
	            		],
                 
	            		'owners_name',
	            		'owners_cellphone',
	            		'acreage'
	            	],
	            ] )
	            ?>
			</td>
		</tr>
	</tbody>
</table>

</div>