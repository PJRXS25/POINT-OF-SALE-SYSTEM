<?php

namespace App\Models;

use CodeIgniter\Model;

class SaleModel extends Model
{
    protected $table = 'sales';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['product_id', 'customer_id', 'sold_by', 'quantity', 'total_price', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime';
    protected $validationRules = [
        'product_id' => 'required|integer',
        'customer_id' => 'permit_empty|integer',
        'sold_by' => 'required|integer',
        'quantity' => 'required|integer|greater_than[0]',
        'total_price' => 'required|decimal|greater_than_equal_to[0]',
    ];

    public function getSalesWithDetails()
    {
        return $this->select('sales.*, products.name as product_name, customers.full_name as customer_name, users.full_name as sold_by_name')
            ->join('products', 'products.id = sales.product_id', 'left')
            ->join('customers', 'customers.id = sales.customer_id', 'left')
            ->join('users', 'users.id = sales.sold_by', 'left')
            ->orderBy('sales.created_at', 'DESC')
            ->findAll();
    }

    public function searchWithDetails(string $search = ''): self
    {
        $this->select('sales.*, products.name as product_name, customers.full_name as customer_name, users.full_name as sold_by_name')
            ->join('products', 'products.id = sales.product_id', 'left')
            ->join('customers', 'customers.id = sales.customer_id', 'left')
            ->join('users', 'users.id = sales.sold_by', 'left')
            ->orderBy('sales.created_at', 'DESC');

        if ($search !== '') {
            $this->groupStart()
                ->like('products.name', $search)
                ->orLike('customers.full_name', $search)
                ->orLike('users.full_name', $search)
                ->groupEnd();
        }

        return $this;
    }

    public function getRecentSales(int $limit = 5)
    {
        return $this->select('sales.*, products.name as product_name, users.full_name as sold_by_name')
            ->join('products', 'products.id = sales.product_id', 'left')
            ->join('users', 'users.id = sales.sold_by', 'left')
            ->orderBy('sales.created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
