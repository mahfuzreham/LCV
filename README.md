# Staff Permission & Support PIN

**Developer:** Resellnom

A professional WHMCS administrator addon for granular staff access control, client/service permissions, field-level visibility, support department restrictions, and Support PIN verification.

## Features

- Custom administrator roles
- Granular page and action permissions
- Client and Client Summary access control
- Products & Services permissions
- Field-level View / Edit permissions
- Sensitive service data protection
- Support department restrictions
- Support PIN verification for protected staff actions
- Select All / Deselect All controls
- Staff activity and audit logs
- Super Admin full-access mode
- WHMCS core-file friendly architecture
- Clean, professional WHMCS-style administration UI

## Permission Examples

- clients.view
- clients.summary.view
- clients.profile.view
- services.view
- services.create
- services.modify
- services.suspend
- services.unsuspend
- services.terminate
- services.execute
- tickets.view
- tickets.reply
- billing.invoices.view
- billing.transactions.view
- billing.refund

## Field-Level Access

Service fields can have independent View and Edit permissions, including Domain, Username, Password, Server, IP Address, Next Due Date, Recurring Amount, Payment Method, Status and Custom Fields.

## Compatibility

Target: WHMCS 8.x/9.x. Exact compatibility will be verified against the deployed WHMCS version before production release.

## Licensing

The addon uses the WHMCS Software Licensing verification endpoint at `https://my.resellnom.com/modules/servers/licensing/verify.php`.

The client-facing addon configuration intentionally exposes **only the WHMCS License Key**. The licensing URL, MD5 verification secret, local-key interval, and grace period are not shown in the addon Configure screen.

### Private Licensing Configuration

For new installations, private licensing values should be supplied server-side through WHMCS `configuration.php` or environment variables. Do not commit the secret to GitHub.

Example in WHMCS `configuration.php`:

~~~php
define('LCV_LICENSE_SERVER_URL', 'https://my.resellnom.com/');
define('LCV_LICENSE_SECRET', 'YOUR_PRIVATE_MD5_VERIFICATION_SECRET');
define('LCV_LOCAL_KEY_DAYS', '30');
define('LCV_LICENSE_GRACE_DAYS', '5');
~~~

The actual secret must be replaced with the private MD5 verification secret configured on the WHMCS Software Licensing server.

Supported environment variables are:

- `LCV_LICENSE_SERVER_URL`
- `LCV_LICENSE_SECRET`
- `LCV_LOCAL_KEY_DAYS`
- `LCV_LICENSE_GRACE_DAYS`

Existing installations that already have the private licensing values stored in the addon database remain backward compatible.

- Local key validation avoids a remote request on every admin page load.
- Default remote re-check interval: 30 days.
- Domain, IP and installation-directory bindings are validated when the license is checked.
- Unauthorized installation or inactive license status locks the addon UI and protected hooks.
- Use the WHMCS License Manager to suspend, reissue or terminate a license when required.

## Client Setup

1. Install and activate the addon.
2. Open **Addon Modules → Configure**.
3. Enter the **WHMCS License Key**.
4. Save.
5. The addon validates the license against the licensing server.
6. When the license is Active and the domain/IP/directory bindings match, the addon unlocks automatically.

## Status

Production-readiness hardening / active development.

## Installation

Copy `modules/addons/lcv` into the WHMCS installation and activate the addon from the WHMCS admin area.

Do not edit WHMCS core files.