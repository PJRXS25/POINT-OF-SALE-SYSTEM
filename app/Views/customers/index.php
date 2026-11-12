<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="module-toolbar">
    <div class="module-intro">
        <h2>Customer directory</h2>
        <p>Keep customer contact information organized.</p>
    </div>
    <div class="module-actions"><a href="/customers/new" class="btn">Add customer</a></div>
</div>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($customers as $customer): ?>
                <tr>
                    <td><div class="record-media"><span class="record-image round"><?= esc(strtoupper(mb_substr($customer['full_name'], 0, 2))) ?></span><span class="record-details"><strong><?= esc($customer['full_name']) ?></strong><small>Customer #<?= esc($customer['id']) ?></small></span></div></td>
                    <td><?= esc($customer['email']) ?></td>
                    <td><?= esc($customer['phone'] ?? '-') ?></td>
                    <td><?= esc(date('Y-m-d', strtotime($customer['created_at']))) ?></td>
                    <td><div class="action-group">
                        <a href="/customers/edit/<?= esc($customer['id'], 'url') ?>" class="btn secondary">Edit</a>
                        <form method="post" action="/customers/delete/<?= esc($customer['id'], 'url') ?>" style="display:inline" onsubmit="return confirm('Delete this customer?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn danger">Delete</button>
                        </form>
                    </div></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($customers)): ?><tr><td colspan="5" class="empty-state">No customers yet. Add a customer or continue using walk-in sales.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
