<?php

namespace App\Messenger;

use App\Core\Database\Database;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class TenantContextMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        $message = $envelope->getMessage();
        
        // Extract tenant_id from message if present
        $tenantId = null;
        if (method_exists($message, 'tenantId')) {
            $tenantId = $message->tenantId;
        } elseif (method_exists($message, 'tenant_id')) {
            $tenantId = $message->tenant_id;
        } elseif (isset($message->tenant_id)) {
            $tenantId = $message->tenant_id;
        }
        
        // Fallback to default tenant (1)
        $tenantId = $tenantId ?? 1;
        
        // Set tenant context for this request
        try {
            Database::getInstance()->setTenantContext($tenantId);
        } catch (\Throwable $e) {
            // Silently ignore if tenant context setting fails
            // The default tenant will be used
        }
        
        return $stack->next()->handle($envelope, $stack);
    }
}