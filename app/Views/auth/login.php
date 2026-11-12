<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="login-box">
    <h2>POS Login</h2>
    <form method="post" action="/login">
        <?= csrf_field() ?>
        <div class="field">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= old('username') ?>" required>
        </div>
        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn">Login</button>
    </form>
</div>
<?= $this->endSection() ?>
