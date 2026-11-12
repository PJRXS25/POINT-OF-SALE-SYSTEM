<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['name', 'price', 'stock_quantity', 'image', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime';
    protected $validationRules = [
        'name' => 'required|max_length[100]',
        'price' => 'required|decimal|greater_than[0]',
        'stock_quantity' => 'required|integer|greater_than_equal_to[0]',
        'image' => 'permit_empty|max_size[image,2048]|mime_in[image,image/jpeg,image/png,image/webp]|ext_in[image,jpg,jpeg,png,webp]|is_image[image]',
    ];

    public function getAvailableProducts()
    {
        return $this->where('stock_quantity >', 0)->orderBy('name', 'ASC')->findAll();
    }

    public function getLowStockProducts($limit = 5)
    {
        return $this->where('stock_quantity <=', 10)->orderBy('stock_quantity', 'ASC')->limit($limit)->findAll();
    }
}
