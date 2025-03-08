<?php
namespace App\Utils;

class Validator {
    private $data;
    private $errors = [];

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function required(string $field, string $message) {
        if (empty($this->data[$field])) {
            $this->errors[$field] = $message;
        }
    }

    public function email(string $field, string $message) {
        if (!filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message;
        }
    }

    public function minLength(string $field, int $length, string $message) {
        if (strlen($this->data[$field]) < $length) {
            $this->errors[$field] = $message;
        }
    }

    public function maxLength(string $field, int $length, string $message) {
        if (strlen($this->data[$field]) > $length) {
            $this->errors[$field] = $message;
        }
    }

    public function numeric(string $field, string $message) {
        if (!is_numeric($this->data[$field])) {
            $this->errors[$field] = $message;
        }
    }

    public function isValid(): bool {
        return empty($this->errors);
    }

    public function getErrors(): array {
        return $this->errors;
    }
}
