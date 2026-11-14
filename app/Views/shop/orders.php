<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>
<section class="portal-page container">
    <div class="portal-heading"><div><span class="eyebrow">Customer account</span><h1>My orders</h1><p>Track current orders and review previous purchases.</p></div><a href="/shop/products" class="shop-button">Shop again</a></div>
    <?php if ($orders): ?><div class="orders-list"><?php foreach ($orders as $order): ?><a href="/shop/orders/<?= esc($order['id']) ?>" class="portal-card order-row"><div><small>Order</small><strong>#<?= esc(str_pad((string) $order['id'], 5, '0', STR_PAD_LEFT)) ?></strong></div><div><small>Placed</small><strong><?= esc(date('M j, Y', strtotime($order['created_at']))) ?></strong></div><div><small>Total</small><strong>$<?= esc(number_format((float) $order['total_amount'], 2)) ?></strong></div><span class="order-status <?= esc($order['status']) ?>"><?= esc(ucfirst($order['status'])) ?></span><span>&rarr;</span></a><?php endforeach; ?></div><?= $pager->links('orders') ?><?php else: ?><div class="portal-empty"><h2>No orders yet</h2><p>Your completed checkouts will appear here.</p><a href="/shop/products" class="shop-button">Start shopping</a></div><?php endif; ?>
</section>
<?= $this->endSection() ?>
