<?php require APP_ROOT . '/app/views/admin/layout_top.php'; ?>
<?php
$course = $course ?? null;
$isQuantumVault = (int) ($course['id'] ?? 0) > 3 && !empty($course['qv_product_key']);
?>

<?php if ($msg = get_flash('error')): ?>
    <div class="alert alert-error"><?= e($msg) ?></div>
<?php endif; ?>

<div class="admin-header">
    <div>
        <h1><?= $course ? 'Edit Course' : 'Add New Course' ?></h1>
        <div class="breadcrumb">
            <a href="<?= ADMIN_URL ?>/courses" style="color:var(--text-muted);">Courses</a> → <?= $course ? 'Edit' : 'New' ?>
        </div>
    </div>
</div>

<div class="glass-card" style="max-width:700px;padding:36px;">
    <form method="POST" action="<?= ADMIN_URL ?>/courses/save" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= $course['id'] ?? 0 ?>">

        <div class="form-group">
            <label class="form-label" for="title">Course Title *</label>
            <input type="text" id="title" name="title" class="form-control"
                   value="<?= e($course['title'] ?? '') ?>"
                   placeholder="e.g., Full-Stack Web Development" required>
        </div>

        <div class="form-group">
            <label class="form-label" for="description">Description</label>
            <textarea id="description" name="description" class="form-control" rows="5"
                      placeholder="Course description..."><?= e($course['description'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label class="form-label" for="type">Product Type</label>
            <select id="type" name="type" class="form-control">
                <option value="course" <?= ($course['type'] ?? 'course') === 'course' ? 'selected' : '' ?>>Course (Telegram Access)</option>
                <option value="tool" <?= ($course['type'] ?? '') === 'tool' ? 'selected' : '' ?>>Tool (Download Link)</option>
                <option value="ai" <?= ($course['type'] ?? '') === 'ai' ? 'selected' : '' ?>>Account AI Pro (Instant Delivery / API)</option>
            </select>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;">
            <div class="form-group">
                <label class="form-label" for="original_price">Original Price (Optional)</label>
                <input type="number" id="original_price" name="original_price" class="form-control"
                       value="<?= e($course['original_price'] ?? '') ?>"
                       placeholder="e.g. 99.00" step="0.01" min="0.01">
            </div>

            <div class="form-group">
                <label class="form-label" for="price">Selling Price *</label>
                <input type="number" id="price" name="price" class="form-control"
                       value="<?= e($course['price'] ?? '') ?>"
                       placeholder="e.g. 49.99" step="0.01" min="0.01" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="currency">Currency</label>
                <select id="currency" name="currency" class="form-control">
                    <option value="USD" <?= ($course['currency'] ?? 'USD') === 'USD' ? 'selected' : '' ?>>USD ($)</option>
                    <option value="KHR" <?= ($course['currency'] ?? '') === 'KHR' ? 'selected' : '' ?>>KHR (៛)</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="telegram_group_id">Telegram Group ID *</label>
            <input type="text" id="telegram_group_id" name="telegram_group_id" class="form-control"
                   value="<?= e($course['telegram_group_id'] ?? '') ?>"
                   placeholder="e.g., -1001234567890">
            <small class="text-muted" style="font-size:0.75rem;display:block;margin-top:4px;">
                Get this from @userinfobot or @RawDataBot in your Telegram group
            </small>
        </div>

        <div class="form-group" id="download-link-group">
            <label class="form-label" for="download_link">Download Link <?= $isQuantumVault ? '(Optional)' : '*' ?></label>
            <input type="text" id="download_link" name="download_link" class="form-control"
                   value="<?= e($course['download_link'] ?? '') ?>"
                   placeholder="e.g., https://example.com/files/tool.zip">
            <small class="text-muted" style="font-size:0.75rem;display:block;margin-top:4px;">
                <?php if ($isQuantumVault): ?>
                    QuantumVault: <?= e($course['qv_product_key'] ?? '') ?>
                    <?= e($course['qv_variant_key'] ?? '') ?>.
                    Maximum supplier cost: <?= e($course['qv_max_cost'] ?? '') ?> USD.
                <?php else: ?>
                    The link to download the tool after payment is completed.
                <?php endif; ?>
            </small>
        </div>

        <div class="form-group">
            <label class="form-label" for="thumbnail">Thumbnail Image</label>
            <input type="file" id="thumbnail" name="thumbnail" class="form-control"
                   accept="image/jpeg,image/png,image/gif,image/webp">
            <?php if (!empty($course['thumbnail'])): ?>
                <div class="mt-1" style="font-size:0.8rem;color:var(--text-muted);">
                    Current: <?= e($course['thumbnail']) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label class="form-label" for="video_url">Preview Video URL (YouTube or Direct Video URL)</label>
            <input type="url" id="video_url" name="video_url" class="form-control"
                   value="<?= e($course['video_url'] ?? '') ?>"
                   placeholder="e.g., https://www.youtube.com/watch?v=dQw4w9WgXcQ">
            <small class="text-muted" style="font-size:0.75rem;display:block;margin-top:4px;">
                Supports YouTube video links or direct links to video files (.mp4, .webm)
            </small>
        </div>

        <div class="form-group">
            <label class="form-check">
                <input type="checkbox" name="is_active" value="1"
                       <?= ($course['is_active'] ?? 1) ? 'checked' : '' ?>>
                <span>Course is active and visible</span>
            </label>
        </div>

        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                <span id="submit-btn-text"><?= $course ? 'Update Product' : 'Create Product' ?></span>
            </button>
            <a href="<?= ADMIN_URL ?>/courses" class="btn btn-ghost">Cancel</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const typeSelect = document.getElementById('type');
    const tgGroup = document.getElementById('telegram_group_id').closest('.form-group');
    const dlGroup = document.getElementById('download-link-group');
    const titleLabel = document.querySelector('label[for="title"]');
    const titleInput = document.getElementById('title');
    const submitBtnText = document.getElementById('submit-btn-text');

    function toggleFields() {
        const type = typeSelect.value;
        if (type === 'tool') {
            tgGroup.style.display = 'none';
            dlGroup.style.display = 'block';
            document.getElementById('download_link').toggleAttribute('required', <?= $isQuantumVault ? 'false' : 'true' ?>);
            document.getElementById('telegram_group_id').removeAttribute('required');
            if (titleLabel) titleLabel.textContent = 'Tool Title *';
            if (titleInput) titleInput.placeholder = 'e.g., MT4 Auto Trader Bot';
            if (submitBtnText) submitBtnText.textContent = '<?= $course ? "Update Tool" : "Create Tool" ?>';
        } else if (type === 'ai') {
            tgGroup.style.display = 'none';
            dlGroup.style.display = 'block';
            document.getElementById('download_link').removeAttribute('required');
            document.getElementById('telegram_group_id').removeAttribute('required');
            if (titleLabel) titleLabel.textContent = 'Account AI Title *';
            if (titleInput) titleInput.placeholder = 'e.g., Gemini 18 month link / ChatGPT Plus';
            if (submitBtnText) submitBtnText.textContent = '<?= $course ? "Update Product" : "Create Product" ?>';
        } else {
            tgGroup.style.display = 'block';
            dlGroup.style.display = 'none';
            document.getElementById('telegram_group_id').setAttribute('required', 'required');
            document.getElementById('download_link').removeAttribute('required');
            if (titleLabel) titleLabel.textContent = 'Course Title *';
            if (titleInput) titleInput.placeholder = 'e.g., Full-Stack Web Development';
            if (submitBtnText) submitBtnText.textContent = '<?= $course ? "Update Course" : "Create Course" ?>';
        }
    }

    typeSelect.addEventListener('change', toggleFields);
    toggleFields(); // run initially
});
</script>

<?php require APP_ROOT . '/app/views/admin/layout_bottom.php'; ?>
