<?php

namespace LCV;

use WHMCS\Database\Capsule;

class License
{
    const STATUS_ACTIVE = 'Active';
    const DEFAULT_LICENSE_SERVER = 'https://my.resellnom.com/';

    public static function config($key, $default = '')
    {
        // Client-facing configuration is intentionally limited to the license key.
        // Private licensing settings may be supplied server-side via constants/env.
        if ($key === 'licensing_url') {
            if (defined('LCV_LICENSE_SERVER_URL') && LCV_LICENSE_SERVER_URL !== '') {
                return rtrim((string)LCV_LICENSE_SERVER_URL, '/') . '/';
            }

            $env = getenv('LCV_LICENSE_SERVER_URL');
            if ($env !== false && trim($env) !== '') {
                return rtrim(trim($env), '/') . '/';
            }

            return self::DEFAULT_LICENSE_SERVER;
        }

        if ($key === 'licensing_secret_key') {
            if (defined('LCV_LICENSE_SECRET') && LCV_LICENSE_SECRET !== '') {
                return trim((string)LCV_LICENSE_SECRET);
            }

            $env = getenv('LCV_LICENSE_SECRET');
            if ($env !== false && trim($env) !== '') {
                return trim($env);
            }
        }

        if ($key === 'local_key_days') {
            if (defined('LCV_LOCAL_KEY_DAYS') && LCV_LOCAL_KEY_DAYS !== '') {
                return (string)LCV_LOCAL_KEY_DAYS;
            }

            $env = getenv('LCV_LOCAL_KEY_DAYS');
            if ($env !== false && trim($env) !== '') {
                return trim($env);
            }
        }

        if ($key === 'allow_check_fail_days') {
            if (defined('LCV_LICENSE_GRACE_DAYS') && LCV_LICENSE_GRACE_DAYS !== '') {
                return (string)LCV_LICENSE_GRACE_DAYS;
            }

            $env = getenv('LCV_LICENSE_GRACE_DAYS');
            if ($env !== false && trim($env) !== '') {
                return trim($env);
            }
        }

        // Backward compatibility for installations that already have these
        // private values stored in tbladdonmodules.
        $value = Capsule::table('tbladdonmodules')
            ->where('module', 'lcv')
            ->where('setting', $key)
            ->value('value');

        return $value === null ? $default : trim((string)$value);
    }

    public static function licenseKey()
    {
        return self::config('license_key');
    }

    public static function localKey()
    {
        $row = Database::table('license')->where('id', 1)->first();
        return $row && !empty($row->local_key) ? (string)$row->local_key : '';
    }

    private static function saveLocalKey($localKey)
    {
        Database::table('license')->updateOrInsert(
            ['id' => 1],
            [
                'license_key' => self::licenseKey(),
                'local_key' => $localKey ?: null,
                'status' => self::STATUS_ACTIVE,
                'last_checked_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
            ]
        );
    }

