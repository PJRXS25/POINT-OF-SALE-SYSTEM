<?php
$path = trim(service('uri')->getPath(), '/');
$customerName = (string) (session()->get('customer_name') ?? '');
$cssPath = FCPATH . 'assets/css/client.css';
$cssVersion = is_file($cssPath) ? (string) filemtime($cssPath) : '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'StoreFlow Shop') ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/client.css') ?>?v=<?= esc($cssVersion, 'url') ?>">
</head>
<body>
    <header class="shop-header">
        <div class="shop-nav container">
            <a href="/shop" class="shop-brand"><span class="shop-logo"></span> StoreFlow</a>
            <?php if (session()->get('customer_id')): ?>
                <nav aria-label="Customer navigation">
                    <a href="/shop/orders" class="<?= str_starts_with($path, 'shop/orders') ? 'active' : '' ?>">My orders</a>
                </nav>
            <?php endif; ?>
            <div class="shop-account">
                <?php if (session()->get('customer_id')): ?>
                    <a href="/customer/profile" class="customer-greeting">Hi, <?= esc(explode(' ', $customerName)[0]) ?></a>
                    <form method="post" action="/customer/logout">
                        <?= csrf_field() ?>
                        <button type="submit" class="text-button">Sign out</button>
                    </form>
                <?php else: ?>
                    <a href="/customer/login" class="text-button">Sign in</a>
                    <a href="/customer/register" class="shop-button small">Create account</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main>
        <?php $success = session()->getFlashdata('success'); if ($success): ?>
            <div class="portal-alert success container"><?= esc($success) ?></div>
        <?php endif; ?>
        <?php $error = session()->getFlashdata('error'); if ($error): ?>
            <div class="portal-alert error container"><?= esc($error) ?></div>
        <?php endif; ?>
        <?php $errors = session()->getFlashdata('errors'); if (is_array($errors) && $errors): ?>
            <div class="portal-alert error container"><ul><?php foreach ($errors as $message): ?><li><?= esc($message) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>
        <?= $this->renderSection('content') ?>
    </main>

    <footer class="shop-footer">
        <div class="container"><strong>StoreFlow</strong><span>Simple shopping. Secure checkout.</span><span>&copy; <?= esc(date('Y')) ?></span></div>
    </footer>
</body>
</html>
