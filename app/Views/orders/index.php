<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="module-toolbar">
    <div class="module-intro"><h2>Customer orders</h2><p>Review and fulfill orders placed through the customer portal.</p></div>
</div>
<div class="card">
    <form method="get" action="/orders" class="search-form">
        <input type="search" name="q" value="<?= esc($search) ?>" maxlength="100" placeholder="Order number, customer, or email">
        <select name="status"><option value="">All statuses</option><?php foreach ($statuses as $option): ?><option value="<?= esc($option) ?>" <?= $status === $option ? 'selected' : '' ?>><?= esc(ucfirst($option)) ?></option><?php endforeach; ?></select>
        <button type="submit" class="btn">Filter</button>
        <?php if ($search !== '' || $status !== ''): ?><a href="/orders" class="btn secondary">Clear</a><?php endif; ?>
    </form>
    <table class="table">
        <thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th><th>Placed</th><th>Action</th></tr></thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr><td><strong>#<?= esc(str_pad((string) $order['id'], 5, '0', STR_PAD_LEFT)) ?></strong></td><td><div class="record-details"><strong><?= esc($order['customer_name']) ?></strong><small><?= esc($order['customer_email']) ?></small></div></td><td><strong>$<?= esc(number_format((float) $order['total_amount'], 2)) ?></strong></td><td><span class="badge <?= $order['status'] === 'completed' ? 'success' : ($order['status'] === 'cancelled' ? 'danger' : 'warning') ?>"><?= esc(ucfirst($order['status'])) ?></span></td><td><?= esc(date('M j, Y g:i A', strtotime($order['created_at']))) ?></td><td><a href="/orders/<?= esc($order['id']) ?>" class="btn secondary">View</a></td></tr>
            <?php endforeach; ?>
            <?php if (!$orders): ?><tr><td colspan="6" class="empty-state">No online orders found.</td></tr><?php endif; ?>
        </tbody>
    </table>
    <?= $pager->only(['q', 'status'])->links('onlineOrders') ?>
</div>
<?= $this->endSection() ?>
