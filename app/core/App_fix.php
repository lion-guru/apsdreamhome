// Property routes
        } elseif ($uri === "/properties") {
            return $this->loadController("Property\\PropertyController", "index");
        } elseif (preg_match('/^\/properties\/(\d+)$/', $uri, $matches)) {
            return $this->loadController("HomeController", "propertyDetail", [$matches[1]]);
