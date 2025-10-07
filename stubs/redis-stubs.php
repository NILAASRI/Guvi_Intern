<?php
/** @phpstub */
class Redis {
    public function connect(string $host, int $port = 6379, float $timeout = 0.0): bool { return true; }
    public function set(string $key, $value, $timeout = 0): bool { return true; }
    public function get(string $key): mixed { return null; }
    public function del(string $key): int { return 1; }
    public function exists(string $key): bool { return true; }
    public function expire(string $key, int $seconds): bool { return true; }
    public function keys(string $pattern): array { return []; }
    public function hSet(string $key, string $field, $value): bool { return true; }
    public function hGet(string $key, string $field): mixed { return null; }
    public function hDel(string $key, string $field): int { return 1; }
    public function lPush(string $key, $value): int { return 1; }
    public function rPop(string $key): mixed { return null; }
    public function flushAll(): bool { return true; }
}
