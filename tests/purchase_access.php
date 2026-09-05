<?php

require_once dirname(__DIR__) . '/app/Services/GoogleIdentity.php';

use App\Services\GoogleIdentity;

$claims = [
    'aud' => 'test-client', 'iss' => 'https://accounts.google.com', 'exp' => 2000,
    'email_verified' => 'true', 'email' => 'buyer@example.com', 'sub' => 'test-subject',
];
if (!GoogleIdentity::validClaims($claims, 'test-client', 1000)) {
    throw new RuntimeException('Valid Google claims rejected.');
}
foreach (['aud' => 'other-client', 'iss' => 'other-issuer', 'exp' => 1000,
    'email_verified' => 'false', 'email' => 'invalid', 'sub' => ''] as $field => $value) {
    if (GoogleIdentity::validClaims(array_replace($claims, [$field => $value]), 'test-client', 1000)) {
        throw new RuntimeException('Invalid Google claim accepted: ' . $field);
    }
}
foreach ([[null, null], ['', ''], ['token', 'other'], [[], 'token']] as [$cookie, $posted]) {
    if (GoogleIdentity::validCsrf($cookie, $posted)) {
        throw new RuntimeException('Invalid Google CSRF accepted.');
    }
}
if (!GoogleIdentity::validCsrf('token', 'token')) {
    throw new RuntimeException('Valid Google CSRF rejected.');
}
echo "PASS: Google claim and CSRF checks\n";

require_once dirname(__DIR__) . '/app/Services/PurchaseAccess.php';

$invoice = ['invoice_no' => 'INV-TEST', 'buyer_email' => 'buyer@example.com'];
foreach ([
    'anonymous' => [[], false],
    'other account' => [['user_email' => 'other@example.com'], false],
    'owner account' => [['user_email' => 'buyer@example.com'], true],
    'owner case' => [['user_email' => 'Buyer@example.com'], true],
    'guest session' => [['purchase_invoices' => ['INV-TEST' => true]], true],
    'other invoice' => [['purchase_invoices' => ['INV-OTHER' => true]], false],
] as $name => [$session, $expected]) {
    if (App\Services\PurchaseAccess::canView($invoice, $session) !== $expected) {
        throw new RuntimeException('Purchase access failed: ' . $name);
    }
}
if (App\Services\PurchaseAccess::canView(['invoice_no' => 'INV-GUEST', 'buyer_email' => null], [])) {
    throw new RuntimeException('Anonymous guest invoice exposed.');
}
$_SESSION = [];
App\Services\PurchaseAccess::remember('INV-TEST');
if (!App\Services\PurchaseAccess::canView($invoice, $_SESSION)) {
    throw new RuntimeException('New purchase not remembered.');
}
echo "PASS: Purchase ownership and guest session checks\n";