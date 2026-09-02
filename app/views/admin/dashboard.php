<?php require APP_ROOT . '/app/views/admin/layout_top.php'; ?>

<!-- Flash Messages -->
<?php if ($msg = get_flash('success')): ?>
    <div class="alert alert-success"><?= e($msg) ?></div>
<?php endif; ?>

<!-- Header -->
<div class="admin-header">
    <div>
        <h1>Dashboard</h1>
        <div class="breadcrumb">Welcome back, <?= e($_SESSION['admin_user'] ?? 'Admin') ?></div>
    </div>
    <a href="<?= ADMIN_URL ?>/courses/form" class="btn btn-primary btn-sm">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Course
    </a>
</div>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="glass-card stat-card">
        <div class="stat-icon blue">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
        </div>
        <div>
            <div class="stat-value"><?= $courseCount ?></div>
            <div class="stat-label">Total Courses</div>
        </div>
    </div>

    <div class="glass-card stat-card">
        <div class="stat-icon green">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
        </div>
        <div>
            <div class="stat-value"><?= format_price($stats['total_revenue']) ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>

    <div class="glass-card stat-card">
        <div class="stat-icon cyan">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
            <div class="stat-value"><?= $stats['completed_count'] ?></div>
            <div class="stat-label">Completed Sales</div>
        </div>
    </div>

    <div class="glass-card stat-card">
        <div class="stat-icon yellow">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div>
            <div class="stat-value"><?= $stats['pending_count'] ?></div>
            <div class="stat-label">Pending Payments</div>
        </div>
    </div>

    <div class="glass-card stat-card">
        <div class="stat-icon purple">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
        </div>
        <div>
            <div class="stat-value"><?= $stats['today_sales'] ?></div>
            <div class="stat-label">Today's Sales</div>
        </div>
    </div>

    <div class="glass-card stat-card">
        <div class="stat-icon green">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
        </div>
        <div>
            <div class="stat-value"><?= format_price($stats['today_revenue']) ?></div>
            <div class="stat-label">Today's Revenue</div>
        </div>
    </div>
</div>

<!-- Recent Invoices -->
<div class="glass-card" style="padding:0;overflow:hidden;">
    <div style="padding:20px 24px;border-bottom:1px solid var(--border-color);display:flex;justify-content:space-between;align-items:center;">
        <h3 style="font-size:1rem;font-weight:700;">Recent Invoices</h3>
        <a href="<?= ADMIN_URL ?>/invoices" class="btn btn-ghost btn-sm">View All</a>
    </div>
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Buyer</th>
                    <th>Course</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentInvoices)): ?>
                    <tr><td colspan="6" class="text-center text-muted" style="padding:40px;">No invoices yet</td></tr>
                <?php else: ?>
                    <?php foreach ($recentInvoices as $inv): ?>
                        <tr>
                            <td style="font-weight:600;color:var(--text-primary);"><?= e($inv['invoice_no']) ?></td>
                            <td><?= e($inv['buyer_name']) ?></td>
                            <td class="truncate" style="max-width:200px;"><?= e($inv['course_title']) ?></td>
                            <td style="color:var(--cyan-400);font-weight:600;"><?= format_price($inv['amount'], $inv['currency']) ?></td>
                            <td>
                                <span class="badge badge-<?= e($inv['payment_status']) ?>"><?= e(ucfirst($inv['payment_status'])) ?></span>
                            </td>
                            <td style="font-size:0.8rem;"><?= date('M d, H:i', strtotime($inv['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require APP_ROOT . '/app/views/admin/layout_bottom.php'; ?>
