<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $fieldErrors = session()->getFlashdata('errors') ?? []; ?>
<div class="card form-card">
    <div class="form-heading">
        <h2>Record a new sale</h2>
        <p>Select a product and quantity. Stock is updated automatically after checkout.</p>
    </div>
    <form method="post" action="/sales/new">
        <?= csrf_field() ?>
        <div class="form-grid">
            <div class="field">
                <label>Product <span class="required-mark">*</span></label>
                <select name="product_id" required>
                    <option value="">Select Product</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?= esc($product['id']) ?>" <?= old('product_id') == $product['id'] ? 'selected' : '' ?>><?= esc($product['name']) ?> (<?= esc($product['stock_quantity']) ?> left)</option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($fieldErrors['product_id'])): ?><span class="field-error"><?= esc($fieldErrors['product_id']) ?></span><?php endif; ?>
            </div>
            <div class="field">
                <label>Customer</label>
                <select name="customer_id">
                    <option value="">Walk-in Customer</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?= esc($customer['id']) ?>" <?= old('customer_id') == $customer['id'] ? 'selected' : '' ?>><?= esc($customer['full_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($fieldErrors['customer_id'])): ?><span class="field-error"><?= esc($fieldErrors['customer_id']) ?></span><?php endif; ?>
            </div>
            <div class="field">
                <label>Quantity <span class="required-mark">*</span></label>
                <input type="number" min="1" name="quantity" value="<?= old('quantity') ?? 1 ?>" required>
                <?php if (isset($fieldErrors['quantity'])): ?><span class="field-error"><?= esc($fieldErrors['quantity']) ?></span><?php endif; ?>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn">Complete sale</button>
            <a href="/sales" class="btn secondary">View History</a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
