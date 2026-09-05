# Purchase Security and License Delivery

## Installation

These changes are local until explicitly deployed. Do not enable the new delivery flow before preparing the database and license API.

1. Back up the website database and the existing `key/api/register.php` endpoint. Use a staging copy first.
2. Upload `scratch/migrate_delivery.php` and run `php scratch/migrate_delivery.php` from the website root using the hosting PHP CLI. It adds `license_delivery_status` and `license_delivery_attempted_at` if missing. The script is CLI-only and does not register licenses or alter payment status.
3. Deploy the application files. The cPanel deployment task copies `deployment/register.php` over `key/api/register.php` when that directory exists. This is an intentional endpoint replacement. The wrapper loads `app/license-register-endpoint.php`; existing key configuration and credentials are not replaced. Manual deployments must copy the wrapper to the same path themselves.
4. Confirm the existing key service uses transactional MySQL/MariaDB tables and the application can reach its HTTPS URL with a valid CA certificate. Keep TLS verification enabled; configure the PHP CA bundle if needed.
5. Test a controlled purchase, webhook/poll race, failed delivery, retry, duplicate callback, and existing purchase on staging before enabling sales.

If the migration is missing, license delivery stays pending and logs a retry error. Payment confirmation and delivery are separate states. Do not mark a license delivered manually without checking the key registry.

## Behavior

- Payments require a positive, exact amount and matching USD/KHR currency. Webhooks independently query Bakong instead of trusting the callback amount. Each QR includes its invoice number.
- A signed-in buyer can access purchases matching their email. A guest can access invoices created in their browser session, limited to the most recent 100. Logout clears those grants. Old guest invoices and guests who lose their session require support; invoice numbers alone no longer grant access. External clients polling an invoice must preserve the checkout session cookie.
- Google sign-in checks the audience, issuer, expiry, verified email, subject and GIS double-submit CSRF token.
- Delivery runs after confirmed payment, when the buyer opens purchase details, or through the CSRF-protected retry button. Attempts are limited to once per minute per invoice. This is request-triggered recovery, not a background queue.
- License plans are the existing product IDs 1, 2 and 3 (30, 90 and 365 days). All checkout paths generate plan-bound keys. Other software products retain their download links and do not automatically receive a license key.
- Retrying a registration with the same key and original purchase note succeeds without recreating or extending the license. A different purchase reference, plan or device is rejected. The expiry remains anchored to the original payment date.
- Existing license registrations can be reconciled on retry when their original payment note matches. Edited or legacy notes may require support review. The migration does not assume older completed payments were delivered successfully.
- Payment details, receipts and account purchase history are sent with private/no-store headers. Pending license keys are not included in the success-page HTML or JavaScript.

## Checks

Run from the repository root:

```text
php tests/payment_security.php
php tests/purchase_access.php
php tests/license_delivery.php
php tests/license_registration.php
php tests/controller_security.php
php tests/receipt_delivery.php
```

These checks use synthetic data without production configuration or bank requests. Registration tests use in-memory SQLite, translating MySQL lock syntax. They do not prove MySQL concurrency, real OAuth, bank settlement or hosting configuration.

The loopback-only UI preview uses the real success template with a synthetic purchase:

```text
php -S 127.0.0.1:8097 -t . tests/ui/router.php
```

Open `http://127.0.0.1:8097/?state=pending` or `?state=delivered`. This is a visual test fixture, not a working checkout. Retry, receipt and software links in the fixture do not execute real purchases.

Before production approval, separately verify backups/restores, public-file access rules, rate limits, inventory fulfillment, expired-payment reconciliation and a full real-provider staging purchase. These changes are not a complete production-security certification.