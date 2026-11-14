<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\OrderModel;
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
        $orderModel = model(OrderModel::class);

        $staffSalesCount = $saleModel->countAll();
        $staffSalesTotal = (float) ($saleModel->selectSum('total_price')->first()['total_price'] ?? 0);
        $completedOnlineOrders = $orderModel->where('status', 'completed')->countAllResults();
        $completedOnlineTotal = (float) ($orderModel->selectSum('total_amount')->where('status', 'completed')->first()['total_amount'] ?? 0);

        $data = [
            'totalProducts' => $productModel->countAll(),
            'totalCustomers' => $customerModel->countAll(),
            'totalStaff' => $userModel->countAll(),
            'totalSales' => $staffSalesCount + $completedOnlineOrders,
            'salesTotal' => $staffSalesTotal + $completedOnlineTotal,
            'totalOnlineOrders' => $orderModel->countAll(),
            'openOnlineOrders' => $orderModel->whereIn('status', ['pending', 'confirmed', 'ready'])->countAllResults(),
            'lowStockProducts' => $productModel->getLowStockProducts(5),
            'recentSales' => $saleModel->getRecentSales(5),
            'recentOnlineOrders' => $orderModel->getRecentWithCustomer(5),
        ];

        return view('dashboard/index', $data);
    }
}
