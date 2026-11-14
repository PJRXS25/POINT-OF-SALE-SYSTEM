<?php

namespace App\Commands;

use App\Models\UserModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CreateAdmin extends BaseCommand
{
    protected $group = 'POS';
    protected $name = 'admin:create';
    protected $description = 'Creates the first administrator from temporary environment variables.';
    protected $usage = 'admin:create';

    public function run(array $params)
    {
        $username = trim((string) getenv('ADMIN_USERNAME'));
        $fullName = trim((string) getenv('ADMIN_FULL_NAME'));
        $password = (string) getenv('ADMIN_PASSWORD');

        if (preg_match('/^[a-zA-Z0-9]{3,50}$/', $username) !== 1) {
            CLI::error('Set ADMIN_USERNAME to 3-50 letters or numbers.');
            return EXIT_ERROR;
        }
        if ($fullName === '' || mb_strlen($fullName) > 100) {
            CLI::error('Set ADMIN_FULL_NAME to a value between 1 and 100 characters.');
            return EXIT_ERROR;
        }
        if (strlen($password) < 12 || strlen($password) > 72) {
            CLI::error('Set ADMIN_PASSWORD to a unique value between 12 and 72 characters.');
            return EXIT_ERROR;
        }

        $users = model(UserModel::class);
        if ($users->findByUsername($username)) {
            CLI::error('That administrator username already exists.');
            return EXIT_ERROR;
        }

        if ($users->insert([
            'username' => $username,
            'full_name' => $fullName,
            'password' => $password,
        ]) === false) {
            CLI::error('Administrator creation failed: ' . implode(' ', $users->errors()));
            return EXIT_ERROR;
        }

        CLI::write('Administrator created. Remove ADMIN_PASSWORD from the environment now.', 'green');
        return EXIT_SUCCESS;
    }
}
