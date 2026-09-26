# Staff Permission & Support PIN

**Developer:** MD Mahfuz Reham

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

The addon uses the WHMCS Software Licensing verification endpoint configured in the addon settings. The license key and MD5 verification secret are stored in WHMCS addon configuration and are not included in this repository.

- Local key validation avoids a remote request on every admin page load.
- Default remote re-check interval: 30 days.
- Domain, IP and installation-directory bindings are validated when the license is checked.
- Unauthorized installation or inactive license status locks the addon UI and protected hooks.
- Use the WHMCS License Manager to suspend, reissue or terminate a license when required.

## Status

Production-readiness hardening / active development.

## Installation

Copy `modules/addons/lcv` into the WHMCS installation and activate the addon from the WHMCS admin area.

Do not edit WHMCS core files.