    public static function check($force = false)
    {
        $licenseKey = self::licenseKey();
        $whmcsUrl = rtrim(self::config('licensing_url', self::DEFAULT_LICENSE_SERVER), '/') . '/';
        $secret = self::config('licensing_secret_key');

        $localKeyDays = max(1, (int)self::config('local_key_days', 30));
        $allowCheckFailDays = max(0, (int)self::config('allow_check_fail_days', 5));

        if ($licenseKey === '') {
            return ['status' => 'Invalid', 'description' => 'License key is not configured.'];
        }

        if ($whmcsUrl === '/' || $secret === '') {
            return ['status' => 'Invalid', 'description' => 'WHMCS Software Licensing configuration is incomplete.'];
        }

        $localKey = $force ? '' : self::localKey();
        $result = self::checkLicense($licenseKey, $localKey, $whmcsUrl, $secret, $localKeyDays, $allowCheckFailDays);

        if (!empty($result['localkey']) && $result['status'] === self::STATUS_ACTIVE) {
            self::saveLocalKey($result['localkey']);
        }

        if (isset($result['status'])) {
            Database::table('license')->updateOrInsert(
                ['id' => 1],
                [
                    'license_key' => $licenseKey,
                    'status' => (string)$result['status'],
                    'last_checked_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                ]
            );
        }

        return $result;
    }

    public static function valid()
    {
        try {
            return self::check()['status'] === self::STATUS_ACTIVE;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public static function status()
    {
        try {
            return self::check();
        } catch (\Throwable $e) {
            return ['status' => 'Invalid', 'description' => $e->getMessage()];
        }
    }

    private static function checkLicense($licensekey, $localkey, $whmcsurl, $secret, $localkeydays, $allowcheckfaildays)
    {
        $checkToken = time() . md5(mt_rand(1000000000, 9999999999) . $licensekey);
        $checkdate = date('Ymd');
        $domain = isset($_SERVER['SERVER_NAME']) ? strtolower(trim($_SERVER['SERVER_NAME'])) : '';
        $usersip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : (isset($_SERVER['LOCAL_ADDR']) ? $_SERVER['LOCAL_ADDR'] : '');
        $dirpath = defined('ROOTDIR') ? ROOTDIR : dirname(__DIR__, 3);
        $verifyfilepath = 'modules/servers/licensing/verify.php';

        $localkeyvalid = false;
        $installationMismatch = false;
        $results = [];

        if ($localkey) {
            $localkey = str_replace("\n", '', $localkey);

            if (strlen($localkey) > 32) {
                $localdata = substr($localkey, 0, strlen($localkey) - 32);
                $md5hash = substr($localkey, strlen($localkey) - 32);

                if (hash_equals($md5hash, md5($localdata . $secret))) {
                    $localdata = strrev($localdata);
                    $md5hash = substr($localdata, 0, 32);
                    $localdata = substr($localdata, 32);
                    $decoded = base64_decode($localdata, true);
                    $localkeyresults = $decoded !== false ? @unserialize($decoded) : false;

                    if (is_array($localkeyresults) && !empty($localkeyresults['checkdate'])) {
                        $originalcheckdate = $localkeyresults['checkdate'];

                        if (hash_equals($md5hash, md5($originalcheckdate . $secret))) {
                            $localexpiry = date(
                                'Ymd',
                                mktime(0, 0, 0, date('m'), date('d') - $localkeydays, date('Y'))
                            );

                            if ($originalcheckdate > $localexpiry) {
                                $results = $localkeyresults;

                                $validdomains = isset($results['validdomain']) ? array_filter(array_map('trim', explode(',', strtolower($results['validdomain'])))) : [];
                                if ($validdomains && !in_array($domain, $validdomains, true)) {
                                    $installationMismatch = true;
                                }

                                $validips = isset($results['validip']) ? array_filter(array_map('trim', explode(',', $results['validip']))) : [];
                                if ($validips && !in_array($usersip, $validips, true)) {
                                    $installationMismatch = true;
                                }

                                $validdirs = isset($results['validdirectory']) ? array_filter(array_map('trim', explode(',', $results['validdirectory']))) : [];
                                if ($validdirs && !in_array($dirpath, $validdirs, true)) {
                                    $installationMismatch = true;
                                }

                                if (!$installationMismatch) {
                                    $localkeyvalid = true;
                                } else {
                                    $results = [];
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!$localkeyvalid) {
            $postfields = [
                'licensekey' => $licensekey,
                'domain' => $domain,
                'ip' => $usersip,
                'dir' => $dirpath,
                'check_token' => $checkToken,
            ];

            $verifyUrl = $whmcsurl . ltrim($verifyfilepath, '/');
            $ch = curl_init($verifyUrl);

            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($postfields),
                CURLOPT_TIMEOUT => 30,
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_HTTPHEADER => ['Accept: application/xml,text/xml;q=0.9,*/*;q=0.8'],
            ]);

            $data = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (!$data) {
                $localexpiry = date(
                    'Ymd',
                    mktime(0, 0, 0, date('m'), date('d') - ($localkeydays + $allowCheckFailDays), date('Y'))
                );

                if (!empty($localKey) && isset($originalcheckdate) && $originalcheckdate > $localexpiry && !empty($localkeyresults) && !$installationMismatch) {
                    $results = $localkeyresults;
                    $results['remotecheck'] = false;
                } else {
                    return [
                        'status' => 'Remote Check Failed',
                        'description' => $curlError ?: ('License server returned no response (HTTP ' . $httpCode . ').'),
                        'installation_mismatch' => $installationMismatch,
                    ];
                }
            } else {
                preg_match_all('/<(.*?)>([^<]*)<\/\\1>/i', $data, $matches);
                $results = [];

                foreach ($matches[1] as $k => $v) {
                    $results[$v] = html_entity_decode($matches[2][$k], ENT_QUOTES, 'UTF-8');
                }
            }

            if (!is_array($results) || empty($results['status'])) {
                return [
                    'status' => 'Invalid',
                    'description' => 'Invalid license server response.',
                    'installation_mismatch' => $installationMismatch,
                ];
            }

            if (!empty($results['md5hash']) && !hash_equals($results['md5hash'], md5($secret . $checkToken))) {
                return [
                    'status' => 'Invalid',
                    'description' => 'MD5 checksum verification failed.',
                    'installation_mismatch' => $installationMismatch,
                ];
            }

            if ($results['status'] === self::STATUS_ACTIVE) {
                $results['checkdate'] = $checkdate;
                $dataEncoded = serialize($results);
                $dataEncoded = base64_encode($dataEncoded);
                $dataEncoded = md5($checkdate . $secret) . $dataEncoded;
                $dataEncoded = strrev($dataEncoded);
                $dataEncoded .= md5($dataEncoded . $secret);
                $results['localkey'] = wordwrap($dataEncoded, 80, "\n", true);
            }

            $results['remotecheck'] = true;
        }

        $results['installation_mismatch'] = $installationMismatch;

        if (($results['status'] ?? '') !== self::STATUS_ACTIVE && $installationMismatch) {
            $results['description'] = 'Unauthorized installation detected. The licensed domain, IP address, or installation directory does not match the license.';
        }

        return $results;
    }
}
