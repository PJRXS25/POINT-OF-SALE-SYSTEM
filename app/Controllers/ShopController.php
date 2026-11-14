<?php

namespace App\Controllers;

use App\Models\CustomerModel;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Models\ProductModel;

class ShopController extends BaseController
{
    protected ProductModel $productModel;
    protected OrderModel $orderModel;
    protected OrderItemModel $orderItemModel;
    protected CustomerModel $customerModel;

    public function __construct()
    {
        $this->productModel = model(ProductModel::class);
        $this->orderModel = model(OrderModel::class);
        $this->orderItemModel = model(OrderItemModel::class);
        $this->customerModel = model(CustomerModel::class);
    }

    public function index()
    {
        $search = trim((string) $this->request->getGet('q'));
        if (mb_strlen($search) > 100) {
            $search = mb_substr($search, 0, 100);
        }

        $this->productModel->where('stock_quantity >', 0)->orderBy('name', 'ASC');
        if ($search !== '') {
            $this->productModel->like('name', $search);
        }

        return view('shop/index', [
            'products' => $this->productModel->paginate(12, 'catalog'),
            'pager' => $this->productModel->pager,
            'search' => $search,
        ]);
    }

    public function addToCart()
    {
        $rules = [
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|greater_than[0]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $productId = (int) $this->request->getPost('product_id');
        $quantity = (int) $this->request->getPost('quantity');
        $product = $this->productModel->find($productId);
        if (!$product || (int) $product['stock_quantity'] < 1) {
            return redirect()->back()->with('error', 'This product is currently unavailable.');
        }

        $cart = $this->cart();
        $newQuantity = ($cart[$productId] ?? 0) + $quantity;
        if ($newQuantity > (int) $product['stock_quantity']) {
            return redirect()->back()->with('error', 'Only ' . $product['stock_quantity'] . ' units are available.');
        }

        $cart[$productId] = $newQuantity;
        session()->set('customer_cart', $cart);

        return redirect()->back()->with('success', $product['name'] . ' was added to your cart.');
    }

    public function cartView()
    {
        return view('shop/cart', $this->cartDetails());
    }

    public function updateCart()
    {
        $quantities = $this->request->getPost('quantities');
        if (!is_array($quantities)) {
            return redirect()->to('/shop/cart')->with('error', 'Invalid cart update.');
        }

        $cart = $this->cart();
        foreach ($quantities as $productId => $quantityValue) {
            $productId = (int) $productId;
            $quantity = filter_var($quantityValue, FILTER_VALIDATE_INT);
            if (!isset($cart[$productId]) || $quantity === false) {
                continue;
            }
            if ($quantity <= 0) {
                unset($cart[$productId]);
                continue;
            }

            $product = $this->productModel->find($productId);
            if (!$product || $quantity > (int) $product['stock_quantity']) {
                return redirect()->to('/shop/cart')->with('error', 'The requested quantity is unavailable.');
            }
            $cart[$productId] = $quantity;
        }

        session()->set('customer_cart', $cart);
        return redirect()->to('/shop/cart')->with('success', 'Your cart has been updated.');
    }

    public function removeFromCart($productId)
    {
        $cart = $this->cart();
        unset($cart[(int) $productId]);
        session()->set('customer_cart', $cart);

        return redirect()->to('/shop/cart')->with('success', 'The item was removed from your cart.');
    }

    public function checkout()
    {
        $cartData = $this->cartDetails();
        if (empty($cartData['items'])) {
            return redirect()->to('/shop')->with('error', 'Your cart is empty.');
        }

        if (!$this->request->is('post')) {
            $customer = $this->customerModel->find((int) session()->get('customer_id'));
            return view('shop/checkout', $cartData + ['customer' => $customer]);
        }

        if (!$this->validate(['pickup_notes' => 'permit_empty|max_length[500]'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $cart = $this->cart();
        ksort($cart);
        $db = \Config\Database::connect();
        $db->transBegin();

        try {
            $lockedItems = [];
            $total = 0.0;
            foreach ($cart as $productId => $quantity) {
                $product = $db->query(
                    'SELECT id, name, price, stock_quantity FROM products WHERE id = ? FOR UPDATE',
                    [(int) $productId],
                )->getRowArray();

                if (!$product || $quantity < 1 || $quantity > (int) $product['stock_quantity']) {
                    throw new \RuntimeException('One or more products no longer have enough stock.');
                }

                $subtotal = round((float) $product['price'] * $quantity, 2);
                $total = round($total + $subtotal, 2);
                $lockedItems[] = ['product' => $product, 'quantity' => $quantity, 'subtotal' => $subtotal];
            }

            $orderId = $this->orderModel->insert([
                'customer_id' => (int) session()->get('customer_id'),
                'status' => 'pending',
                'payment_method' => 'cash_on_pickup',
                'total_amount' => $total,
                'pickup_notes' => trim((string) $this->request->getPost('pickup_notes')) ?: null,
            ]);
            if ($orderId === false) {
                throw new \RuntimeException('The order could not be created.');
            }

            foreach ($lockedItems as $item) {
                $product = $item['product'];
                $itemId = $this->orderItemModel->insert([
                    'order_id' => $orderId,
                    'product_id' => $product['id'],
                    'product_name' => $product['name'],
                    'unit_price' => $product['price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['subtotal'],
                ]);
                $updated = $this->productModel->update($product['id'], [
                    'stock_quantity' => (int) $product['stock_quantity'] - $item['quantity'],
                ]);
                if ($itemId === false || $updated === false) {
                    throw new \RuntimeException('The order items could not be saved.');
                }
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('The checkout transaction failed.');
            }
            $db->transCommit();
        } catch (\Throwable $exception) {
            $db->transRollback();
            return redirect()->to('/shop/cart')->with('error', $exception instanceof \RuntimeException ? $exception->getMessage() : 'Checkout could not be completed.');
        }

        session()->remove('customer_cart');
        return redirect()->to('/shop/orders/' . $orderId)->with('success', 'Your order has been placed successfully.');
    }

    public function orders()
    {
        $customerId = (int) session()->get('customer_id');
        $orders = $this->orderModel->where('customer_id', $customerId)->orderBy('created_at', 'DESC')->paginate(10, 'orders');

        return view('shop/orders', ['orders' => $orders, 'pager' => $this->orderModel->pager]);
    }

    public function orderDetails($orderId)
    {
        $order = $this->orderModel->findForCustomer((int) $orderId, (int) session()->get('customer_id'));
        if (!$order) {
            return redirect()->to('/shop/orders')->with('error', 'Order not found.');
        }

        return view('shop/order_details', [
            'order' => $order,
            'items' => $this->orderItemModel->where('order_id', $order['id'])->findAll(),
        ]);
    }

    private function cart(): array
    {
        $cart = session()->get('customer_cart');
        return is_array($cart) ? array_map('intval', $cart) : [];
    }

    private function cartDetails(): array
    {
        $cart = $this->cart();
        if ($cart === []) {
            return ['items' => [], 'total' => 0.0, 'itemCount' => 0];
        }

        $products = $this->productModel->find(array_keys($cart));
        $items = [];
        $total = 0.0;
        $itemCount = 0;
        foreach ($products as $product) {
            $quantity = $cart[(int) $product['id']] ?? 0;
            if ($quantity < 1) {
                continue;
            }
            $subtotal = round((float) $product['price'] * $quantity, 2);
            $items[] = ['product' => $product, 'quantity' => $quantity, 'subtotal' => $subtotal];
            $total = round($total + $subtotal, 2);
            $itemCount += $quantity;
        }

        return compact('items', 'total', 'itemCount');
    }
}
