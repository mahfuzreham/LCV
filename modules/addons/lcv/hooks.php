<?php

if (!defined('WHMCS')) {
    die('This file cannot be accessed directly');
}

require_once __DIR__ . '/lib/Permission.php';

use WHMCS\View\Menu\Item as MenuItem;

/**
 * LCV deliberately avoids editing WHMCS core files.
 * Enforcement hooks will be registered here as the permission engine grows.
 */
add_hook('AdminAreaHeadOutput', 1, function () {
    return '<link rel="stylesheet" href="../modules/addons/lcv/assets/css/lcv.css">';
});

add_hook('AdminAreaFooterOutput', 1, function () {
    return '<script src="../modules/addons/lcv/assets/js/lcv.js"></script>';
});
