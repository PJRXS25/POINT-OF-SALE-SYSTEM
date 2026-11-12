<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="module-toolbar">
    <div class="module-intro">
        <h2>Transaction history</h2>
        <p>Review sales by product, customer, or staff member.</p>
    </div>
    <div class="module-actions"><a href="/sales/new" class="btn">Record sale</a></div>
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
                <tr><td colspan="6" class="empty-state">No sales match your search.</td></tr>
            <?php endif; ?>
            <?php foreach ($sales as $sale): ?>
                <tr>
                    <td><div class="record-details"><strong><?= esc($sale['product_name'] ?? '-') ?></strong><small>Sale #<?= esc($sale['id']) ?></small></div></td>
                    <td><span class="badge"><?= esc($sale['customer_name'] ?? 'Walk-in Customer') ?></span></td>
                    <td><?= esc($sale['sold_by_name'] ?? '-') ?></td>
                    <td><?= esc($sale['quantity']) ?></td>
                    <td><strong>$<?= esc(number_format((float) $sale['total_price'], 2)) ?></strong></td>
                    <td><?= esc(date('Y-m-d', strtotime($sale['created_at']))) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?= $pager->only(['q'])->links('sales') ?>
</div>
<?= $this->endSection() ?>
