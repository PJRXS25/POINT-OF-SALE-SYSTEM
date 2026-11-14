<?php

if (!function_exists('media_url')) {
    function media_url(?string $identifier): ?string
    {
        if ($identifier === null || $identifier === '') {
            return null;
        }

        if (str_starts_with($identifier, 'media:')) {
            $token = substr($identifier, 6);
            return preg_match('/^[a-f0-9]{48}$/', $token) === 1 ? base_url('media/' . $token) : null;
        }

        if (filter_var($identifier, FILTER_VALIDATE_URL)) {
            return $identifier;
        }

        $relativePath = ltrim(str_replace('\\', '/', $identifier), '/');
        return is_file(FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $relativePath)) ? base_url($relativePath) : null;
    }
}
