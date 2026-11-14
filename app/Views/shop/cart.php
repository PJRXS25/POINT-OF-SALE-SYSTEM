<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>
<section class="portal-page container">
    <div class="portal-heading"><div><span class="eyebrow">Your selection</span><h1>Shopping cart</h1><p>Review quantities before checkout.</p></div><a href="/shop/products" class="back-link">&larr; Continue shopping</a></div>
    <?php if ($items): ?>
        <div class="cart-layout">
            <form method="post" action="/shop/cart/update" class="portal-card cart-items"><?= csrf_field() ?>
                <?php foreach ($items as $item): $product = $item['product']; ?>
                    <div class="cart-row">
                        <div class="cart-thumb"><?php $productImageUrl = media_url($product['image'] ?? null); if ($productImageUrl): ?><img src="<?= esc($productImageUrl, 'attr') ?>" alt=""><?php else: ?><span><?= esc(strtoupper(mb_substr($product['name'], 0, 2))) ?></span><?php endif; ?></div>
                        <div class="cart-name"><strong><?= esc($product['name']) ?></strong><span>$<?= esc(number_format((float) $product['price'], 2)) ?> each</span></div>
                        <input type="number" name="quantities[<?= esc($product['id']) ?>]" value="<?= esc($item['quantity']) ?>" min="0" max="<?= esc($product['stock_quantity']) ?>" aria-label="Quantity for <?= esc($product['name']) ?>">
                        <strong class="cart-subtotal">$<?= esc(number_format($item['subtotal'], 2)) ?></strong>
                        <button type="submit" formaction="/shop/cart/remove/<?= esc($product['id']) ?>" class="remove-button" title="Remove item">Remove</button>
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="outline-button">Update cart</button>
            </form>
            <aside class="portal-card order-summary"><h2>Order summary</h2><div><span>Items</span><strong><?= esc($itemCount) ?></strong></div><div class="summary-total"><span>Total</span><strong>$<?= esc(number_format($total, 2)) ?></strong></div><a href="/shop/checkout" class="shop-button full">Proceed to checkout</a><small>Payment: cash on pickup</small></aside>
        </div>
    <?php else: ?><div class="portal-empty"><h2>Your cart is empty</h2><p>Explore the catalog and add something you like.</p><a href="/shop/products" class="shop-button">Browse products</a></div><?php endif; ?>
</section>
<?= $this->endSection() ?>
