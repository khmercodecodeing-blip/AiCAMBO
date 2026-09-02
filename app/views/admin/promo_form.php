<?php require APP_ROOT . '/app/views/admin/layout_top.php'; ?>

<div class="admin-header">
    <div>
        <h1>Add Promo Code</h1>
        <div class="breadcrumb"><a href="<?= ADMIN_URL ?>/promos">Promo Codes</a> &bull; Create a new code</div>
    </div>
</div>

<div class="glass-card" style="max-width: 600px; margin: 0 auto; padding: 32px;">
    <form action="<?= ADMIN_URL ?>/promos/save" method="POST">
        <?= csrf_field() ?>

        <div class="form-group">
            <label class="form-label" for="code">Promo Code</label>
            <input type="text" 
                   id="code" 
                   name="code" 
                   class="form-control" 
                   placeholder="e.g. WELCOME10" 
                   style="text-transform: uppercase;"
                   required>
            <div style="font-size:0.75rem;color:var(--text-secondary);margin-top:6px;">Only letters and numbers. Automatically converted to uppercase.</div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label" for="discount_type">Discount Type</label>
                <select id="discount_type" name="discount_type" class="form-control" required>
                    <option value="percentage">Percentage (%)</option>
                    <option value="fixed">Fixed Amount ($)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="discount_value">Discount Value</label>
                <input type="number" 
                       id="discount_value" 
                       name="discount_value" 
                       class="form-control" 
                       step="0.01" 
                       min="0.01" 
                       placeholder="e.g. 10 or 5.00" 
                       required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label" for="max_uses">Max Uses (Limit)</label>
                <input type="number" 
                       id="max_uses" 
                       name="max_uses" 
                       class="form-control" 
                       min="1" 
                       placeholder="Unlimited if empty">
            </div>

            <div class="form-group">
                <label class="form-label" for="expires_at">Expires At</label>
                <input type="datetime-local" 
                       id="expires_at" 
                       name="expires_at" 
                       class="form-control">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="is_active">Status</label>
            <select id="is_active" name="is_active" class="form-control" required>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:32px;">
            <a href="<?= ADMIN_URL ?>/promos" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Promo Code</button>
        </div>
    </form>
</div>

<script>
// Prevent spaces in code field and force uppercase
document.getElementById('code').addEventListener('input', function(e) {
    this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
});
</script>

<?php require APP_ROOT . '/app/views/admin/layout_bottom.php'; ?>
