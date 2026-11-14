<?php

namespace App\Controllers;

use App\Libraries\MediaStorage;
use App\Models\UserModel;

class UsersController extends BaseController
{
    protected UserModel $userModel;
    protected MediaStorage $mediaStorage;

    public function __construct()
    {
        $this->userModel = model(UserModel::class);
        $this->mediaStorage = new MediaStorage();
    }

    public function index()
    {
        $users = $this->userModel->orderBy('created_at', 'DESC')->findAll();

        return view('users/index', ['users' => $users]);
    }

    public function save($id = null)
    {
        $existing = $id !== null ? $this->userModel->find($id) : null;
        if ($id !== null && $existing === null) {
            return redirect()->to('/users')->with('error', 'Staff account not found.');
        }

        if ($this->request->is('post')) {
            $rules = [
                'username' => 'required|min_length[3]|max_length[50]|alpha_numeric' . ($id !== null ? '|is_unique[users.username,id,' . $id . ']' : '|is_unique[users.username]'),
                'full_name' => 'required|max_length[100]',
                'password' => $id === null ? 'required|min_length[8]|max_length[72]' : 'permit_empty|min_length[8]|max_length[72]',
            ];

            $uploadedFile = $this->request->getFile('avatar');
            if ($uploadedFile && $uploadedFile->getError() !== UPLOAD_ERR_NO_FILE) {
                $rules['avatar'] = 'uploaded[avatar]|max_size[avatar,2048]|mime_in[avatar,image/jpeg,image/png,image/webp]|ext_in[avatar,jpg,jpeg,png,webp]|is_image[avatar]';
            }

            if (!$this->validate($rules)) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $data = [
                'username' => trim($this->request->getPost('username')),
                'full_name' => trim($this->request->getPost('full_name')),
            ];

            if ($this->request->getPost('password') !== '') {
                $data['password'] = $this->request->getPost('password');
            }

            $newAvatar = null;
            if ($uploadedFile && $uploadedFile->isValid() && !$uploadedFile->hasMoved()) {
                try {
                    $newAvatar = $this->mediaStorage->store($uploadedFile, 'avatars');
                    $data['avatar'] = $newAvatar;
                } catch (\Throwable $exception) {
                    return redirect()->back()->withInput()->with('error', 'The staff avatar could not be uploaded.');
                }
            }

            if ($id === null) {
                $saved = $this->userModel->insert($data) !== false;
                $successMessage = 'Staff account created successfully.';
            } else {
                $saved = $this->userModel->update($id, $data);
                $successMessage = 'Staff account updated successfully.';
            }

            if (!$saved) {
                $this->mediaStorage->delete($newAvatar);
                return redirect()->back()->withInput()->with('errors', $this->userModel->errors());
            }

            if ($id !== null && $newAvatar !== null && !empty($existing['avatar'])) {
                $this->mediaStorage->delete($existing['avatar']);
            }

            session()->setFlashdata('success', $successMessage);

            return redirect()->to('/users');
        }

        return view('users/form', ['user' => $existing, 'mode' => $id !== null ? 'edit' : 'create']);
    }

    public function delete($id)
    {
        $currentUser = session()->get('user_id');
        if ((int) $id === (int) $currentUser) {
            session()->setFlashdata('error', 'You cannot delete the currently logged-in user.');
            return redirect()->to('/users');
        }

        $user = $this->userModel->find($id);
        if ($user) {
            try {
                $deleted = $this->userModel->delete($id);
            } catch (\Throwable $exception) {
                return redirect()->to('/users')->with('error', 'This staff account cannot be deleted because it is used in sales history.');
            }
            if ($deleted) {
                $this->mediaStorage->delete($user['avatar'] ?? null);
                session()->setFlashdata('success', 'Staff deleted successfully.');
            }
        }

        return redirect()->to('/users');
    }
}
