<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>
<section class="customer-auth container">
    <div class="auth-copy">
        <span class="eyebrow">Join StoreFlow</span>
        <h1>Your orders, organized.</h1>
        <p>Create an account to check out securely and review your purchase history anytime.</p>
        <a href="/shop" class="back-link">&larr; Continue browsing</a>
    </div>
    <div class="auth-card wide">
        <h2>Create your account</h2>
        <p>Enter your details below. Passwords must contain at least eight characters.</p>
        <form method="post" action="/customer/register">
            <?= csrf_field() ?>
            <div class="portal-form-grid">
                <div><label for="full_name">Full name</label><input id="full_name" type="text" name="full_name" value="<?= esc(old('full_name')) ?>" maxlength="100" required></div>
                <div><label for="phone">Phone number</label><input id="phone" type="tel" name="phone" value="<?= esc(old('phone')) ?>" maxlength="20" pattern="[0-9+() .-]{7,20}"></div>
                <div class="span-2"><label for="email">Email address</label><input id="email" type="email" name="email" value="<?= esc(old('email')) ?>" maxlength="100" autocomplete="email" required></div>
                <div><label for="password">Password</label><input id="password" type="password" name="password" minlength="8" maxlength="72" autocomplete="new-password" required></div>
                <div><label for="password_confirmation">Confirm password</label><input id="password_confirmation" type="password" name="password_confirmation" minlength="8" maxlength="72" autocomplete="new-password" required></div>
            </div>
            <button type="submit" class="shop-button full">Create account</button>
        </form>
        <p class="auth-switch">Already registered? <a href="/customer/login">Sign in</a></p>
    </div>
</section>
<?= $this->endSection() ?>
