<?php

namespace LCV;

final class Permission
{
    public const VIEW = 'view';
    public const CREATE = 'create';
    public const MODIFY = 'modify';
    public const DELETE = 'delete';
    public const SUSPEND = 'suspend';
    public const UNSUSPEND = 'unsuspend';
    public const TERMINATE = 'terminate';
    public const EXECUTE = 'execute';

    public static function allActions(): array
    {
        return [
            self::VIEW,
            self::CREATE,
            self::MODIFY,
            self::DELETE,
            self::SUSPEND,
            self::UNSUSPEND,
            self::TERMINATE,
            self::EXECUTE,
        ];
    }

    public static function key(string $resource, string $action): string
    {
        return $resource . '.' . $action;
    }

    public static function fieldKey(string $resource, string $field, string $action = self::VIEW): string
    {
        return $resource . '.field.' . $field . '.' . $action;
    }
}
