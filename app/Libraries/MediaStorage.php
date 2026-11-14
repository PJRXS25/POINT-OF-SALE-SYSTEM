<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;
use RuntimeException;

class MediaStorage
{
    public function store(UploadedFile $file, string $category): string
    {
        if (!in_array($category, ['products', 'avatars'], true)) {
            throw new RuntimeException('Unsupported media category.');
        }

        $generatedName = $file->getRandomName();
        if ($this->usesDatabase()) {
            $contents = file_get_contents($file->getTempName());
            if ($contents === false) {
                throw new RuntimeException('The uploaded file could not be read.');
            }

            $token = bin2hex(random_bytes(24));
            $saved = db_connect()->table('media_assets')->insert([
                'token' => $token,
                'category' => $category,
                'filename' => $generatedName,
                'mime_type' => $file->getMimeType(),
                'data' => $contents,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            if (!$saved) {
                throw new RuntimeException('The uploaded file could not be stored.');
            }

            return 'media:' . $token;
        }

        $uploadDirectory = FCPATH . 'uploads' . DIRECTORY_SEPARATOR . $category;
        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
            throw new RuntimeException('The upload directory is not available.');
        }

        $file->move($uploadDirectory, $generatedName);
        return 'uploads/' . $category . '/' . $generatedName;
    }

    public function delete(?string $identifier): void
    {
        if ($identifier === null || $identifier === '') {
            return;
        }

        if (str_starts_with($identifier, 'media:')) {
            $token = substr($identifier, 6);
            if (preg_match('/^[a-f0-9]{48}$/', $token) === 1) {
                db_connect()->table('media_assets')->where('token', $token)->delete();
            }
            return;
        }

        $relativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($identifier, '/\\'));
        if (!str_starts_with($relativePath, 'uploads' . DIRECTORY_SEPARATOR)) {
            return;
        }

        $absolutePath = FCPATH . $relativePath;
        if (is_file($absolutePath)) {
            unlink($absolutePath);
        }
    }

    private function usesDatabase(): bool
    {
        $driver = getenv('E2E_MEDIA_STORAGE') ?: env('media.storage', env('media_storage', 'local'));
        return strtolower((string) $driver) === 'database';
    }
}
