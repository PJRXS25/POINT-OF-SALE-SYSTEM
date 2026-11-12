<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="topbar">
    <h2>Sales History</h2>
    <a href="/sales/new" class="btn">Record Sale</a>
</div>

<div class="card">
    <form method="get" action="/sales" class="search-form">
        <input type="search" name="q" value="<?= esc($search ?? '') ?>" maxlength="100" placeholder="Search product, customer, or staff">
        <button type="submit" class="btn">Search</button>
        <?php if (!empty($search)): ?>
            <a href="/sales" class="btn secondary">Clear</a>
        <?php endif; ?>
    </form>
    <table class="table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Customer</th>
                <th>Sold By</th>
                <th>Quantity</th>
                <th>Total</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($sales)): ?>
                <tr><td colspan="6">No sales found.</td></tr>
            <?php endif; ?>
            <?php foreach ($sales as $sale): ?>
                <tr>
                    <td><?= esc($sale['product_name'] ?? '-') ?></td>
                    <td><?= esc($sale['customer_name'] ?? 'Walk-in Customer') ?></td>
                    <td><?= esc($sale['sold_by_name'] ?? '-') ?></td>
                    <td><?= esc($sale['quantity']) ?></td>
                    <td>$<?= esc(number_format((float) $sale['total_price'], 2)) ?></td>
                    <td><?= esc(date('Y-m-d', strtotime($sale['created_at']))) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?= $pager->only(['q'])->links('sales') ?>
</div>
<?= $this->endSection() ?>
