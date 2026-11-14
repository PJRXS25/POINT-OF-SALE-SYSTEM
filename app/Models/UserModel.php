<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = ['username', 'full_name', 'password', 'avatar', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime';
    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[50]|alpha_numeric',
        'full_name' => 'required|max_length[100]',
        'password' => 'permit_empty|min_length[8]|max_length[255]',
        'avatar' => 'permit_empty|max_size[avatar,2048]|mime_in[avatar,image/jpeg,image/png,image/webp]|ext_in[avatar,jpg,jpeg,png,webp]|is_image[avatar]',
    ];

    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data): array
    {
        if (!isset($data['data']['password']) || $data['data']['password'] === '') {
            return $data;
        }

        $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);

        return $data;
    }

    public function findByUsername(string $username): ?array
    {
        return $this->where('username', $username)->first();
    }
}
