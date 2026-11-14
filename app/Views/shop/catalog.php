<?= $this->extend('layouts/client') ?>
<?= $this->section('content') ?>
<section class="catalog-hero">
    <div class="container"><span class="eyebrow">Product catalog</span><h1>Find your next favorite.</h1><p>Browse everything currently available for pickup.</p></div>
</section>
<section class="catalog-section container">
    <div class="catalog-toolbar">
        <div><h2>Available products</h2><p>Only in-stock products are shown.</p></div>
        <div class="catalog-actions">
            <form method="get" action="/shop/products" class="catalog-search"><input type="search" name="q" value="<?= esc($search ?? '') ?>" maxlength="100" placeholder="Search products"><button type="submit">Search</button><?php if ($search): ?><a href="/shop/products">Clear</a><?php endif; ?></form>
            <a href="/shop/cart" class="shop-button cart-content-link">View cart<?php $catalogCart = session()->get('customer_cart'); $catalogCartCount = is_array($catalogCart) ? array_sum(array_map('intval', $catalogCart)) : 0; if ($catalogCartCount > 0): ?> (<?= esc($catalogCartCount) ?>)<?php endif; ?></a>
        </div>
    </div>
    <?php if ($products): ?>
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <article class="product-card">
                    <div class="product-photo"><?php $productImageUrl = media_url($product['image'] ?? null); if ($productImageUrl): ?><img src="<?= esc($productImageUrl, 'attr') ?>" alt="<?= esc($product['name']) ?>"><?php else: ?><span><?= esc(strtoupper(mb_substr($product['name'], 0, 2))) ?></span><?php endif; ?><small><?= esc($product['stock_quantity']) ?> available</small></div>
                    <div class="product-info"><h3><?= esc($product['name']) ?></h3><strong>$<?= esc(number_format((float) $product['price'], 2)) ?></strong><form method="post" action="/shop/cart/add" class="add-cart-form"><?= csrf_field() ?><input type="hidden" name="product_id" value="<?= esc($product['id']) ?>"><input type="number" name="quantity" value="1" min="1" max="<?= esc($product['stock_quantity']) ?>" aria-label="Quantity"><button type="submit" class="shop-button">Add to cart</button></form></div>
                </article>
            <?php endforeach; ?>
        </div>
        <?= $pager->only(['q'])->links('catalog') ?>
    <?php else: ?><div class="portal-empty"><h3>No products found</h3><p>Try another search or check back later.</p></div><?php endif; ?>
</section>
<?= $this->endSection() ?>
