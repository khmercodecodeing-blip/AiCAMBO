<?php require APP_ROOT . '/app/views/admin/layout_top.php'; ?>

<div class="admin-header">
    <div>
        <h1>Promo Codes</h1>
        <div class="breadcrumb">Promotion and discount code management</div>
    </div>
    <div>
        <a href="<?= ADMIN_URL ?>/promos/form" class="btn btn-primary btn-sm">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="vertical-align:-2px;margin-right:4px;"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Add Promo Code
        </a>
    </div>
</div>

<?php if ($successMsg = get_flash('success')): ?>
    <div class="alert alert-success" style="margin-bottom: 20px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        <?= e($successMsg) ?>
    </div>
<?php endif; ?>
<?php if ($errorMsg = get_flash('error')): ?>
    <div class="alert alert-error" style="margin-bottom: 20px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
        <?= e($errorMsg) ?>
    </div>
<?php endif; ?>

<div class="glass-card" style="padding:0;overflow:hidden;">
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Type</th>
                    <th>Value</th>
                    <th>Uses / Limit</th>
                    <th>Expires</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($promos)): ?>
                    <tr><td colspan="7" class="text-center text-muted" style="padding:40px;">No promo codes found. Click "Add Promo Code" to create one.</td></tr>
                <?php else: ?>
                    <?php foreach ($promos as $promo): ?>
                        <tr>
                            <td style="font-weight:700;color:var(--text-primary);letter-spacing:0.5px;font-size:0.85rem;text-transform:uppercase;"><?= e($promo['code']) ?></td>
                            <td style="font-size:0.85rem;">
                                <?php if ($promo['discount_type'] === 'percentage'): ?>
                                    <span style="color:var(--blue-400);">Percentage (%)</span>
                                <?php else: ?>
                                    <span style="color:var(--cyan-400);">Fixed ($)</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight:600;color:var(--text-primary);">
                                <?php if ($promo['discount_type'] === 'percentage'): ?>
                                    <?= number_format($promo['discount_value'], 0) ?>%
                                <?php else: ?>
                                    <?= format_price($promo['discount_value']) ?>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:0.85rem;color:var(--text-secondary);">
                                <strong style="color:var(--text-primary);"><?= (int)$promo['uses_count'] ?></strong> 
                                / <?= $promo['max_uses'] !== null ? (int)$promo['max_uses'] : '∞' ?>
                            </td>
                            <td style="font-size:0.85rem;color:var(--text-secondary);">
                                <?php if ($promo['expires_at']): ?>
                                    <?php 
                                    $expired = strtotime($promo['expires_at']) < time(); 
                                    ?>
                                    <span style="<?= $expired ? 'color:var(--red-400);' : '' ?>">
                                        <?= date('M d, Y H:i', strtotime($promo['expires_at'])) ?>
                                        <?= $expired ? ' (Expired)' : '' ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">Never</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                $expired = $promo['expires_at'] && strtotime($promo['expires_at']) < time();
                                $active = (int)$promo['is_active'] === 1 && !$expired;
                                ?>
                                <span class="badge" style="background:<?= $active ? 'rgba(16,185,129,0.15);color:var(--green-400);' : 'rgba(239,68,68,0.15);color:var(--red-400);' ?>">
                                    <?= $active ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= ADMIN_URL ?>/promos/delete/<?= $promo['id'] ?>" 
                                   class="btn btn-sm btn-danger" 
                                   style="padding:4px 8px;font-size:0.75rem;" 
                                   data-confirm-delete="<?= e($promo['code']) ?>">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require APP_ROOT . '/app/views/admin/layout_bottom.php'; ?>
