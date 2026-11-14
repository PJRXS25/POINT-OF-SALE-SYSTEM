<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>
<section class="portal-page container">
    <div class="portal-heading"><div><span class="eyebrow">Customer account</span><h1>Profile settings</h1><p>Keep your contact and security information current.</p></div><a href="/shop" class="back-link">&larr; Back to shop</a></div>
    <form method="post" action="/customer/profile" class="auth-card profile-card">
        <?= csrf_field() ?>
        <h2>Personal details</h2>
        <div class="portal-form-grid">
            <div><label for="full_name">Full name</label><input id="full_name" type="text" name="full_name" value="<?= esc(old('full_name', $customer['full_name'])) ?>" maxlength="100" required></div>
            <div><label for="phone">Phone number</label><input id="phone" type="tel" name="phone" value="<?= esc(old('phone', $customer['phone'] ?? '')) ?>" maxlength="20" pattern="[0-9+() .-]{7,20}"></div>
            <div class="span-2"><label>Email address</label><input type="email" value="<?= esc($customer['email']) ?>" disabled><small>Email addresses cannot be changed here.</small></div>
        </div>
        <div class="profile-divider"><h2>Change password</h2><p>Leave these fields blank to keep your current password.</p></div>
        <div class="portal-form-grid">
            <div class="span-2"><label for="current_password">Current password</label><input id="current_password" type="password" name="current_password" autocomplete="current-password"></div>
            <div><label for="new_password">New password</label><input id="new_password" type="password" name="new_password" minlength="8" maxlength="72" autocomplete="new-password"></div>
            <div><label for="new_password_confirmation">Confirm new password</label><input id="new_password_confirmation" type="password" name="new_password_confirmation" minlength="8" maxlength="72" autocomplete="new-password"></div>
        </div>
        <button type="submit" class="shop-button">Save profile</button>
    </form>
</section>
<?= $this->endSection() ?>
