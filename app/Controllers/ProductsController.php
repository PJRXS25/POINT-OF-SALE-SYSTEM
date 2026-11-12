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
            $newImageAbsolutePath = null;
            if ($uploadedFile && $uploadedFile->isValid() && !$uploadedFile->hasMoved()) {
                $uploadDirectory = FCPATH . 'uploads/products';
                if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
                    return redirect()->back()->withInput()->with('error', 'The product image directory is not available.');
                }

                try {
                    $newName = $uploadedFile->getRandomName();
                    $uploadedFile->move($uploadDirectory, $newName);
                    $productData['image'] = 'uploads/products/' . $newName;
                    $newImageAbsolutePath = $uploadDirectory . DIRECTORY_SEPARATOR . $newName;
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
                if ($newImageAbsolutePath !== null && file_exists($newImageAbsolutePath)) {
                    unlink($newImageAbsolutePath);
                }

                return redirect()->back()->withInput()->with('errors', $this->productModel->errors());
            }

            if ($id !== null && $newImageAbsolutePath !== null && !empty($existing['image'])) {
                $oldImageAbsolutePath = FCPATH . $existing['image'];
                if (file_exists($oldImageAbsolutePath)) {
                    unlink($oldImageAbsolutePath);
                }
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
                if (!empty($product['image']) && file_exists(FCPATH . $product['image'])) {
                    unlink(FCPATH . $product['image']);
                }
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
        $uploadDirectory = FCPATH . 'uploads/products';
        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
            return redirect()->to('/products')->with('error', 'The product image directory is not available.');
        }

        try {
            $newName = $uploadedFile->getRandomName();
            $uploadedFile->move($uploadDirectory, $newName);
        } catch (\Throwable $exception) {
            return redirect()->to('/products')->with('error', 'The product image could not be uploaded.');
        }

        $newImagePath = 'uploads/products/' . $newName;
        $newImageAbsolutePath = $uploadDirectory . DIRECTORY_SEPARATOR . $newName;
        if (!$this->productModel->update($id, ['image' => $newImagePath])) {
            if (file_exists($newImageAbsolutePath)) {
                unlink($newImageAbsolutePath);
            }

            return redirect()->to('/products')->with('errors', $this->productModel->errors());
        }

        if (!empty($product['image'])) {
            $oldImageAbsolutePath = FCPATH . $product['image'];
            if (file_exists($oldImageAbsolutePath)) {
                unlink($oldImageAbsolutePath);
            }
        }

        return redirect()->to('/products')->with('success', 'Product image updated successfully.');
    }
}
