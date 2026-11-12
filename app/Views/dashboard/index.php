<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<h2>Dashboard</h2>
<div class="stats-grid">
    <div class="stat-card">
        <div>Total Products</div>
        <h3><?= esc($totalProducts ?? 0) ?></h3>
    </div>
    <div class="stat-card">
        <div>Total Customers</div>
        <h3><?= esc($totalCustomers ?? 0) ?></h3>
    </div>
    <div class="stat-card">
        <div>Total Staff</div>
        <h3><?= esc($totalStaff ?? 0) ?></h3>
    </div>
    <div class="stat-card">
        <div>Total Sales</div>
        <h3><?= esc($totalSales ?? 0) ?></h3>
    </div>
</div>

<div class="card">
    <h3>Sales Amount</h3>
    <h2>$<?= esc(number_format((float) ($salesTotal ?? 0), 2)) ?></h2>
</div>

<div class="stats-grid" style="margin-top: 20px;">
    <div class="card">
        <h3>Low Stock</h3>
        <?php if (!empty($lowStockProducts)): ?>
            <ul>
                <?php foreach ($lowStockProducts as $product): ?>
                    <li><?= esc($product['name']) ?> - <?= esc($product['stock_quantity']) ?> left</li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No low-stock products.</p>
        <?php endif; ?>
    </div>
    <div class="card">
        <h3>Recent Sales</h3>
        <?php if (!empty($recentSales)): ?>
            <ul>
                <?php foreach ($recentSales as $sale): ?>
                    <li><?= esc($sale['product_name']) ?> × <?= esc($sale['quantity']) ?> - $<?= esc(number_format((float) $sale['total_price'], 2)) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No sales recorded yet.</p>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
