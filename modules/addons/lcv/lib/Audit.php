<?php

namespace LCV;

final class Audit
{
    public static function record(string $action, $resource = null, $resourceId = null, array $details = []): void
    {
        try {
            $adminId = Access::adminId();
            Database::table('audit_logs')->insert([
                'admin_id' => $adminId ?: null,
                'action' => $action,
                'resource' => $resource,
                'resource_id' => $resourceId !== null ? (string)$resourceId : null,
                'details' => $details ? json_encode($details, JSON_UNESCAPED_SLASHES) : null,
                'ip_address' => isset($_SERVER['REMOTE_ADDR']) ? substr($_SERVER['REMOTE_ADDR'], 0, 45) : null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\\Throwable $e) {
            // Audit failures must never break WHMCS administration.
        }
    }
}
