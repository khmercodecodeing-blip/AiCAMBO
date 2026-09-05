<?php require APP_ROOT . '/app/views/admin/layout_top.php'; ?>

<?php if ($msg = get_flash('success')): ?>
    <div class="alert alert-success"><?= e($msg) ?></div>
<?php endif; ?>
<?php if ($msg = get_flash('error')): ?>
    <div class="alert alert-error"><?= e($msg) ?></div>
<?php endif; ?>

<div class="admin-header">
    <div>
        <h1>Manage Courses</h1>
        <div class="breadcrumb">Manage your course catalog</div>
    </div>
    <a href="<?= ADMIN_URL ?>/courses/form" class="btn btn-primary btn-sm">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Course
    </a>
</div>

<div class="glass-card" style="padding:0;overflow:hidden;">
    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Title</th>
                    <th>Price</th>
                    <th>Students</th>
                    <th>Group / Link</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($courses)): ?>
                    <tr><td colspan="9" class="text-center text-muted" style="padding:40px;">No courses or tools yet. Click "Add Course" to get started.</td></tr>
                <?php else: ?>
                    <?php foreach ($courses as $c): ?>
                        <tr>
                            <td style="font-weight:600;color:var(--text-primary);">#<?= $c['id'] ?></td>
                            <td>
                                <?php if (($c['type'] ?? 'course') === 'tool'): ?>
                                    <span class="badge" style="background:rgba(16,185,129,0.1);color:var(--green-400);border:1px solid rgba(16,185,129,0.2);font-size:0.75rem;padding:3px 8px;">Tool</span>
                                <?php elseif (($c['type'] ?? '') === 'ai'): ?>
                                    <span class="badge" style="background:rgba(245,158,11,0.12);color:#d97706;border:1px solid rgba(245,158,11,0.25);font-size:0.75rem;padding:3px 8px;">AI Pro</span>
                                <?php else: ?>
                                    <span class="badge" style="background:rgba(59,130,246,0.1);color:var(--blue-400);border:1px solid rgba(59,130,246,0.2);font-size:0.75rem;padding:3px 8px;">Course</span>
                                <?php endif; ?>
                            </td>
                            <td style="color:var(--text-primary);font-weight:500;"><?= e($c['title']) ?></td>
                            <td style="color:var(--cyan-400);font-weight:600;">
                                <?php if (!empty($c['original_price']) && (float)$c['original_price'] > (float)$c['price']): ?>
                                    <span style="text-decoration:line-through;color:var(--text-muted);font-size:0.8rem;margin-right:6px;font-weight:400;">
                                        <?= format_price($c['original_price'], $c['currency']) ?>
                                    </span>
                                <?php endif; ?>
                                <?= format_price($c['price'], $c['currency']) ?>
                            </td>
                            <td style="font-weight:600;text-align:center;color:var(--text-primary);"><?= (int) ($c['student_count'] ?? 0) ?></td>
                            <td style="font-size:0.8rem;font-family:monospace;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                <?php if (($c['type'] ?? 'course') === 'tool'): ?>
                                    <a href="<?= e($c['download_link']) ?>" target="_blank" style="color:var(--cyan-400);" title="<?= e($c['download_link']) ?>">
                                        <?= e(substr($c['download_link'] ?? '', 0, 25)) . (strlen($c['download_link'] ?? '') > 25 ? '...' : '') ?>
                                    </a>
                                <?php else: ?>
                                    <?= e($c['telegram_group_id']) ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?= $c['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                                    <?= $c['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td style="font-size:0.8rem;"><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="<?= ADMIN_URL ?>/courses/form?id=<?= $c['id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
                                    <a href="<?= ADMIN_URL ?>/courses/delete/<?= $c['id'] ?>"
                                       class="btn btn-danger btn-sm"
                                       data-confirm-delete="<?= e($c['title']) ?>">Delete</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require APP_ROOT . '/app/views/admin/layout_bottom.php'; ?>
