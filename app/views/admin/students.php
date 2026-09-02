<?php require APP_ROOT . '/app/views/admin/layout_top.php'; ?>

<div class="admin-header">
    <div>
        <h1>Students</h1>
        <div class="breadcrumb">Students who completed payment and enrolled</div>
    </div>
    <div class="btn btn-ghost btn-sm" style="cursor:default;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        <?= count($students) ?> Students
    </div>
</div>

<div class="glass-card" style="padding:0;overflow:hidden;">
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Student Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Course</th>
                    <th>Invoice</th>
                    <th>Enrolled</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr><td colspan="6" class="text-center text-muted" style="padding:40px;">No students enrolled yet</td></tr>
                <?php else: ?>
                    <?php foreach ($students as $s): ?>
                        <tr>
                            <td style="font-weight:600;color:var(--text-primary);">
                                <div class="d-flex align-center gap-1">
                                    <div style="width:32px;height:32px;background:var(--gradient-primary);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.8rem;font-weight:700;color:#fff;flex-shrink:0;">
                                        <?= strtoupper(substr($s['buyer_name'], 0, 1)) ?>
                                    </div>
                                    <?= e($s['buyer_name']) ?>
                                </div>
                            </td>
                            <td style="font-size:0.85rem;"><?= e($s['buyer_phone'] ?? '—') ?></td>
                            <td style="font-size:0.85rem;"><?= e($s['buyer_email'] ?? '—') ?></td>
                            <td class="truncate" style="max-width:180px;"><?= e($s['course_title']) ?></td>
                            <td style="font-size:0.8rem;font-family:monospace;"><?= e($s['invoice_no']) ?></td>
                            <td style="font-size:0.8rem;white-space:nowrap;">
                                <?= $s['paid_at'] ? date('M d, Y H:i', strtotime($s['paid_at'])) : '—' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require APP_ROOT . '/app/views/admin/layout_bottom.php'; ?>
