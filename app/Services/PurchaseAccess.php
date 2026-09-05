<?php

namespace App\Services;

class PurchaseAccess
{
    public static function remember(string $invoiceNo): void
    {
        $_SESSION['purchase_invoices'][$invoiceNo] = true;
        $_SESSION['purchase_invoices'] = array_slice($_SESSION['purchase_invoices'], -100, null, true);
    }

    public static function canView(array $invoice, array $session): bool
    {
        if (($session['purchase_invoices'][$invoice['invoice_no']] ?? false) === true) {
            return true;
        }

        $email = $session['user_email'] ?? null;
        return is_string($email) && $email !== ''
            && is_string($invoice['buyer_email'] ?? null)
            && strcasecmp($email, $invoice['buyer_email']) === 0;
    }
}