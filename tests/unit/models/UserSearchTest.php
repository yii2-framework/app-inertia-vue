<?php

declare(strict_types=1);

namespace app\tests\unit\models;

use app\models\UserSearch;
use app\tests\support\Fixtures\UserFixture;
use yii\data\ActiveDataProvider;

/**
 * Unit tests for {@see UserSearch} model.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 0.1
 */
final class UserSearchTest extends \Codeception\Test\Unit
{
    /**
     * @return array{user: array{class: string, dataFile: string}}
     */
    public function _fixtures(): array
    {
        return [
            'user' => [
                'class' => UserFixture::class,
                // @phpstan-ignore binaryOp.invalid
                'dataFile' => codecept_data_dir() . 'user.php',
            ],
        ];
    }

    public function testRulesReturnArray(): void
    {
        $searchModel = new UserSearch();

        $rules = $searchModel->rules();

        verify($rules)
            ->notEmpty();
    }

    public function testSearchReturnsDataProvider(): void
    {
        $searchModel = new UserSearch();

        $dataProvider = $searchModel->search([]);

        self::assertInstanceOf(
            ActiveDataProvider::class,
            $dataProvider,
            'Failed asserting that search method returns an instance of ActiveDataProvider.',
        );

        verify($dataProvider->getCount())
            ->greaterThan(0);
    }

    public function testSearchWithInvalidData(): void
    {
        $searchModel = new UserSearch();

        $dataProvider = $searchModel->search(['UserSearch' => ['id' => 'invalid']]);

        self::assertInstanceOf(
            ActiveDataProvider::class,
            $dataProvider,
            'Failed asserting that search method returns an instance of ActiveDataProvider.',
        );

        verify($dataProvider->getCount())
            ->equals(0);
    }

    public function testSearchWithUsernameFilter(): void
    {
        $searchModel = new UserSearch();

        $dataProvider = $searchModel->search(['UserSearch' => ['username' => 'okirlin']]);

        self::assertInstanceOf(
            ActiveDataProvider::class,
            $dataProvider,
            'Failed asserting that search method returns an instance of ActiveDataProvider.',
        );

        verify($dataProvider->getCount())
            ->equals(1);
    }
}
