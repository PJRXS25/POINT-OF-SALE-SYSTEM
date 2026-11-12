<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $fieldErrors = session()->getFlashdata('errors') ?? []; ?>
<div class="card form-card">
    <div class="form-heading">
        <h2><?= $mode === 'edit' ? 'Edit product' : 'Create a product' ?></h2>
        <p><?= $mode === 'edit' ? 'Update product information and inventory.' : 'Add a new item to your product catalog.' ?></p>
    </div>
    <form method="post" action="<?= $mode === 'edit' ? '/products/edit/' . esc($product['id'], 'url') : '/products/new' ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-grid">
            <div class="field">
                <label>Name <span class="required-mark">*</span></label>
                <input type="text" name="name" value="<?= esc(old('name', $product['name'] ?? '')) ?>" required>
                <?php if (isset($fieldErrors['name'])): ?><span class="field-error"><?= esc($fieldErrors['name']) ?></span><?php endif; ?>
            </div>
            <div class="field">
                <label>Price <span class="required-mark">*</span></label>
                <input type="number" step="0.01" min="0.01" name="price" value="<?= esc(old('price', $product['price'] ?? '')) ?>" required>
                <?php if (isset($fieldErrors['price'])): ?><span class="field-error"><?= esc($fieldErrors['price']) ?></span><?php endif; ?>
            </div>
            <div class="field">
                <label>Stock quantity <span class="required-mark">*</span></label>
                <input type="number" min="0" name="stock_quantity" value="<?= esc(old('stock_quantity', $product['stock_quantity'] ?? '')) ?>" required>
                <?php if (isset($fieldErrors['stock_quantity'])): ?><span class="field-error"><?= esc($fieldErrors['stock_quantity']) ?></span><?php endif; ?>
            </div>
            <div class="field image-upload-field">
                <label>Product image</label>
                <div class="image-upload-box">
                    <div class="image-preview" id="imagePreview">
                        <?php if (!empty($product['image']) && file_exists(FCPATH . $product['image'])): ?>
                            <img src="/<?= esc($product['image']) ?>" alt="Current image for <?= esc($product['name']) ?>">
                        <?php else: ?>
                            <span>Image preview</span>
                        <?php endif; ?>
                    </div>
                    <div class="image-upload-control">
                        <input type="file" id="productImage" name="image" accept="image/jpeg,image/png,image/webp">
                        <small>JPG, PNG, or WEBP. Maximum file size: 2 MB.</small>
                        <?php if ($mode === 'edit' && !empty($product['image'])): ?><small>Leave this empty to keep the current image.</small><?php endif; ?>
                    </div>
                </div>
                <?php if (isset($fieldErrors['image'])): ?><span class="field-error"><?= esc($fieldErrors['image']) ?></span><?php endif; ?>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn"><?= $mode === 'edit' ? 'Save changes' : 'Create product' ?></button>
            <a href="/products" class="btn secondary">Cancel</a>
        </div>
    </form>
</div>
<script>
document.getElementById('productImage')?.addEventListener('change', function () {
    const file = this.files && this.files[0];
    const preview = document.getElementById('imagePreview');
    if (!file || !preview) return;

    const reader = new FileReader();
    reader.addEventListener('load', function () {
        preview.innerHTML = '';
        const image = document.createElement('img');
        image.src = String(reader.result);
        image.alt = 'Selected product image preview';
        preview.appendChild(image);
    });
    reader.readAsDataURL(file);
});
</script>
<?= $this->endSection() ?>
