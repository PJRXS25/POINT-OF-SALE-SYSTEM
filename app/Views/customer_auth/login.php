<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>
<section class="customer-auth container">
    <div class="auth-copy">
        <span class="eyebrow">Customer portal</span>
        <h1>Welcome back.</h1>
        <p>Sign in to complete checkout and track every order in one place.</p>
        <a href="/shop" class="back-link">&larr; Continue browsing</a>
    </div>
    <div class="auth-card">
        <h2>Customer sign in</h2>
        <p>Use the email and password connected to your account.</p>
        <form method="post" action="/customer/login">
            <?= csrf_field() ?>
            <label for="email">Email address</label>
            <input id="email" type="email" name="email" value="<?= esc(old('email')) ?>" autocomplete="email" required autofocus>
            <label for="password">Password</label>
            <input id="password" type="password" name="password" autocomplete="current-password" required>
            <button type="submit" class="shop-button full">Sign in</button>
        </form>
        <p class="auth-switch">New to StoreFlow? <a href="/customer/register">Create an account</a></p>
    </div>
</section>
<?= $this->endSection() ?>
