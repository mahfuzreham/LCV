<?php

namespace LCV;

class SupportPin
{
    const SESSION_KEY = 'lcv_support_pin_verified_until';

    public static function setPin($adminId, $pin)
    {
        if (!preg_match('/^\\d{4,8}$/', (string)$pin)) {
            throw new InvalidArgumentException('Support PIN must contain 4-8 digits.');
        }

        Database::table('support_pins')->updateOrInsert(
            ['admin_id'=>(int)$adminId],
            [
                'pin_hash'=>password_hash((string)$pin, PASSWORD_DEFAULT),
                'failed_attempts'=>0,
                'locked_until'=>null,
                'verified_at'=>null,
                'updated_at'=>date('Y-m-d H:i:s'),
            ]
        );
    }

    public static function verify($adminId, $pin)
    {
        $row = Database::table('support_pins')->where('admin_id', (int)$adminId)->first();
        if (!$row) return false;

        if ($row->locked_until && strtotime($row->locked_until) > time()) {
            return false;
        }

        if (password_verify((string)$pin, $row->pin_hash)) {
            Database::table('support_pins')->where('id',$row->id)->update([
                'failed_attempts'=>0,
                'locked_until'=>null,
                'verified_at'=>date('Y-m-d H:i:s'),
                'updated_at'=>date('Y-m-d H:i:s'),
            ]);
            $_SESSION[self::SESSION_KEY] = time() + 900;
            return true;
        }

        $failed = ((int)$row->failed_attempts) + 1;
        $lock = $failed >= 5 ? date('Y-m-d H:i:s', time() + 900) : null;
        Database::table('support_pins')->where('id',$row->id)->update([
            'failed_attempts'=>$failed,
            'locked_until'=>$lock,
            'updated_at'=>date('Y-m-d H:i:s'),
        ]);
        return false;
    }

    public static function verified()
    {
        return !empty($_SESSION[self::SESSION_KEY]) && (int)$_SESSION[self::SESSION_KEY] > time();
    }

    public static function requireVerified()
    {
        if (!self::verified()) {
            http_response_code(403);
            exit('Support PIN verification required.');
        }
    }
}
