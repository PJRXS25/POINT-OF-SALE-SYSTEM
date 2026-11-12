<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $fieldErrors = session()->getFlashdata('errors') ?? []; ?>
<div class="card form-card">
    <div class="form-heading">
        <h2><?= $mode === 'edit' ? 'Edit customer' : 'Create a customer' ?></h2>
        <p><?= $mode === 'edit' ? 'Keep this customer’s contact details up to date.' : 'Save customer details for faster future transactions.' ?></p>
    </div>
    <form method="post" action="<?= $mode === 'edit' ? '/customers/edit/' . esc($customer['id'], 'url') : '/customers/new' ?>">
        <?= csrf_field() ?>
        <div class="form-grid">
            <div class="field">
                <label>Full name <span class="required-mark">*</span></label>
                <input type="text" name="full_name" value="<?= esc(old('full_name', $customer['full_name'] ?? '')) ?>" required>
                <?php if (isset($fieldErrors['full_name'])): ?><span class="field-error"><?= esc($fieldErrors['full_name']) ?></span><?php endif; ?>
            </div>
            <div class="field">
                <label>Email address <span class="required-mark">*</span></label>
                <input type="email" name="email" value="<?= esc(old('email', $customer['email'] ?? '')) ?>" required>
                <?php if (isset($fieldErrors['email'])): ?><span class="field-error"><?= esc($fieldErrors['email']) ?></span><?php endif; ?>
            </div>
            <div class="field">
                <label>Phone</label>
                <input type="tel" name="phone" maxlength="20" pattern="[0-9+() .-]{7,20}" value="<?= esc(old('phone', $customer['phone'] ?? '')) ?>">
                <?php if (isset($fieldErrors['phone'])): ?><span class="field-error"><?= esc($fieldErrors['phone']) ?></span><?php endif; ?>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn"><?= $mode === 'edit' ? 'Save changes' : 'Create customer' ?></button>
            <a href="/customers" class="btn secondary">Cancel</a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
