<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\ProductModel;
use App\Models\SaleModel;

class SalesController extends BaseController
{
    protected ProductModel $productModel;
    protected CustomerModel $customerModel;
    protected SaleModel $saleModel;

    public function __construct()
    {
        $this->productModel = model(ProductModel::class);
        $this->customerModel = model(CustomerModel::class);
        $this->saleModel = model(SaleModel::class);
    }

    public function index()
    {
        $search = trim((string) $this->request->getGet('q'));
        if (mb_strlen($search) > 100) {
            $search = mb_substr($search, 0, 100);
        }

        $sales = $this->saleModel->searchWithDetails($search)->paginate(10, 'sales');

        return view('sales/index', [
            'sales' => $sales,
            'pager' => $this->saleModel->pager,
            'search' => $search,
        ]);
    }

    public function save()
    {
        if ($this->request->getMethod() === 'post') {
            $rules = [
                'product_id' => 'required|integer',
                'quantity' => 'required|integer|greater_than[0]',
                'customer_id' => 'permit_empty|integer',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $productId = (int) $this->request->getPost('product_id');
            $customerId = $this->request->getPost('customer_id');
            $quantity = (int) $this->request->getPost('quantity');
            if ($customerId !== '' && $customerId !== null) {
                $customer = $this->customerModel->find((int) $customerId);
                if (!$customer) {
                    return redirect()->back()->withInput()->with('error', 'Selected customer is invalid.');
                }
            }

            $db = \Config\Database::connect();
            $db->transBegin();

            try {
                // Lock the row so concurrent checkouts cannot sell the same stock.
                $product = $db->query('SELECT id, price, stock_quantity FROM products WHERE id = ? FOR UPDATE', [$productId])->getRowArray();

                if (!$product) {
                    $db->transRollback();
                    return redirect()->back()->withInput()->with('error', 'Please select a valid product.');
                }

                if ($quantity > (int) $product['stock_quantity']) {
                    $db->transRollback();
                    return redirect()->back()->withInput()->with('error', 'Stock is insufficient for the requested quantity.');
                }

                $totalPrice = round((float) $product['price'] * $quantity, 2);
                $saleId = $this->saleModel->insert([
                    'product_id' => $productId,
                    'customer_id' => $customerId ?: null,
                    'sold_by' => session()->get('user_id'),
                    'quantity' => $quantity,
                    'total_price' => $totalPrice,
                ]);

                $updated = $this->productModel->update($productId, [
                    'stock_quantity' => (int) $product['stock_quantity'] - $quantity,
                ]);

                if ($saleId === false || $updated === false || $db->transStatus() === false) {
                    throw new \RuntimeException('Sale persistence failed.');
                }

                $db->transCommit();
            } catch (\Throwable $exception) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'The sale could not be completed.');
            }

            session()->setFlashdata('success', 'Sale recorded successfully.');
            return redirect()->to('/sales');
        }

        $products = $this->productModel->getAvailableProducts();
        $customers = $this->customerModel->orderBy('full_name', 'ASC')->findAll();

        return view('sales/form', ['products' => $products, 'customers' => $customers]);
    }
}
