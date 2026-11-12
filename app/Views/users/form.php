<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $fieldErrors = session()->getFlashdata('errors') ?? []; ?>
<div class="card">
    <h2><?= $mode === 'edit' ? 'Edit Staff' : 'Add Staff' ?></h2>
    <form method="post" action="<?= $mode === 'edit' ? '/users/edit/' . esc($user['id'], 'url') : '/users/new' ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-grid">
            <div class="field">
                <label>Username</label>
                <input type="text" name="username" value="<?= esc(old('username', $user['username'] ?? '')) ?>" required>
                <?php if (isset($fieldErrors['username'])): ?><span class="field-error"><?= esc($fieldErrors['username']) ?></span><?php endif; ?>
            </div>
            <div class="field">
                <label>Full Name</label>
                <input type="text" name="full_name" value="<?= esc(old('full_name', $user['full_name'] ?? '')) ?>" required>
                <?php if (isset($fieldErrors['full_name'])): ?><span class="field-error"><?= esc($fieldErrors['full_name']) ?></span><?php endif; ?>
            </div>
            <div class="field">
                <label>Password <?= $mode === 'edit' ? '(leave blank to keep current)' : '' ?></label>
                <input type="password" name="password" <?= $mode === 'edit' ? '' : 'required' ?>>
                <?php if (isset($fieldErrors['password'])): ?><span class="field-error"><?= esc($fieldErrors['password']) ?></span><?php endif; ?>
            </div>
            <div class="field">
                <label>Avatar</label>
                <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp">
                <?php if (isset($fieldErrors['avatar'])): ?><span class="field-error"><?= esc($fieldErrors['avatar']) ?></span><?php endif; ?>
            </div>
        </div>
        <div style="margin-top: 18px; display:flex; gap:12px;">
            <button type="submit" class="btn">Save</button>
            <a href="/users" class="btn secondary">Cancel</a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
