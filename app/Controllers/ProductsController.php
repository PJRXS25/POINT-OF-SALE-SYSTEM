<?php

namespace App\Controllers;

use App\Models\ProductModel;

class ProductsController extends BaseController
{
    protected ProductModel $productModel;

    public function __construct()
    {
        $this->productModel = model(ProductModel::class);
    }

    public function index()
    {
        $products = $this->productModel->orderBy('created_at', 'DESC')->findAll();

        return view('products/index', ['products' => $products]);
    }

    public function save($id = null)
    {
        $existing = $id !== null ? $this->productModel->find($id) : null;
        if ($id !== null && $existing === null) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'name' => 'required|max_length[100]',
                'price' => 'required|decimal|greater_than[0]',
                'stock_quantity' => 'required|integer|greater_than_equal_to[0]',
            ];

            $rules['image'] = 'permit_empty|max_size[image,2048]|mime_in[image,image/jpeg,image/png,image/webp]|ext_in[image,jpg,jpeg,png,webp]|is_image[image]';

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $productData = [
                'name' => trim($this->request->getPost('name')),
                'price' => (float) $this->request->getPost('price'),
                'stock_quantity' => (int) $this->request->getPost('stock_quantity'),
            ];

            $uploadedFile = $this->request->getFile('image');
            if ($uploadedFile && $uploadedFile->isValid() && !$uploadedFile->hasMoved()) {
                $newName = $uploadedFile->getRandomName();
                $uploadedFile->move(FCPATH . 'uploads/products', $newName);
                $productData['image'] = 'uploads/products/' . $newName;

                if ($id !== null) {
                    if (!empty($existing['image']) && file_exists(FCPATH . $existing['image'])) {
                        unlink(FCPATH . $existing['image']);
                    }
                }
            }

            if ($id === null) {
                $this->productModel->insert($productData);
                session()->setFlashdata('success', 'Product added successfully.');
            } else {
                $this->productModel->update($id, $productData);
                session()->setFlashdata('success', 'Product updated successfully.');
            }

            return redirect()->to('/products');
        }

        return view('products/form', ['product' => $existing, 'mode' => $id !== null ? 'edit' : 'create']);
    }

    public function delete($id)
    {
        $product = $this->productModel->find($id);
        if ($product) {
            try {
                $deleted = $this->productModel->delete($id);
            } catch (\Throwable $exception) {
                return redirect()->to('/products')->with('error', 'This product cannot be deleted because it is used in sales history.');
            }
            if ($deleted) {
                if (!empty($product['image']) && file_exists(FCPATH . $product['image'])) {
                    unlink(FCPATH . $product['image']);
                }
                session()->setFlashdata('success', 'Product deleted successfully.');
            }
        }

        return redirect()->to('/products');
    }
}
