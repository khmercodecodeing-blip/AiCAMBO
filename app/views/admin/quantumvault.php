<?php require APP_ROOT . '/app/views/admin/layout_top.php'; ?>
<?php
$notices = $notices ?? [];
$catalog = $catalog ?? [];
$providerOrders = $providerOrders ?? [];
$invoices = $invoices ?? [];
$configured = $configured ?? false;
$schemaReady = $schemaReady ?? false;
$refreshed = $refreshed ?? false;
$balanceLabel = $balanceLabel ?? 'Unavailable';
?>

<style>
    .admin-main:has(.qv-admin) { min-width: 0; }
    .qv-admin { min-width: 0; }
    .qv-admin .admin-header { flex-wrap: wrap; gap: 16px; }
    .qv-admin h1 { font-size: 1.75rem; overflow-wrap: anywhere; letter-spacing: 0; }
    .qv-admin h2 { font-size: 1.1rem; margin-bottom: 16px; letter-spacing: 0; }
    .qv-band { padding: 24px 0; border-top: 1px solid var(--border-color); }
    .qv-form { display: grid; grid-template-columns: minmax(0, 2fr) repeat(2, minmax(0, 1fr)); gap: 16px; align-items: end; }
    .qv-form .form-group { min-width: 0; margin: 0; }
    .qv-form .form-control { width: 100%; min-width: 0; }
    .qv-form .qv-submit { grid-column: 1 / -1; justify-self: start; }
    .qv-admin .admin-table-wrapper { max-width: 100%; overflow-x: auto; }
    .qv-admin .admin-table { min-width: 760px; }
    .qv-admin th, .qv-admin td { max-width: 260px; overflow-wrap: anywhere; white-space: normal; }
    .qv-admin .btn { white-space: normal; text-align: center; }
    .qv-recovery { display: grid; gap: 8px; min-width: 210px; }
    .qv-recovery .form-control { min-width: 0; width: 100%; }
    .qv-retry { margin-top: 12px; }
    .qv-empty { color: var(--text-muted); padding: 12px 0; }
    @media (max-width: 760px) {
        .qv-form { grid-template-columns: minmax(0, 1fr); }
        .qv-admin h1 { font-size: 1.5rem; }
    }
</style>

