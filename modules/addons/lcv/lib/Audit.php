<?php

namespace LCV;

final class Audit
{
    public static function record(string $action, array $context = []): void
    {
        // Database-backed audit logging will be implemented with the migration layer.
        // Keep this method as the single entry point so sensitive actions can be logged consistently.
    }
}
