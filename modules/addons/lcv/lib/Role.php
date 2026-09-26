<?php

namespace LCV;

final class Role
{
    public const SUPER_ADMIN = 'super_admin';
    public const ADMIN_MANAGER = 'admin_manager';
    public const SUPPORT_MANAGER = 'support_manager';
    public const SUPPORT_STAFF = 'support_staff';
    public const BILLING_STAFF = 'billing_staff';
    public const TECHNICAL_STAFF = 'technical_staff';
    public const SALES_STAFF = 'sales_staff';
    public const READ_ONLY = 'read_only';

    public static function defaults(): array
    {
        return [
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN_MANAGER => 'Admin Manager',
            self::SUPPORT_MANAGER => 'Support Manager',
            self::SUPPORT_STAFF => 'Support Staff',
            self::BILLING_STAFF => 'Billing Staff',
            self::TECHNICAL_STAFF => 'Technical Staff',
            self::SALES_STAFF => 'Sales Staff',
            self::READ_ONLY => 'Read Only',
        ];
    }
}
