<?php

namespace App\Libraries;

class LoginThrottle
{
    public function allows(string $key, int $capacity, int $windowSeconds): bool
    {
        $db = db_connect();
        $attemptKey = hash('sha256', $key);
        $now = time();
        $timestamp = date('Y-m-d H:i:s', $now);
        $allowed = false;

        $db->transBegin();
        try {
            $row = $db->query(
                'SELECT attempt_key, attempts, window_started_at FROM auth_throttles WHERE attempt_key = ? FOR UPDATE',
                [$attemptKey],
            )->getRowArray();

            if (!$row) {
                $allowed = $db->table('auth_throttles')->insert([
                    'attempt_key' => $attemptKey,
                    'attempts' => 1,
                    'window_started_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            } elseif (strtotime($row['window_started_at']) + $windowSeconds <= $now) {
                $allowed = $db->table('auth_throttles')->where('attempt_key', $attemptKey)->update([
                    'attempts' => 1,
                    'window_started_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            } elseif ((int) $row['attempts'] < $capacity) {
                $allowed = $db->table('auth_throttles')->where('attempt_key', $attemptKey)->update([
                    'attempts' => (int) $row['attempts'] + 1,
                    'updated_at' => $timestamp,
                ]);
            }

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Login throttle persistence failed.');
            }
            $db->transCommit();
        } catch (\Throwable $exception) {
            $db->transRollback();
            return false;
        }

        return $allowed;
    }

    public function clear(string $key): void
    {
        db_connect()->table('auth_throttles')->where('attempt_key', hash('sha256', $key))->delete();
    }
}
