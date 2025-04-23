<?php
class Validator {
    private $errors = [];

    public function validateEmail($email) {
        if (empty($email)) {
            $this->errors[] = 'Email is required';
            return false;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'Invalid email format';
            return false;
        }
        return true;
    }

    public function validatePassword($password, $minLength = 8) {
        if (empty($password)) {
            $this->errors[] = 'Password is required';
            return false;
        }
        if (strlen($password) < $minLength) {
            $this->errors[] = "Password must be at least {$minLength} characters long";
            return false;
        }
        if (!preg_match('/[A-Z]/', $password)) {
            $this->errors[] = 'Password must contain at least one uppercase letter';
            return false;
        }
        if (!preg_match('/[a-z]/', $password)) {
            $this->errors[] = 'Password must contain at least one lowercase letter';
            return false;
        }
        if (!preg_match('/[0-9]/', $password)) {
            $this->errors[] = 'Password must contain at least one number';
            return false;
        }
        return true;
    }

    public function validateMobile($mobile) {
        if (empty($mobile)) {
            $this->errors[] = 'Mobile number is required';
            return false;
        }
        if (!preg_match('/^[0-9]{10}$/', $mobile)) {
            $this->errors[] = 'Invalid mobile number format';
            return false;
        }
        return true;
    }

    public function sanitizeInput($data) {
        if (is_array($data)) {
            return array_map([$this, 'sanitizeInput'], $data);
        }
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }

    public function validateName($name) {
        if (empty($name)) {
            $this->errors[] = 'Name is required';
            return false;
        }
        if (!preg_match('/^[a-zA-Z ]{2,50}$/', $name)) {
            $this->errors[] = 'Name must contain only letters and spaces, and be between 2-50 characters';
            return false;
        }
        return true;
    }

    public function validateUserType($type) {
        $validTypes = ['user', 'admin', 'associate', 'builder'];
        if (!in_array($type, $validTypes)) {
            $this->errors[] = 'Invalid user type';
            return false;
        }
        return true;
    }

    public function validateNumeric($value, $fieldName, $min = null, $max = null) {
        if (!is_numeric($value)) {
            $this->errors[] = "$fieldName must be a number";
            return false;
        }
        if ($min !== null && $value < $min) {
            $this->errors[] = "$fieldName must be greater than or equal to $min";
            return false;
        }
        if ($max !== null && $value > $max) {
            $this->errors[] = "$fieldName must be less than or equal to $max";
            return false;
        }
        return true;
    }

    public function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        if (!$d || $d->format($format) !== $date) {
            $this->errors[] = 'Invalid date format';
            return false;
        }
        return true;
    }

    public function getErrors() {
        return $this->errors;
    }

    public function hasErrors() {
        return !empty($this->errors);
    }

    public function clearErrors() {
        $this->errors = [];
    }
}