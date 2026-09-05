# QuantumVault Reseller Integration

This integration buys one supplier unit after a verified customer payment. It is local code until deployed; no real orders were purchased during development.

## Before Enabling

1. Confirm that you are authorized to resell the selected products and understand their warranty and account-transfer terms.
2. Back up the database and verify restoration on staging. Run `php scratch/migrate_quantumvault.php` from the project root with the hosting PHP CLI. The script is CLI-only and repeatable. It adds nullable mapping/delivery columns and a unique supplier order index, and expands `delivered_stock` to MEDIUMTEXT. It does not purchase anything or modify existing payment states.
3. Deploy the changed application files, including the new models, services, controller and views. Keep existing server configuration and secrets intact. PHP needs cURL, PDO MySQL, JSON, and a valid HTTPS CA bundle. Database tables must use InnoDB. The normal product and license flows do not require the new columns until using supplier features.
4. Obtain your own API key from the QuantumVault Telegram bot's Get API option. Set `QUANTUMVAULT_API_KEY` securely in the server-side PHP process environment. Do not put it in JavaScript, chat, source control, screenshots, public files, or query strings. The key can spend your wallet balance. Top up the supplier wallet separately.
5. Set `QUANTUMVAULT_ENABLED=1` in that same PHP environment to opt in. The default is disabled. Restart/reload PHP as required by your host. Setting this to `0` pauses new supplier checkouts and purchases; existing delivered accounts remain accessible. The admin supplier page also requires enabled configuration for import/recovery.
6. Open the existing private Admin area and select **QuantumVault**. Refresh supplier data, choose a product and variant, set the USD selling price and maximum supplier unit cost, then import. Imports are inactive tools with immutable supplier mappings. Review the product description, add a relevant thumbnail through Edit, and activate it only after staging verification. Each variant becomes a separate local product. Product IDs 1, 2 and 3 remain reserved for existing license plans.

There is no API-key entry form and no automatic environment/production deployment. If the hosting panel cannot set PHP environment variables, ask the hosting administrator to provision them outside the public document root. The loopback UI previews do not connect to your database or perform purchases.

## Delivery Rules

- All three checkout paths read the current supplier product/variant before creating the QR. Unavailable stock, disabled integration, wrong currency, or costs above the configured ceiling block checkout. Promotions cannot reduce the customer charge below the approved maximum supplier cost. Only USD supplier products are supported.
- The invoice stores its product key, variant, cost ceiling and initial `pending` state. Editing the catalog does not change existing invoice mappings.
- After Bakong verification, polling/webhooks attempt fulfillment. Opening authorized purchase details also resumes pending delivery. Delivery reads the persisted paid invoice and atomically claims `pending -> processing` before contacting the supplier. Pre-purchase failures return to `pending`, with a 60-second cooldown. They can be retried from the buyer detail page or Admin.
- One request purchases exactly one unit. The response is saved in the database before delivery parsing, and all supplier-defined fields are preserved as label/value text. Delivery becomes `delivered` only after storing the canonical supplier order ID and goods together. A unique index prevents the same supplier order from being assigned to two invoices.
- Once a purchase request starts, any timeout, rejection, malformed response, partial/empty result, or persistence failure blocks automatic repurchasing. It becomes `review`, or stays `processing` if the process/database fails. Even an old processing claim is never automatically reclaimed. This favors avoiding duplicate charges over automatic retries.
- Completed accounts appear on the owner's order details page, with an authenticated/session-authorized text download. Purchase history links back to those details. No supplier key, raw response or delivered credentials are returned by public catalog, admin metadata, or payment polling endpoints. Account views/downloads use private/no-store headers.
- Existing license and Telegram delivery remain separate. Imported accounts are stored under the existing Tool catalog and do not require a software download link.

## Recovering An Uncertain Purchase

1. Do not purchase again, and do not manually reset `processing` or `review` to `pending`.
2. In Admin > QuantumVault, refresh order metadata. Compare the supplier order reference, product, variant and timestamp with the paid invoice's attempted time. Supplier history shows the newest 100 orders; use the supplier dashboard/support for older records.
3. Enter the confirmed existing order reference next to the matching unresolved invoice and choose **Recover existing order**. This calls only `GET /orders/:orderId`, verifies the product/variant and that the delivery is not older than the attempt (five seconds clock tolerance), and saves the goods without another charge. Already-associated supplier orders are rejected.
4. For a rejection that definitely charged nothing, a crashed pre-purchase request, missing order, ambiguous concurrent orders, insufficient wallet balance, or no stock, contact supplier support and resolve the customer order manually. There is deliberately no reset-and-rebuy button for uncertain attempts. Refunds and manual state reconciliation are not automated.

The provider does not document an idempotency key, client invoice reference, reservation, or purchase-time maximum-price field. Matching recovery records therefore requires an administrator; the application must not guess among similar orders. The price ceiling is checked immediately before purchase but cannot prevent a supplier price change between the GET and POST. Wallet balance and stock can also change after customer checkout. Use conservative margins, a bounded wallet balance, active monitoring, and a customer support/refund process. Invoice records store sensitive delivered credentials and raw responses: restrict database/backups and define retention rules. This change does not introduce at-rest encryption.

Delivery is request-triggered, not a background queue. If no webhook, buyer request, or Admin retry arrives, pending delivery waits. Logs intentionally contain only a local invoice reference, not supplier response bodies or keys. Check the admin state and supplier history for diagnosis. Keep server clocks synchronized.

## Verification

Run these offline synthetic checks from the project root:

```text
php tests/quantumvault_client.php
php tests/quantumvault_delivery.php
php tests/quantumvault_checkout.php
php tests/quantumvault_access.php
php tests/quantumvault_admin.php
php tests/payment_security.php
php tests/controller_security.php
php tests/license_delivery.php
php tests/success_license_access.php
```

Tests cover documented API shapes, variants, cost guards, snapshot persistence, all checkout paths, paid-only delivery, duplicate claims, timeouts, recovery, unique orders, owner-only downloads, escaped goods, CSRF, inactive imports and rollback. SQLite and synthetic services do not prove MySQL concurrency, real bank settlement, live provider contracts or hosting secrets.

Using the existing loopback-only preview server, open `http://127.0.0.1:8097/?state=delivered&account=1` or change `state` to `pending` / `review`. These contain fake goods; their action links do not perform real purchases. The separate synthetic Admin fixture is `tests/ui/quantumvault.php` on a loopback PHP server and has no working import/recovery actions.

Before publishing, verify migration/restore on staging, a controlled real paid purchase (with explicit spending approval), webhook/poll races on MySQL, no-stock and low-wallet handling, a timeout followed by read-only recovery, owner access after login/logout, and disabled-provider behavior. Do not declare the production integration ready before those checks.