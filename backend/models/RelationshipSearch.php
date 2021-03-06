<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\UserRelationshipRealestate;

/**
 * RelationshipSearch represents the model behind the search form about `app\models\UserRelationshipRealestate`.
 */
class RelationshipSearch extends UserRelationshipRealestate
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'realestate_id'], 'integer'],
            [['account_id'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = UserRelationshipRealestate::find()->where(['property' => 1]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
			'sort' => [
			    'defaultOrder' => [
			        'id' => SORT_DESC
		        ]
		    ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'realestate_id' => $this->realestate_id,
        ]);

        $query->andFilterWhere(['like', 'account_id', $this->account_id]);

        return $dataProvider;
    }
}
