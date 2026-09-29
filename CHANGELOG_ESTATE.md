# LaundryPro Kenya — Estate Pivot Changelog

## v2.0 Estate Edition (2026-09)
### Security
- `admin_class.php` all actions migrated to PDO prepared statements; `extract($_POST)` removed; bcrypt `password_hash` with auto-rehash; session regeneration.
- `db_connect.php` `db()/db_query/db_fetch/db_insert/db_execute` wrapped `function_exists`; `Africa/Nairobi` default.
- `index.php` page whitelist + LFI guard; `ajax.php` action allowlist.
- `home.php/categories.php/inventory.php/supply.php/users.php/manage_inv.php/manage_laundry.php` converted from `$conn->query` + `while fetch_assoc` → `db_query` + `foreach`.
- `manage_receiving.php` retired (dead supplier/receiving code).
- `includes/security.php` added: CSRF, Kenyan phone `normalize_ke_phone` (070X/01XX/2547XX/+2547XX), RBAC, gate PIN.

### Database `migrate_estate_v2.sql`
- Money `double` → `DECIMAL(10,2)`; `gate_pin` on `laundry_list/pickup_schedule`; `qr_code/garment_photo` on `laundry_items`; indexes on estate/customer/driver/status/date; `audit_log`, `estate_subscriptions`, `estate_commissions`, driver `lat/lng/last_seen`.

### M-Pesa Daraja
- `includes/mpesa.php` STK Push `mpesa_stk_push()` + token + callback logging; `callback/mpesa_callback.php` handles STK result, updates `pay_status`, SMS confirm; idempotent guards.

### SMS/WhatsApp
- `includes/sms.php` AT `send_sms/send_status_sms/send_payment_confirmation_sms` demo-mode fallback to `sms_log`; `send_whatsapp` Meta Cloud.

### Operations
- `manage_laundry.php` gate PIN auto-gen, QR per item; `laundry.php` quick-status + STK dropdown.
- New pages: `receipt.php` (QR receipt + gate QR), `manifest.php` (batch by block/unit), `estate_statement.php` (commission ledger), `driver_pod.php` (gate PIN POD), `subscriptions.php/manage_subscription.php`, `settings_mpesa.php`, `cron/subscriptions.php`, `docs/RUNBOOK.md`, `includes/qr.php`.

### Verification
- `php -l` sweep: 0 errors across 30+ php files (inventory/home/admin_class fixed).
- Smoke test `Temp/opencode/smoke.php` covers customer→order→QR→STK demo→SMS demo→status cycle→commission (storage `LOCK TABLES` → `FOR UPDATE` fixed).
- Live DB: 5 estates, 10 customers, 8 orders, 21 items, 3 sms logs, 0 commissions yet (12% set for South B).

## Next (optional)
- Till/Paybill C2B reconciliation + KRA eTIMS fields
- PWA for riders with photo POD + offline sync
- USSD `*384*` via AT for non-smartphone estates
- Automated estate invoice email + PDF
