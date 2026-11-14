<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>
<section class="portal-page container">
    <div class="portal-heading"><div><span class="eyebrow">Final step</span><h1>Confirm your order</h1><p>Review your details before placing the order.</p></div><a href="/shop/cart" class="back-link">&larr; Back to cart</a></div>
    <form method="post" action="/shop/checkout" class="checkout-layout"><?= csrf_field() ?>
        <div class="portal-card checkout-details"><h2>Pickup information</h2><div class="customer-summary"><strong><?= esc($customer['full_name']) ?></strong><span><?= esc($customer['email']) ?></span><?php if ($customer['phone']): ?><span><?= esc($customer['phone']) ?></span><?php endif; ?></div><label for="pickup_notes">Pickup notes (optional)</label><textarea id="pickup_notes" name="pickup_notes" maxlength="500" rows="5" placeholder="Add any helpful instructions..."><?= esc(old('pickup_notes')) ?></textarea><div class="payment-note"><strong>Cash on pickup</strong><span>Pay when you collect your order.</span></div></div>
        <aside class="portal-card order-summary"><h2>Your order</h2><?php foreach ($items as $item): ?><div><span><?= esc($item['product']['name']) ?> &times; <?= esc($item['quantity']) ?></span><strong>$<?= esc(number_format($item['subtotal'], 2)) ?></strong></div><?php endforeach; ?><div class="summary-total"><span>Total</span><strong>$<?= esc(number_format($total, 2)) ?></strong></div><button type="submit" class="shop-button full">Place order</button></aside>
    </form>
</section>
<?= $this->endSection() ?>
