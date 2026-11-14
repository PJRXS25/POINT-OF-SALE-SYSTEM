<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['customer_id', 'status', 'payment_method', 'total_amount', 'pickup_notes'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime';
    protected $validationRules = [
        'customer_id' => 'required|integer',
        'status' => 'required|in_list[pending,confirmed,ready,completed,cancelled]',
        'payment_method' => 'required|in_list[cash_on_pickup]',
        'total_amount' => 'required|decimal|greater_than[0]',
        'pickup_notes' => 'permit_empty|max_length[500]',
    ];

    public function findForCustomer(int $orderId, int $customerId): ?array
    {
        return $this->where(['id' => $orderId, 'customer_id' => $customerId])->first();
    }

    public function getRecentWithCustomer(int $limit = 5): array
    {
        return $this->select('orders.*, customers.full_name AS customer_name')
            ->join('customers', 'customers.id = orders.customer_id')
            ->orderBy('orders.created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
