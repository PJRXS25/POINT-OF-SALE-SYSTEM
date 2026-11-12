<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="login-page">
    <section class="login-showcase" aria-label="Point of Sale overview">
        <div class="login-brand"><span class="login-logo" aria-hidden="true"></span> StoreFlow POS</div>
        <div class="login-message">
            <span class="login-kicker">Smart retail management</span>
            <h1>Run your store with confidence.</h1>
            <p>Manage products, customers, staff, and daily sales from one secure workspace.</p>
            <div class="login-benefits">
                <div><span>01</span> Real-time inventory tracking</div>
                <div><span>02</span> Fast and reliable checkout</div>
                <div><span>03</span> Clear sales insights</div>
            </div>
        </div>
        <p class="login-showcase-footer">Secure staff access &bull; <?= esc(date('Y')) ?></p>
    </section>

    <section class="login-panel">
        <div class="login-card">
            <div class="login-mobile-brand"><span class="login-logo" aria-hidden="true"></span> StoreFlow POS</div>
            <div class="login-card-heading">
                <span>Welcome back</span>
                <h2>Sign in to your account</h2>
                <p>Enter your staff credentials to continue.</p>
            </div>

            <form method="post" action="/login" class="login-form">
                <?= csrf_field() ?>
                <div class="field">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?= esc(old('username')) ?>" autocomplete="username" maxlength="50" placeholder="Enter your username" autofocus required>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" autocomplete="current-password" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn login-submit">Sign in securely</button>
            </form>

            <p class="login-security"><span aria-hidden="true">&#128274;</span> Authorized staff members only</p>
        </div>
    </section>
</div>
<?= $this->endSection() ?>
