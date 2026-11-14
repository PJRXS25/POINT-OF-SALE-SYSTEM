<?php

namespace App\Controllers;

use App\Libraries\MediaStorage;
use App\Models\ProductModel;

class ProductsController extends BaseController
{
    protected ProductModel $productModel;
    protected MediaStorage $mediaStorage;

    public function __construct()
    {
        $this->productModel = model(ProductModel::class);
        $this->mediaStorage = new MediaStorage();
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

        if ($this->request->is('post')) {
            $rules = [
                'name' => 'required|max_length[100]',
                'price' => 'required|decimal|greater_than[0]',
                'stock_quantity' => 'required|integer|greater_than_equal_to[0]',
            ];

            $uploadedFile = $this->request->getFile('image');
            if ($uploadedFile && $uploadedFile->getError() !== UPLOAD_ERR_NO_FILE) {
                $rules['image'] = 'uploaded[image]|max_size[image,2048]|mime_in[image,image/jpeg,image/png,image/webp]|ext_in[image,jpg,jpeg,png,webp]|is_image[image]';
            }

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $productData = [
                'name' => trim($this->request->getPost('name')),
                'price' => (float) $this->request->getPost('price'),
                'stock_quantity' => (int) $this->request->getPost('stock_quantity'),
            ];

            $newImage = null;
            if ($uploadedFile && $uploadedFile->isValid() && !$uploadedFile->hasMoved()) {
                try {
                    $newImage = $this->mediaStorage->store($uploadedFile, 'products');
                    $productData['image'] = $newImage;
                } catch (\Throwable $exception) {
                    return redirect()->back()->withInput()->with('error', 'The product image could not be uploaded.');
                }
            }

            if ($id === null) {
                $saved = $this->productModel->insert($productData) !== false;
                $successMessage = 'Product added successfully.';
            } else {
                $saved = $this->productModel->update($id, $productData);
                $successMessage = 'Product updated successfully.';
            }

            if (!$saved) {
                $this->mediaStorage->delete($newImage);

                return redirect()->back()->withInput()->with('errors', $this->productModel->errors());
            }

            if ($id !== null && $newImage !== null && !empty($existing['image'])) {
                $this->mediaStorage->delete($existing['image']);
            }

            session()->setFlashdata('success', $successMessage);

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
                $this->mediaStorage->delete($product['image'] ?? null);
                session()->setFlashdata('success', 'Product deleted successfully.');
            }
        }

        return redirect()->to('/products');
    }

    public function uploadImage($id)
    {
        $product = $this->productModel->find($id);
        if (!$product) {
            return redirect()->to('/products')->with('error', 'Product not found.');
        }

        $rules = [
            'image' => 'uploaded[image]|max_size[image,2048]|mime_in[image,image/jpeg,image/png,image/webp]|ext_in[image,jpg,jpeg,png,webp]|is_image[image]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->to('/products')->with('errors', $this->validator->getErrors());
        }

        $uploadedFile = $this->request->getFile('image');
        try {
            $newImagePath = $this->mediaStorage->store($uploadedFile, 'products');
        } catch (\Throwable $exception) {
            return redirect()->to('/products')->with('error', 'The product image could not be uploaded.');
        }

        if (!$this->productModel->update($id, ['image' => $newImagePath])) {
            $this->mediaStorage->delete($newImagePath);

            return redirect()->to('/products')->with('errors', $this->productModel->errors());
        }

        if (!empty($product['image'])) {
            $this->mediaStorage->delete($product['image']);
        }

        return redirect()->to('/products')->with('success', 'Product image updated successfully.');
    }
}
