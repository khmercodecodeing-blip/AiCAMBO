<?php require APP_ROOT . '/app/views/admin/layout_top.php'; ?>

<div class="admin-header">
    <div>
        <h1>Invoices</h1>
        <div class="breadcrumb">Payment history and invoice management</div>
    </div>
    <div class="d-flex gap-1">
        <a href="<?= ADMIN_URL ?>/invoices" class="btn btn-sm <?= empty($_GET['status']) ? 'btn-primary' : 'btn-ghost' ?>">All</a>
        <a href="<?= ADMIN_URL ?>/invoices?status=completed" class="btn btn-sm <?= ($_GET['status'] ?? '') === 'completed' ? 'btn-primary' : 'btn-ghost' ?>">Completed</a>
        <a href="<?= ADMIN_URL ?>/invoices?status=pending" class="btn btn-sm <?= ($_GET['status'] ?? '') === 'pending' ? 'btn-primary' : 'btn-ghost' ?>">Pending</a>
        <a href="<?= ADMIN_URL ?>/invoices?status=expired" class="btn btn-sm <?= ($_GET['status'] ?? '') === 'expired' ? 'btn-primary' : 'btn-ghost' ?>">Expired</a>
    </div>
</div>

<div class="glass-card" style="padding:0;overflow:hidden;">
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Buyer</th>
                    <th>Course</th>
                    <th>Amount</th>
                    <th>Promo / Discount</th>
                    <th>Status</th>
                    <th>Telegram Link</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                    <tr><td colspan="8" class="text-center text-muted" style="padding:40px;">No invoices found</td></tr>
                <?php else: ?>
                    <?php foreach ($invoices as $inv): ?>
                        <tr>
                            <td style="font-weight:600;color:var(--text-primary);font-size:0.85rem;"><?= e($inv['invoice_no']) ?></td>
                            <td>
                                <div style="font-weight:500;color:var(--text-primary);"><?= e($inv['buyer_name']) ?></div>
                                <?php if (!empty($inv['buyer_phone'])): ?>
                                    <div style="font-size:0.75rem;color:var(--text-muted);"><?= e($inv['buyer_phone']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="truncate" style="max-width:180px;"><?= e($inv['course_title']) ?></td>
                            <td style="color:var(--cyan-400);font-weight:600;"><?= format_price($inv['amount'], $inv['currency']) ?></td>
                            <td>
                                <?php if (!empty($inv['promo_code'])): ?>
                                    <span style="background:rgba(16,185,129,0.15);color:var(--green-400);font-size:0.7rem;padding:2px 6px;border-radius:4px;font-weight:700;letter-spacing:0.5px;text-transform:uppercase;"><?= e($inv['promo_code']) ?></span>
                                    <div style="font-size:0.75rem;color:var(--green-400);margin-top:2px;">-<?= format_price($inv['discount_amount'], $inv['currency']) ?></div>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size:0.8rem;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?= e($inv['payment_status']) ?>"><?= e(ucfirst($inv['payment_status'])) ?></span>
                            </td>
                            <td>
                                <?php if (!empty($inv['telegram_link'])): ?>
                                    <a href="<?= e($inv['telegram_link']) ?>" target="_blank" class="btn btn-sm btn-ghost" style="font-size:0.75rem;padding:4px 10px;">
                                        View Link
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted" style="font-size:0.8rem;">—</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:0.8rem;white-space:nowrap;"><?= date('M d, Y H:i', strtotime($inv['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require APP_ROOT . '/app/views/admin/layout_bottom.php'; ?>
