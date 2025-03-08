<?php
namespace App\Utils;

class Session {
    public static function set(string $key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key) {
        unset($_SESSION[$key]);
    }

    public static function clear() {
        $_SESSION = [];
    }
}
