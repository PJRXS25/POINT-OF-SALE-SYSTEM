<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\ProductModel;
use App\Models\SaleModel;
use App\Models\UserModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $productModel = model(ProductModel::class);
        $customerModel = model(CustomerModel::class);
        $userModel = model(UserModel::class);
        $saleModel = model(SaleModel::class);

        $data = [
            'totalProducts' => $productModel->countAll(),
            'totalCustomers' => $customerModel->countAll(),
            'totalStaff' => $userModel->countAll(),
            'totalSales' => $saleModel->countAll(),
            'salesTotal' => $saleModel->selectSum('total_price')->first()['total_price'] ?? 0,
            'lowStockProducts' => $productModel->getLowStockProducts(5),
            'recentSales' => $saleModel->getRecentSales(5),
        ];

        return view('dashboard/index', $data);
    }
}
