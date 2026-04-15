<?php

declare(strict_types=1);

namespace app\models;

use yii\data\ActiveDataProvider;

/**
 * Provides search and filtering capabilities for the {@see User} model.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class UserSearch extends User
{
    /**
     * Returns the validation rules for the search attributes.
     */
    public function rules(): array
    {
        return [
            [
                [
                    'id',
                    'status',
                ],
                'integer',
            ],
            [
                [
                    'username',
                    'email',
                ],
                'safe',
            ],
        ];
    }

    /**
     * Creates data provider instance with search query applied.
     *
     * @param array<string, mixed> $params Search parameters to filter the user data.
     *
     * @return ActiveDataProvider Data provider containing the filtered user data.
     */
    public function search(array $params): ActiveDataProvider
    {
        $query = User::find();

        $dataProvider = new ActiveDataProvider(
            [
                'query' => $query,
                'pagination' => [
                    'pageSize' => 10,
                ],
                'sort' => [
                    'defaultOrder' => [
                        'id' => SORT_DESC,
                    ],
                ],
            ],
        );

        $this->load($params);

        if (!$this->validate()) {
            $query->where('0=1');

            return $dataProvider;
        }

        $query->andFilterWhere(['id' => $this->id])
            ->andFilterWhere(['status' => $this->status])
            ->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'email', $this->email]);

        return $dataProvider;
    }
}
