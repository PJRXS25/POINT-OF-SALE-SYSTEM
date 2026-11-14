<?php

namespace App\Controllers;

use App\Libraries\LoginThrottle;
use App\Models\CustomerModel;

class CustomerAuthController extends BaseController
{
    protected CustomerModel $customerModel;

    public function __construct()
    {
        $this->customerModel = model(CustomerModel::class);
    }

    public function loginForm()
    {
        if (session()->get('customer_id')) {
            return redirect()->to('/shop');
        }

        return view('customer_auth/login');
    }

    public function login()
    {
        $throttle = new LoginThrottle();
        $throttleKey = 'customer-login:' . $this->request->getIPAddress();
        if (!$throttle->allows($throttleKey, 5, 60)) {
            return redirect()->back()->withInput()->with('error', 'Too many login attempts. Please wait one minute and try again.');
        }

        $rules = [
            'email' => 'required|valid_email|max_length[100]',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $customer = $this->customerModel->findByEmail((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');

        if (!$customer || empty($customer['password']) || !password_verify($password, $customer['password'])) {
            return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
        }

        $throttle->clear($throttleKey);

        session()->regenerate(true);
        session()->set([
            'customer_id' => $customer['id'],
            'customer_name' => $customer['full_name'],
            'customer_email' => $customer['email'],
        ]);

        $intendedUrl = (string) session()->get('customer_intended_url');
        session()->remove('customer_intended_url');
        if ($intendedUrl !== '' && str_starts_with($intendedUrl, base_url())) {
            return redirect()->to($intendedUrl)->with('success', 'Welcome back, ' . $customer['full_name'] . '.');
        }

        return redirect()->to('/shop')->with('success', 'Welcome back, ' . $customer['full_name'] . '.');
    }

    public function registerForm()
    {
        if (session()->get('customer_id')) {
            return redirect()->to('/shop');
        }

        return view('customer_auth/register');
    }

    public function register()
    {
        $throttle = new LoginThrottle();
        if (!$throttle->allows('customer-register:' . $this->request->getIPAddress(), 3, 300)) {
            return redirect()->back()->withInput()->with('error', 'Too many registration attempts. Please wait a few minutes and try again.');
        }

        $rules = [
            'full_name' => 'required|max_length[100]',
            'email' => 'required|valid_email|max_length[100]',
            'phone' => 'permit_empty|max_length[20]|regex_match[/^[0-9+() .-]{7,20}$/]',
            'password' => 'required|min_length[8]|max_length[72]',
            'password_confirmation' => 'required|matches[password]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = strtolower(trim((string) $this->request->getPost('email')));
        if ($this->customerModel->findByEmail($email)) {
            return redirect()->back()->withInput()->with('error', 'An account already exists for this email address.');
        }

        $customerId = $this->customerModel->insert([
            'full_name' => trim((string) $this->request->getPost('full_name')),
            'email' => $email,
            'phone' => trim((string) $this->request->getPost('phone')) ?: null,
            'password' => (string) $this->request->getPost('password'),
        ]);

        if ($customerId === false) {
            return redirect()->back()->withInput()->with('errors', $this->customerModel->errors());
        }

        $customer = $this->customerModel->find($customerId);
        session()->regenerate(true);
        session()->set([
            'customer_id' => $customer['id'],
            'customer_name' => $customer['full_name'],
            'customer_email' => $customer['email'],
        ]);

        return redirect()->to('/shop')->with('success', 'Your customer account has been created.');
    }

    public function logout()
    {
        session()->remove(['customer_id', 'customer_name', 'customer_email', 'customer_intended_url']);
        session()->regenerate(true);

        return redirect()->to('/shop')->with('success', 'You have been signed out.');
    }

    public function profile()
    {
        $customer = $this->customerModel->find((int) session()->get('customer_id'));
        if (!$customer) {
            session()->remove(['customer_id', 'customer_name', 'customer_email']);
            return redirect()->to('/customer/login')->with('error', 'Your customer account could not be found.');
        }

        if (!$this->request->is('post')) {
            return view('customer_auth/profile', ['customer' => $customer]);
        }

        $rules = [
            'full_name' => 'required|max_length[100]',
            'phone' => 'permit_empty|max_length[20]|regex_match[/^[0-9+() .-]{7,20}$/]',
            'current_password' => 'permit_empty',
            'new_password' => 'permit_empty|min_length[8]|max_length[72]',
            'new_password_confirmation' => 'matches[new_password]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'full_name' => trim((string) $this->request->getPost('full_name')),
            'phone' => trim((string) $this->request->getPost('phone')) ?: null,
        ];
        $newPassword = (string) $this->request->getPost('new_password');
        if ($newPassword !== '') {
            $currentPassword = (string) $this->request->getPost('current_password');
            if ($currentPassword === '' || !password_verify($currentPassword, $customer['password'])) {
                return redirect()->back()->withInput()->with('error', 'Your current password is incorrect.');
            }
            $data['password'] = $newPassword;
        }

        if (!$this->customerModel->update($customer['id'], $data)) {
            return redirect()->back()->withInput()->with('errors', $this->customerModel->errors());
        }

        session()->set('customer_name', $data['full_name']);
        return redirect()->to('/customer/profile')->with('success', 'Your profile has been updated.');
    }
}
