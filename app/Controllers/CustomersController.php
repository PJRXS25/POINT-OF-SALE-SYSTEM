<?php

namespace App\Controllers;

use App\Models\CustomerModel;

class CustomersController extends BaseController
{
    protected CustomerModel $customerModel;

    public function __construct()
    {
        $this->customerModel = model(CustomerModel::class);
    }

    public function index()
    {
        $customers = $this->customerModel->orderBy('created_at', 'DESC')->findAll();

        return view('customers/index', ['customers' => $customers]);
    }

    public function save($id = null)
    {
        $customer = $id !== null ? $this->customerModel->find($id) : null;
        if ($id !== null && $customer === null) {
            return redirect()->to('/customers')->with('error', 'Customer not found.');
        }

        if ($this->request->getMethod() === 'post') {
            $rules = [
                'full_name' => 'required|max_length[100]',
                'email' => 'required|valid_email|max_length[100]',
                'phone' => 'permit_empty|max_length[20]|regex_match[/^[0-9+() .-]{7,20}$/]',
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $data = [
                'full_name' => trim($this->request->getPost('full_name')),
                'email' => trim($this->request->getPost('email')),
                'phone' => trim($this->request->getPost('phone')),
            ];

            if ($id === null) {
                $this->customerModel->insert($data);
                session()->setFlashdata('success', 'Customer added successfully.');
            } else {
                $this->customerModel->update($id, $data);
                session()->setFlashdata('success', 'Customer updated successfully.');
            }

            return redirect()->to('/customers');
        }

        return view('customers/form', ['customer' => $customer, 'mode' => $id !== null ? 'edit' : 'create']);
    }

    public function delete($id)
    {
        if ($this->customerModel->find($id)) {
            try {
                $deleted = $this->customerModel->delete($id);
            } catch (\Throwable $exception) {
                return redirect()->to('/customers')->with('error', 'This customer cannot be deleted because it is used in sales history.');
            }
            if ($deleted) {
                session()->setFlashdata('success', 'Customer deleted successfully.');
            }
        }

        return redirect()->to('/customers');
    }
}
