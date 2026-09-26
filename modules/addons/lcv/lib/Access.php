<?php

namespace LCV;

class Access
{
    public static function adminId()
    {
        return (int) (function_exists('adminID') ? adminID() : ($_SESSION['adminid'] ?? 0));
    }

    public static function roleId($adminId = null)
    {
        $adminId = $adminId ?: self::adminId();
        if (!$adminId) return null;

        $row = Database::table('admin_roles')->where('admin_id', $adminId)->first();
        if ($row) return (int) $row->role_id;

        $role = Database::table('roles')->where('role_key', Role::SUPER_ADMIN)->first();
        return $role ? (int) $role->id : null;
    }

    public static function roleKey($adminId = null)
    {
        $roleId = self::roleId($adminId);
        if (!$roleId) return null;

        $role = Database::table('roles')->where('id', $roleId)->first();
        return $role ? $role->role_key : null;
    }

    public static function isSuperAdmin($adminId = null)
    {
        return self::roleKey($adminId) === Role::SUPER_ADMIN;
    }

    public static function can($permission, $adminId = null)
    {
        if (self::isSuperAdmin($adminId)) return true;

        $roleId = self::roleId($adminId);
        if (!$roleId) return false;

        $permissionId = Database::table('permissions')->where('permission_key', $permission)->value('id');
        if (!$permissionId) return false;

        return Database::table('role_permissions')
            ->where('role_id', $roleId)
            ->where('permission_id', $permissionId)
            ->exists();
    }

    public static function field($resource, $field, $action = 'view', $adminId = null)
    {
        if (self::isSuperAdmin($adminId)) return true;

        $roleId = self::roleId($adminId);
        if (!$roleId) return false;

        $row = Database::table('field_permissions')
            ->where('role_id', $roleId)
            ->where('resource', $resource)
            ->where('field_key', $field)
            ->first();

        if (!$row) return false;
        return $action === 'edit' ? (bool)$row->can_edit : (bool)$row->can_view;
    }

    public static function requirePermission($permission)
    {
        if (!self::can($permission)) {
            http_response_code(403);
            exit('Access denied.');
        }
    }
}
