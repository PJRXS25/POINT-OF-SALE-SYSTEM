<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>
<section class="portal-page container">
    <div class="portal-heading"><div><span class="eyebrow">Order #<?= esc(str_pad((string) $order['id'], 5, '0', STR_PAD_LEFT)) ?></span><h1>Order details</h1><p>Placed <?= esc(date('F j, Y \a\t g:i A', strtotime($order['created_at']))) ?></p></div><span class="order-status large <?= esc($order['status']) ?>"><?= esc(ucfirst($order['status'])) ?></span></div>
    <div class="checkout-layout"><div class="portal-card"><h2>Items</h2><?php foreach ($items as $item): ?><div class="order-item"><div><strong><?= esc($item['product_name']) ?></strong><span>$<?= esc(number_format((float) $item['unit_price'], 2)) ?> &times; <?= esc($item['quantity']) ?></span></div><strong>$<?= esc(number_format((float) $item['subtotal'], 2)) ?></strong></div><?php endforeach; ?></div><aside class="portal-card order-summary"><h2>Summary</h2><div><span>Payment</span><strong>Cash on pickup</strong></div><div class="summary-total"><span>Total</span><strong>$<?= esc(number_format((float) $order['total_amount'], 2)) ?></strong></div><?php if ($order['pickup_notes']): ?><div class="order-note"><span>Pickup notes</span><p><?= esc($order['pickup_notes']) ?></p></div><?php endif; ?><a href="/shop/orders" class="outline-button full">Back to orders</a></aside></div>
</section>
<?= $this->endSection() ?>
