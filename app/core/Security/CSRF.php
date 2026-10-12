<?php

namespace App\Core\Security;

use App\Helpers\SecurityHelper;

/**
 * CSRF facade expected by admin views (S216+ views call
 * \App\Core\Security\CSRF::generateTokenField()). The class was referenced
 * but never created, which fataled every page using it (lighting, rwh,
 * dynamic-pricing all returned 500). Thin delegation to the canonical
 * SecurityHelper so there is exactly ONE token mint/validate path
 * (field name `csrf_token`, what routes/router.php reads).
 */
class CSRF
{
    public static function generateTokenField(): string
    {
        return SecurityHelper::csrfField();
    }

    public static function getToken(): string
    {
        return SecurityHelper::generateCsrfToken();
    }

    public static function validate(?string $token): bool
    {
        return SecurityHelper::validateCsrfToken((string)($token ?? ''));
    }
}
