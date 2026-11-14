<?php

namespace App\Database\Seeds;

use App\Models\CustomerModel;
use App\Models\ProductModel;
use App\Models\UserModel;
use CodeIgniter\Database\Seeder;

class DefaultDataSeeder extends Seeder
{
    public function run()
    {
        $adminPassword = (string) env('seed.adminPassword', env('seed_adminPassword', ''));
        if (strlen($adminPassword) < 12) {
            throw new \RuntimeException('Set seed.adminPassword to a unique password of at least 12 characters before running the default seeder.');
        }

        $userModel = model(UserModel::class);
        $userModel->insert([
            'username' => 'admin',
            'full_name' => 'System Administrator',
            'password' => $adminPassword,
        ]);

        $customerModel = model(CustomerModel::class);
        $customerModel->insert([
            'full_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '09123456789',
        ]);

        $customerModel->insert([
            'full_name' => 'John Smith',
            'email' => 'john@example.com',
            'phone' => '09112233445',
        ]);

        $productModel = model(ProductModel::class);
        $productModel->insert([
            'name' => 'Coffee',
            'price' => 55.00,
            'stock_quantity' => 20,
        ]);

        $productModel->insert([
            'name' => 'Burger',
            'price' => 140.00,
            'stock_quantity' => 12,
        ]);

        $productModel->insert([
            'name' => 'Sandwich',
            'price' => 95.00,
            'stock_quantity' => 8,
        ]);
    }
}
