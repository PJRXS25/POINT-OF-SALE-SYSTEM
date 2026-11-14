<?php

use App\Models\CustomerModel;
use App\Models\ProductModel;
use App\Models\SaleModel;
use App\Models\UserModel;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use CodeIgniter\Test\CIUnitTestCase;

/** @internal */
final class ModelConfigurationTest extends CIUnitTestCase
{
    public function testApplicationModelsCanBeCreated(): void
    {
        foreach ([ProductModel::class, CustomerModel::class, UserModel::class, SaleModel::class, OrderModel::class, OrderItemModel::class] as $modelClass) {
            $model = (new ReflectionClass($modelClass))->newInstanceWithoutConstructor();
            $this->assertInstanceOf($modelClass, $model);
        }
    }

    public function testUserPasswordIsHashedBeforeInsert(): void
    {
        $model = (new ReflectionClass(UserModel::class))->newInstanceWithoutConstructor();
        $method = new ReflectionMethod($model, 'hashPassword');
        $result = $method->invoke($model, ['data' => ['password' => 'example-password']]);

        $this->assertNotSame('example-password', $result['data']['password']);
        $this->assertTrue(password_verify('example-password', $result['data']['password']));
    }

    public function testCustomerPasswordIsHashedOnlyOnce(): void
    {
        $model = (new ReflectionClass(CustomerModel::class))->newInstanceWithoutConstructor();
        $method = new ReflectionMethod($model, 'hashPassword');
        $first = $method->invoke($model, ['data' => ['password' => 'customer-password']]);
        $second = $method->invoke($model, ['data' => ['password' => $first['data']['password']]]);

        $this->assertTrue(password_verify('customer-password', $first['data']['password']));
        $this->assertSame($first['data']['password'], $second['data']['password']);
    }
}
