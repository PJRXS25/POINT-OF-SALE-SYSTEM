<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>
<section class="portal-page order-detail-page container">
    <a href="/shop/orders" class="order-back-link" aria-label="Back to my orders">&larr; My orders</a>

    <div class="portal-heading order-detail-heading">
        <div>
            <span class="eyebrow">Order #<?= esc(str_pad((string) $order['id'], 5, '0', STR_PAD_LEFT)) ?></span>
            <h1>Order details</h1>
            <p>Placed <?= esc(date('F j, Y \a\t g:i A', strtotime($order['created_at']))) ?></p>
        </div>
        <span class="order-status large <?= esc($order['status']) ?>">
            <i aria-hidden="true"></i><?= esc(ucfirst($order['status'])) ?>
        </span>
    </div>

    <div class="order-detail-layout">
        <div class="portal-card order-items-card">
            <div class="order-card-header">
                <div>
                    <h2>Order items</h2>
                    <p><?= esc(count($items)) ?> product<?= count($items) === 1 ? '' : 's' ?> in this order</p>
                </div>
                <span>Subtotal</span>
            </div>

            <div class="order-items-list">
                <?php foreach ($items as $item): ?>
                    <div class="order-item">
                        <span class="order-item-icon" aria-hidden="true"><?= esc(strtoupper(mb_substr($item['product_name'], 0, 2))) ?></span>
                        <div class="order-item-info">
                            <strong><?= esc($item['product_name']) ?></strong>
                            <span>$<?= esc(number_format((float) $item['unit_price'], 2)) ?> each &middot; Quantity <?= esc($item['quantity']) ?></span>
                        </div>
                        <strong class="order-item-price">$<?= esc(number_format((float) $item['subtotal'], 2)) ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <aside class="portal-card order-summary order-detail-summary">
            <h2>Order summary</h2>
            <div class="summary-line">
                <span>Payment method</span>
                <strong>Cash on pickup</strong>
            </div>
            <div class="summary-total">
                <span>Total</span>
                <strong>$<?= esc(number_format((float) $order['total_amount'], 2)) ?></strong>
            </div>

            <?php if ($order['pickup_notes']): ?>
                <div class="order-note">
                    <span>Pickup notes</span>
                    <p><?= esc($order['pickup_notes']) ?></p>
                </div>
            <?php endif; ?>

            <div class="pickup-message">
                <span aria-hidden="true">i</span>
                <p>We will update the status when your order is ready for pickup.</p>
            </div>
            <a href="/shop/orders" class="outline-button full">Back to my orders</a>
        </aside>
    </div>
</section>
<?= $this->endSection() ?>
