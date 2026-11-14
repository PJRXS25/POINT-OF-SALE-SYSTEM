<?php

namespace App\Controllers;

use App\Libraries\LoginThrottle;
use App\Models\UserModel;

class AuthController extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = model(UserModel::class);
    }

    public function loginForm()
    {
        if (session()->get('user_id')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function login()
    {
        if (!$this->request->is('post')) {
            return redirect()->to('/login');
        }

        $username = trim($this->request->getPost('username'));
        $password = $this->request->getPost('password');

        $throttle = new LoginThrottle();
        $throttleKey = 'staff-login:' . $this->request->getIPAddress();
        if (!$throttle->allows($throttleKey, 5, 60)) {
            return redirect()->back()->withInput()->with('error', 'Too many login attempts. Please wait one minute and try again.');
        }

        if ($username === '' || $password === '') {
            return redirect()->back()->withInput()->with('error', 'Username and password are required.');
        }

        $user = $this->userModel->findByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            $throttle->clear($throttleKey);
            session()->regenerate(true);
            session()->set([
                'user_id' => $user['id'],
                'user_name' => $user['full_name'],
                'username' => $user['username'],
            ]);

            return redirect()->to('/dashboard')->with('success', 'Login successful.');
        }

        return redirect()->back()->withInput()->with('error', 'Invalid username or password.');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/login')->with('success', 'You have been logged out.');
    }
}
