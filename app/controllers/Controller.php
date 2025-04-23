<?php

namespace App\Controllers;

use App\Services\AuthService;

abstract class Controller
{
    protected AuthService $auth;
    protected array $data = [];
    protected string $layout = 'base';

    public function __construct()
    {
        $this->auth = new AuthService();
    }

    protected function view(string $view, array $data = []): void
    {
        $this->data = array_merge($this->data, $data);
        $viewPath = __DIR__ . "/../views/{$view}.php";

        if (!file_exists($viewPath)) {
            throw new \Exception("View {$view} not found");
        }

        extract($this->data);

        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        if ($this->layout) {
            $layoutPath = __DIR__ . "/../views/layouts/{$this->layout}.php";
            if (!file_exists($layoutPath)) {
                throw new \Exception("Layout {$this->layout} not found");
            }
            require $layoutPath;
        } else {
            echo $content;
        }
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    protected function getRequestMethod(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD']);
    }

    protected function isPost(): bool
    {
        return $this->getRequestMethod() === 'POST';
    }

    protected function isGet(): bool
    {
        return $this->getRequestMethod() === 'GET';
    }

    protected function input(string $key, $default = null)
    {
        return $_REQUEST[$key] ?? $default;
    }

    protected function validate(array $rules): array
    {
        $errors = [];
        foreach ($rules as $field => $rule) {
            $value = $this->input($field);
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field][] = ucfirst($field) . ' is required';
                continue;
            }

            if (strpos($rule, 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field][] = 'Invalid email format';
            }

            if (preg_match('/min:([0-9]+)/', $rule, $matches)) {
                $min = (int) $matches[1];
                if (strlen($value) < $min) {
                    $errors[$field][] = ucfirst($field) . " must be at least {$min} characters";
                }
            }
        }

        return $errors;
    }
}