<div class="qv-admin">
    <div class="admin-header">
        <h1>QuantumVault</h1>
        <?php if ($configured): ?>
            <form method="GET" action="<?= ADMIN_URL ?>/quantumvault">
                <input type="hidden" name="refresh" value="1">
                <button class="btn btn-ghost btn-sm" type="submit">Refresh supplier data</button>
            </form>
        <?php endif; ?>
    </div>

    <?php foreach (['success', 'error'] as $flashType): ?>
        <?php if ($message = get_flash($flashType)): ?>
            <div class="alert alert-<?= e($flashType) ?>" role="status"><?= e($message) ?></div>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php foreach ($notices as $notice): ?>
        <div class="alert alert-error" role="status"><?= e($notice) ?></div>
    <?php endforeach; ?>

    <?php if ($configured && !$schemaReady): ?>
        <div style="margin: 12px 0 24px 0;">
            <form method="POST" action="<?= ADMIN_URL ?>/quantumvault/migrate">
                <?= csrf_field() ?>
                <button class="btn btn-primary btn-sm" type="submit">Run Database Migration Now</button>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($configured): ?>
        <section class="qv-band" aria-labelledby="qv-catalog-title">
            <h2 id="qv-catalog-title">Supplier catalog</h2>
            <p class="qv-empty">Balance: <?= e($balanceLabel) ?></p>
            <?php if (!$catalog): ?>
                <p class="qv-empty"><?= $refreshed ? 'No supplier products available.' : 'Supplier data has not been refreshed.' ?></p>
            <?php else: ?>
                <div class="admin-table-wrapper" tabindex="0" role="region" aria-label="Supplier catalog">
                    <table class="admin-table">
                        <thead><tr><th>Product</th><th>Product key</th><th>Variant</th><th>Supplier price</th><th>Stock</th></tr></thead>
                        <tbody>
                        <?php foreach ($catalog as $row): ?>
                            <tr>
                                <td><?= e($row['name']) ?></td>
                                <td><?= e($row['product']) ?></td>
                                <td><?= e($row['variant_name']) ?><br><?= e($row['variant'] ?? '') ?></td>
                                <td><?= e($row['price']) ?> <?= e($row['currency']) ?></td>
                                <td><?= $row['stock'] ? 'In stock' : 'Out of stock' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>

        <?php if ($schemaReady && $catalog): ?>
            <section class="qv-band" aria-labelledby="qv-import-title">
                <h2 id="qv-import-title">Import inactive tool</h2>
                <form class="qv-form" method="POST" action="<?= ADMIN_URL ?>/quantumvault/import">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label" for="qv-mapping">Product / variant</label>
                        <select class="form-control" id="qv-mapping" name="mapping" required>
                            <option value="">Select product / variant</option>
                            <?php foreach ($catalog as $row): ?>
                                <option value="<?= e(json_encode(['product' => $row['product'], 'variant' => $row['variant']], JSON_INVALID_UTF8_SUBSTITUTE)) ?>" data-price="<?= e($row['price']) ?>" <?= !$row['stock'] || $row['currency'] !== 'USD' ? 'disabled' : '' ?>><?= e($row['name'] . ' / ' . ($row['variant_name'] ?: 'Standard') . ' / ' . $row['price'] . ' ' . $row['currency']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="qv-price">Selling price (USD)</label>
                        <input class="form-control" id="qv-price" type="number" name="price" min="0.01" max="99999999.99" step="0.01" placeholder="e.g. 5.00" required>
                        <small style="color: var(--text-muted); display: block; margin-top: 4px;" id="qv-price-hint">Customer price (must be &ge; supplier cost)</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="qv-cost">Maximum cost (USD)</label>
                        <input class="form-control" id="qv-cost" type="number" name="max_cost" min="0.0001" max="99999999.9999" step="0.0001" placeholder="e.g. 1.5000" required>
                        <small style="color: var(--text-muted); display: block; margin-top: 4px;" id="qv-cost-hint">Supplier base price limit</small>
                    </div>
                    <button class="btn btn-primary qv-submit" type="submit">Import inactive tool</button>
                </form>
            </section>
        <?php endif; ?>
    <?php endif; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const mappingSelect = document.getElementById('qv-mapping');
        const priceInput = document.getElementById('qv-price');
        const costInput = document.getElementById('qv-cost');
        const costHint = document.getElementById('qv-cost-hint');
        const priceHint = document.getElementById('qv-price-hint');

        if (mappingSelect && costInput) {
            mappingSelect.addEventListener('change', function() {
                const selected = this.options[this.selectedIndex];
                const price = selected.getAttribute('data-price');
                if (price && !isNaN(parseFloat(price))) {
                    const numPrice = parseFloat(price);
                    costInput.value = numPrice.toFixed(4);
                    costInput.min = numPrice.toFixed(4);
                    if (!priceInput.value || parseFloat(priceInput.value) < numPrice) {
                        priceInput.value = (numPrice * 1.5).toFixed(2);
                    }
                    priceInput.min = numPrice.toFixed(2);
                    if (costHint) costHint.textContent = 'Auto-filled with supplier price: $' + numPrice.toFixed(4);
                    if (priceHint) priceHint.textContent = 'Selling price (suggested +50% markup, min $' + numPrice.toFixed(2) + ')';
                }
            });
        }
    });
    </script>

    <section class="qv-band" aria-labelledby="qv-invoices-title">
        <h2 id="qv-invoices-title">Recent unresolved paid invoices</h2>
        <?php if (!$invoices): ?>
            <p class="qv-empty"><?= $schemaReady ? 'No unresolved supplier invoices.' : 'Supplier invoice actions are unavailable.' ?></p>
        <?php else: ?>
            <div class="admin-table-wrapper" tabindex="0" role="region" aria-label="Unresolved invoices">
                <table class="admin-table">
                    <thead><tr><th>Invoice / paid at</th><th>Course / mapping</th><th>Status / attempted at</th><th>Order reference</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td><?= e($invoice['invoice_no']) ?><br><?= e($invoice['paid_at'] ?? '') ?></td>
                            <td><a href="<?= ADMIN_URL ?>/courses/form?id=<?= (int) $invoice['course_id'] ?>">#<?= (int) $invoice['course_id'] ?></a><br><?= e($invoice['qv_product_key']) ?><br><?= e($invoice['qv_variant_key'] ?? '') ?></td>
                            <td><?= e($invoice['qv_status']) ?><br><?= e($invoice['qv_attempted_at'] ?? '') ?></td>
                            <td><?= e($invoice['qv_order_id'] ?? '') ?></td>
                            <td>
                                <form class="qv-recovery" method="POST" action="<?= ADMIN_URL ?>/quantumvault/recover">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="invoice_no" value="<?= e($invoice['invoice_no']) ?>">
                                    <label class="form-label">Supplier order ID
                                        <input class="form-control" type="text" name="order_id" value="<?= e($invoice['qv_order_id'] ?? '') ?>" maxlength="191" required>
                                    </label>
                                    <button class="btn btn-ghost btn-sm" type="submit">Recover existing order</button>
                                </form>
                                <?php if ($invoice['qv_status'] === 'pending' && empty($invoice['qv_order_id'])): ?>
                                    <form class="qv-retry" method="POST" action="<?= ADMIN_URL ?>/quantumvault/retry">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="invoice_no" value="<?= e($invoice['invoice_no']) ?>">
                                        <button class="btn btn-primary btn-sm" type="submit">Retry purchase &amp; delivery</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>

    <section class="qv-band" aria-labelledby="qv-orders-title">
        <h2 id="qv-orders-title">Recent supplier order references</h2>
        <?php if (!$providerOrders): ?>
            <p class="qv-empty"><?= $refreshed ? 'No supplier order metadata available.' : 'Supplier order metadata has not been refreshed.' ?></p>
        <?php else: ?>
            <div class="admin-table-wrapper" tabindex="0" role="region" aria-label="Supplier order metadata">
                <table class="admin-table">
                    <thead><tr><th>Order ID</th><th>Product key</th><th>Variant key</th><th>Status</th><th>Created at</th></tr></thead>
                    <tbody>
                    <?php foreach ($providerOrders as $order): ?>
                        <tr>
                            <?php foreach (['orderId', 'productKey', 'variantKey', 'status', 'createdAt'] as $field): ?>
                                <td><?= e($order[$field]) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>

    </main>
</div>
<script src="<?= asset('js/app.js') ?>?v=2.0.0"></script>
</body>
</html>