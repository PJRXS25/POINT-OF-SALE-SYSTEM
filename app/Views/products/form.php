<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $fieldErrors = session()->getFlashdata('errors') ?? []; ?>
<div class="card">
    <h2><?= $mode === 'edit' ? 'Edit Product' : 'Add Product' ?></h2>
    <form method="post" action="<?= $mode === 'edit' ? '/products/edit/' . esc($product['id'], 'url') : '/products/new' ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-grid">
            <div class="field">
                <label>Name</label>
                <input type="text" name="name" value="<?= esc(old('name', $product['name'] ?? '')) ?>" required>
                <?php if (isset($fieldErrors['name'])): ?><span class="field-error"><?= esc($fieldErrors['name']) ?></span><?php endif; ?>
            </div>
            <div class="field">
                <label>Price</label>
                <input type="number" step="0.01" min="0.01" name="price" value="<?= esc(old('price', $product['price'] ?? '')) ?>" required>
                <?php if (isset($fieldErrors['price'])): ?><span class="field-error"><?= esc($fieldErrors['price']) ?></span><?php endif; ?>
            </div>
            <div class="field">
                <label>Stock Quantity</label>
                <input type="number" min="0" name="stock_quantity" value="<?= esc(old('stock_quantity', $product['stock_quantity'] ?? '')) ?>" required>
                <?php if (isset($fieldErrors['stock_quantity'])): ?><span class="field-error"><?= esc($fieldErrors['stock_quantity']) ?></span><?php endif; ?>
            </div>
            <div class="field">
                <label>Image</label>
                <input type="file" name="image" accept="image/jpeg,image/png,image/webp">
                <?php if (isset($fieldErrors['image'])): ?><span class="field-error"><?= esc($fieldErrors['image']) ?></span><?php endif; ?>
            </div>
        </div>
        <div style="margin-top: 18px; display:flex; gap:12px;">
            <button type="submit" class="btn">Save</button>
            <a href="/products" class="btn secondary">Cancel</a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
