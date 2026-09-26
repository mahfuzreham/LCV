<?php

namespace LCV;

use WHMCS\\Database\\Capsule;

class Database
{
    public static function table($name)
    {
        return Capsule::table('mod_lcv_' . $name);
    }

    public static function coreTable($name)
    {
        return Capsule::table($name);
    }

    public static function install()
    {
        $schema = Capsule::schema();

        if (!$schema->hasTable('mod_lcv_roles')) {
            $schema->create('mod_lcv_roles', function ($table) {
                $table->increments('id');
                $table->string('role_key', 80)->unique();
                $table->string('name', 120);
                $table->text('description')->nullable();
                $table->boolean('is_system')->default(false);
                $table->timestamps();
            });
        }

        if (!$schema->hasTable('mod_lcv_permissions')) {
            $schema->create('mod_lcv_permissions', function ($table) {
                $table->increments('id');
                $table->string('permission_key', 180)->unique();
                $table->string('label', 180);
                $table->string('permission_group', 80);
                $table->string('permission_type', 40)->default('action');
                $table->timestamps();
            });
        }

        if (!$schema->hasTable('mod_lcv_role_permissions')) {
            $schema->create('mod_lcv_role_permissions', function ($table) {
                $table->increments('id');
                $table->unsignedInteger('role_id');
                $table->unsignedInteger('permission_id');
                $table->unique(['role_id', 'permission_id']);
            });
        }

        if (!$schema->hasTable('mod_lcv_field_permissions')) {
            $schema->create('mod_lcv_field_permissions', function ($table) {
                $table->increments('id');
                $table->unsignedInteger('role_id');
                $table->string('resource', 80);
                $table->string('field_key', 100);
                $table->boolean('can_view')->default(false);
                $table->boolean('can_edit')->default(false);
                $table->timestamps();
                $table->unique(['role_id', 'resource', 'field_key']);
            });
        }

        if (!$schema->hasTable('mod_lcv_admin_roles')) {
            $schema->create('mod_lcv_admin_roles', function ($table) {
                $table->increments('id');
                $table->unsignedInteger('admin_id')->unique();
                $table->unsignedInteger('role_id');
                $table->timestamps();
            });
        }

        if (!$schema->hasTable('mod_lcv_support_pins')) {
            $schema->create('mod_lcv_support_pins', function ($table) {
                $table->increments('id');
                $table->unsignedInteger('admin_id')->unique();
                $table->string('pin_hash', 255);
                $table->unsignedInteger('failed_attempts')->default(0);
                $table->dateTime('locked_until')->nullable();
                $table->dateTime('verified_at')->nullable();
                $table->timestamps();
            });
        }

        if (!$schema->hasTable('mod_lcv_audit_logs')) {
            $schema->create('mod_lcv_audit_logs', function ($table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('admin_id')->nullable();
                $table->string('action', 120);
                $table->string('resource', 120)->nullable();
                $table->string('resource_id', 80)->nullable();
                $table->text('details')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->dateTime('created_at');
                $table->index(['admin_id', 'created_at']);
            });
        }

        self::seed();
    }

    public static function seed()
    {
        foreach (Role::defaults() as $key => $name) {
            $exists = self::table('roles')->where('role_key', $key)->first();
            if (!$exists) {
                self::table('roles')->insert([
                    'role_key' => $key,
                    'name' => $name,
                    'description' => $key === Role::SUPER_ADMIN ? 'Full addon access.' : 'Standard staff role.',
                    'is_system' => true,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        $permissions = [
            ['clients.view','View clients','Clients'],
            ['clients.summary.view','View client summary','Clients'],
            ['clients.profile.view','View client profile','Clients'],
            ['clients.contacts.view','View client contacts','Clients'],
            ['services.view','View services','Products & Services'],
            ['services.create','Create services','Products & Services'],
            ['services.modify','Modify services','Products & Services'],
            ['services.suspend','Suspend services','Products & Services'],
            ['services.unsuspend','Unsuspend services','Products & Services'],
            ['services.terminate','Terminate services','Products & Services'],
            ['services.execute','Execute module commands','Products & Services'],
            ['tickets.view','View tickets','Support'],
            ['tickets.reply','Reply to tickets','Support'],
            ['tickets.manage','Manage tickets','Support'],
            ['billing.invoices.view','View invoices','Billing'],
            ['billing.transactions.view','View transactions','Billing'],
            ['billing.refund','Refunds','Billing'],
            ['servers.view','View servers','Servers'],
            ['servers.manage','Manage servers','Servers'],
            ['servers.execute','Execute server/module commands','Servers'],
        ];

        foreach ($permissions as $p) {
            if (!self::table('permissions')->where('permission_key', $p[0])->exists()) {
                self::table('permissions')->insert([
                    'permission_key'=>$p[0],
                    'label'=>$p[1],
                    'permission_group'=>$p[2],
                    'permission_type'=>'action',
                    'created_at'=>date('Y-m-d H:i:s'),
                    'updated_at'=>date('Y-m-d H:i:s'),
                ]);
            }
        }

        $roleDefaults = [
            'admin_manager' => ['clients.view','clients.summary.view','clients.profile.view','clients.contacts.view','services.view','services.create','services.modify','services.suspend','services.unsuspend','services.terminate','services.execute','tickets.view','tickets.reply','tickets.manage','billing.invoices.view','billing.transactions.view','billing.refund','servers.view','servers.manage','servers.execute'],
            'support_manager' => ['clients.view','clients.summary.view','tickets.view','tickets.reply','tickets.manage'],
            'support_staff' => ['clients.view','clients.summary.view','tickets.view','tickets.reply'],
            'billing_staff' => ['clients.view','clients.summary.view','billing.invoices.view','billing.transactions.view'],
            'technical_staff' => ['clients.view','clients.summary.view','services.view','services.modify','services.suspend','services.unsuspend','services.execute','servers.view','servers.execute'],
            'sales_staff' => ['clients.view','clients.profile.view','clients.contacts.view','services.view'],
            'read_only' => ['clients.view','services.view','tickets.view','billing.invoices.view','billing.transactions.view','servers.view'],
        ];

        foreach ($roleDefaults as $roleKey => $permissionKeys) {
            $roleRow = self::table('roles')->where('role_key', $roleKey)->first();
            if (!$roleRow) {
                continue;
            }

            $hasPermissions = self::table('role_permissions')->where('role_id', $roleRow->id)->exists();
            if ($hasPermissions) {
                continue;
            }

            foreach ($permissionKeys as $permissionKey) {
                $permissionId = self::table('permissions')->where('permission_key', $permissionKey)->value('id');
                if ($permissionId) {
                    self::table('role_permissions')->insert([
                        'role_id' => $roleRow->id,
                        'permission_id' => $permissionId,
                    ]);
                }
            }
        }

        $role = self::table('roles')->where('role_key', Role::SUPER_ADMIN)->first();
        if ($role) {
            foreach (self::table('permissions')->pluck('id') as $permissionId) {
                self::table('role_permissions')->updateOrInsert(
                    ['role_id'=>$role->id, 'permission_id'=>$permissionId],
                    []
                );
            }
        }
    }
}
