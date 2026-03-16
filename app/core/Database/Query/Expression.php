<?php

namespace App\Core\Database\Query;

/**
 * Expression class for database queries
 * Custom MVC implementation without Laravel dependencies
 */
class Expression {
    /**
     * The value of the expression.
     */
    protected $value;

    /**
     * Create a new expression instance.
     */
    public function __construct($value) {
        $this->value = $value;
    }

    /**
     * Get the string value of the expression.
     */
    public function getValue() {
        return $this->value;
    }

    /**
     * Get the string representation of the expression.
     */
    public function __toString() {
        return (string) $this->value;
    }
}
