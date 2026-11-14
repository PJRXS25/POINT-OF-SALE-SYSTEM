<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="module-toolbar">
    <div class="module-intro">
        <h2>Business overview</h2>
        <p>A quick summary of your store's current activity.</p>
    </div>
    <div class="module-actions"><a href="/sales/new" class="btn">Record a sale</a></div>
</div>

<div class="metric-grid">
    <article class="metric-card"><span class="metric-icon">PR</span><span class="metric-label">Products</span><strong class="metric-value"><?= esc($totalProducts ?? 0) ?></strong></article>
    <article class="metric-card green"><span class="metric-icon">CU</span><span class="metric-label">Customers</span><strong class="metric-value"><?= esc($totalCustomers ?? 0) ?></strong></article>
    <article class="metric-card violet"><span class="metric-icon">ST</span><span class="metric-label">Staff members</span><strong class="metric-value"><?= esc($totalStaff ?? 0) ?></strong></article>
    <article class="metric-card orange"><span class="metric-icon">SL</span><span class="metric-label">Completed sales</span><strong class="metric-value"><?= esc($totalSales ?? 0) ?></strong></article>
    <article class="metric-card cyan"><span class="metric-icon">OR</span><span class="metric-label">Online orders</span><strong class="metric-value"><?= esc($totalOnlineOrders ?? 0) ?></strong><small><?= esc($openOnlineOrders ?? 0) ?> currently open</small></article>
    <article class="metric-card rose"><span class="metric-icon">AM</span><span class="metric-label">Completed revenue</span><strong class="metric-value">$<?= esc(number_format((float) ($salesTotal ?? 0), 2)) ?></strong></article>
</div>

<div class="dashboard-grid">
    <section class="card stock-card">
        <div class="section-heading"><h3>Low-stock products</h3><span>10 units or fewer</span></div>
        <?php if (!empty($lowStockProducts)): ?>
            <ul class="activity-list">
                <?php foreach ($lowStockProducts as $product): ?>
                    <li class="activity-item">
                        <span class="activity-main"><strong><?= esc($product['name']) ?></strong><small>Inventory attention required</small></span>
                        <span class="badge <?= (int) $product['stock_quantity'] <= 3 ? 'danger' : 'warning' ?>"><?= esc($product['stock_quantity']) ?> left</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?><div class="empty-state">Inventory levels look healthy.</div><?php endif; ?>
    </section>

    <section class="card sales-card">
        <div class="section-heading"><h3>Recent sales</h3><a href="/sales">View history</a></div>
        <?php if (!empty($recentSales)): ?>
            <ul class="activity-list">
                <?php foreach ($recentSales as $sale): ?>
                    <li class="activity-item">
                        <span class="activity-main"><strong><?= esc($sale['product_name']) ?> &times; <?= esc($sale['quantity']) ?></strong><small>Processed by <?= esc($sale['sold_by_name'] ?? 'Staff') ?></small></span>
                        <span class="activity-value">$<?= esc(number_format((float) $sale['total_price'], 2)) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?><div class="empty-state">No sales have been recorded yet.</div><?php endif; ?>
    </section>

    <section class="card orders-card">
        <div class="section-heading"><h3>Recent online orders</h3><a href="/orders">Manage orders</a></div>
        <?php if (!empty($recentOnlineOrders)): ?>
            <ul class="activity-list">
                <?php foreach ($recentOnlineOrders as $order): ?>
                    <li class="activity-item">
                        <span class="activity-main"><strong>#<?= esc(str_pad((string) $order['id'], 5, '0', STR_PAD_LEFT)) ?> &middot; <?= esc($order['customer_name']) ?></strong><small><?= esc(date('M j, Y g:i A', strtotime($order['created_at']))) ?></small></span>
                        <span class="order-activity-meta"><strong>$<?= esc(number_format((float) $order['total_amount'], 2)) ?></strong><span class="badge <?= $order['status'] === 'completed' ? 'success' : ($order['status'] === 'cancelled' ? 'danger' : 'warning') ?>"><?= esc(ucfirst($order['status'])) ?></span></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?><div class="empty-state">No online orders have been placed yet.</div><?php endif; ?>
    </section>
</div>
<?= $this->endSection() ?>
