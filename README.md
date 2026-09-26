# LCV — WHMCS Staff Access Control

LCV is a clean, native-feeling WHMCS administrator access-control addon focused on role, page, action, department and field-level permissions.

## Goals
- Granular staff permissions without modifying WHMCS core files.
- Page-level access such as `clientssummary.php`.
- Action permissions such as view, create, modify, suspend, unsuspend and terminate.
- Field-level view/edit control for client and service data.
- Select All / Deselect All permission UX.
- Audit logging for sensitive staff actions.
- Conservative, natural WHMCS-style administration UI.

## Status
Foundation scaffold — permission engine and UI are being implemented incrementally.

## Compatibility
Target: WHMCS 8.x/9.x. Exact compatibility will be verified against the deployed WHMCS version before production use.

## Installation
Copy `modules/addons/lcv` into the WHMCS installation and activate LCV from Setup > System Settings > Addon Modules.

Do not edit WHMCS core files.
