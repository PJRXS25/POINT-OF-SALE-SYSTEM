<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>
<section class="landing-hero">
    <div class="container landing-hero-inner">
        <div class="landing-copy">
            <span class="eyebrow">Simple shopping, local pickup</span>
            <h1>Good products.<br><em>Zero hassle.</em></h1>
            <p>Discover what is available, order in a few clicks, and pay when you pick it up.</p>
            <div class="landing-actions"><a href="/shop/products" class="shop-button landing-primary">Browse products</a><?php if (!session()->get('customer_id')): ?><a href="/customer/register" class="landing-secondary">Create free account</a><?php else: ?><a href="/shop/orders" class="landing-secondary">View my orders</a><?php endif; ?></div>
            <div class="landing-trust"><span>Secure checkout</span><span>Live stock</span><span>Cash on pickup</span></div>
        </div>
        <div class="landing-visual" aria-hidden="true"><div class="visual-card main"><span>Available now</span><strong><?= esc($productCount) ?></strong><small>products ready for pickup</small></div><div class="visual-card accent">Easy<br>pickup</div><div class="visual-orbit"></div></div>
    </div>
</section>

<section class="landing-section container">
    <div class="landing-section-heading"><div><span class="eyebrow">Featured products</span><h2>Fresh from the catalog</h2></div><a href="/shop/products">View all products &rarr;</a></div>
    <?php if ($featuredProducts): ?><div class="product-grid featured-grid"><?php foreach ($featuredProducts as $product): ?><article class="product-card"><div class="product-photo"><?php $productImageUrl = media_url($product['image'] ?? null); if ($productImageUrl): ?><img src="<?= esc($productImageUrl, 'attr') ?>" alt="<?= esc($product['name']) ?>"><?php else: ?><span><?= esc(strtoupper(mb_substr($product['name'], 0, 2))) ?></span><?php endif; ?><small><?= esc($product['stock_quantity']) ?> available</small></div><div class="product-info"><h3><?= esc($product['name']) ?></h3><strong>$<?= esc(number_format((float) $product['price'], 2)) ?></strong><form method="post" action="/shop/cart/add" class="add-cart-form"><?= csrf_field() ?><input type="hidden" name="product_id" value="<?= esc($product['id']) ?>"><input type="hidden" name="quantity" value="1"><button type="submit" class="shop-button">Add to cart</button></form></div></article><?php endforeach; ?></div><?php else: ?><div class="portal-empty"><h3>Products coming soon</h3><p>Check back for newly available items.</p></div><?php endif; ?>
</section>

<section class="how-section"><div class="container"><div class="landing-section-heading centered"><div><span class="eyebrow">How it works</span><h2>From browsing to pickup</h2><p>Three straightforward steps—no complicated checkout.</p></div></div><div class="steps-grid"><article><span>01</span><h3>Choose your items</h3><p>Browse live inventory and add available products to your cart.</p></article><article><span>02</span><h3>Place your order</h3><p>Create an account, confirm your details, and submit securely.</p></article><article><span>03</span><h3>Pick up and pay</h3><p>Follow your order status and pay in cash when it is ready.</p></article></div></div></section>

<section class="landing-cta container"><div><span class="eyebrow">Ready to get started?</span><h2>Your next order is only a few clicks away.</h2></div><a href="/shop/products" class="shop-button landing-primary">Explore the catalog</a></section>
<?= $this->endSection() ?>
