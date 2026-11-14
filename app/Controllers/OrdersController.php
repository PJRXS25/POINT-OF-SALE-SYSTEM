<?php

namespace App\Controllers;

use App\Models\OrderItemModel;
use App\Models\OrderModel;

class OrdersController extends BaseController
{
    protected OrderModel $orderModel;
    protected OrderItemModel $orderItemModel;

    public function __construct()
    {
        $this->orderModel = model(OrderModel::class);
        $this->orderItemModel = model(OrderItemModel::class);
    }

    public function index()
    {
        $search = trim((string) $this->request->getGet('q'));
        $status = trim((string) $this->request->getGet('status'));
        $allowedStatuses = ['pending', 'confirmed', 'ready', 'completed', 'cancelled'];

        $this->orderModel
            ->select('orders.*, customers.full_name AS customer_name, customers.email AS customer_email')
            ->join('customers', 'customers.id = orders.customer_id')
            ->orderBy('orders.created_at', 'DESC');

        if ($search !== '') {
            $this->orderModel->groupStart()
                ->like('customers.full_name', $search)
                ->orLike('customers.email', $search)
                ->orLike('orders.id', $search)
                ->groupEnd();
        }
        if (in_array($status, $allowedStatuses, true)) {
            $this->orderModel->where('orders.status', $status);
        } else {
            $status = '';
        }

        return view('orders/index', [
            'orders' => $this->orderModel->paginate(15, 'onlineOrders'),
            'pager' => $this->orderModel->pager,
            'search' => $search,
            'status' => $status,
            'statuses' => $allowedStatuses,
        ]);
    }

    public function show($orderId)
    {
        $order = $this->orderModel
            ->select('orders.*, customers.full_name AS customer_name, customers.email AS customer_email, customers.phone AS customer_phone')
            ->join('customers', 'customers.id = orders.customer_id')
            ->find((int) $orderId);

        if (!$order) {
            return redirect()->to('/orders')->with('error', 'Online order not found.');
        }

        return view('orders/show', [
            'order' => $order,
            'items' => $this->orderItemModel->where('order_id', $order['id'])->findAll(),
        ]);
    }

    public function updateStatus($orderId)
    {
        $order = $this->orderModel->find((int) $orderId);
        if (!$order) {
            return redirect()->to('/orders')->with('error', 'Online order not found.');
        }

        $allowedTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['ready', 'cancelled'],
            'ready' => ['completed'],
        ];
        $newStatus = (string) $this->request->getPost('status');
        if (!in_array($newStatus, $allowedTransitions[$order['status']] ?? [], true)) {
            return redirect()->back()->with('error', 'That order status change is not allowed.');
        }

        if ($newStatus === 'cancelled') {
            $db = db_connect();
            $db->transBegin();

            try {
                $items = $this->orderItemModel->where('order_id', $order['id'])->findAll();
                foreach ($items as $item) {
                    $product = $db->query(
                        'SELECT id, stock_quantity FROM products WHERE id = ? FOR UPDATE',
                        [(int) $item['product_id']],
                    )->getRowArray();
                    if (!$product) {
                        throw new \RuntimeException('An ordered product could not be found.');
                    }

                    $updated = $db->table('products')->where('id', $product['id'])->update([
                        'stock_quantity' => (int) $product['stock_quantity'] + (int) $item['quantity'],
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                    if (!$updated) {
                        throw new \RuntimeException('Inventory could not be restored.');
                    }
                }

                if (!$this->orderModel->update($order['id'], ['status' => 'cancelled']) || $db->transStatus() === false) {
                    throw new \RuntimeException('The order could not be cancelled.');
                }

                $db->transCommit();
            } catch (\Throwable $exception) {
                $db->transRollback();
                return redirect()->back()->with('error', 'The order could not be cancelled and inventory was not changed.');
            }
        } elseif (!$this->orderModel->update($order['id'], ['status' => $newStatus])) {
            return redirect()->back()->with('error', 'The order status could not be updated.');
        }

        return redirect()->to('/orders/' . $order['id'])->with('success', 'Order status updated to ' . ucfirst($newStatus) . '.');
    }
}
