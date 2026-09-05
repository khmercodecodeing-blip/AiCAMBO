<?php

namespace App\Services;

use App\Models\InvoiceModel;

class LicenseDeliveryService
{
    private InvoiceModel $invoices;
    private \Closure $register;

    public function __construct(InvoiceModel $invoices, ?callable $register = null)
    {
        $this->invoices = $invoices;
        $this->register = \Closure::fromCallable($register ?? [LicenseClient::class, 'register']);
    }

    public function deliver(array $invoice): string
    {
        if (($invoice['payment_status'] ?? '') !== 'completed' || empty($invoice['license_key'])) {
            return 'not_required';
        }
        if (($invoice['license_delivery_status'] ?? '') === 'delivered') {
            return 'delivered';
        }

        $claimed = false;
        try {
            $claimed = $this->invoices->claimLicenseDelivery($invoice['invoice_no']);
            if (!$claimed) {
                $current = $this->invoices->getByInvoiceNo($invoice['invoice_no']);
                return $current['license_delivery_status'] ?? 'pending';
            }

            $delivered = ($this->register)($invoice) === true;
            $this->invoices->finishLicenseDelivery($invoice['invoice_no'], $delivered);
            return $delivered ? 'delivered' : 'pending';
        } catch (\Throwable $error) {
            error_log('License delivery needs retry: ' . $invoice['invoice_no']);
            if ($claimed) {
                try {
                    $this->invoices->finishLicenseDelivery($invoice['invoice_no'], false);
                } catch (\Throwable $ignored) {
                }
            }
            return 'pending';
        }
    }
}