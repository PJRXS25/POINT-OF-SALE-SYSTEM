<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="topbar">
    <h2>Customers</h2>
    <a href="/customers/new" class="btn">Add Customer</a>
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
                    <td><?= esc($customer['full_name']) ?></td>
                    <td><?= esc($customer['email']) ?></td>
                    <td><?= esc($customer['phone'] ?? '-') ?></td>
                    <td><?= esc(date('Y-m-d', strtotime($customer['created_at']))) ?></td>
                    <td class="action-links">
                        <a href="/customers/edit/<?= esc($customer['id'], 'url') ?>" class="btn secondary">Edit</a>
                        <form method="post" action="/customers/delete/<?= esc($customer['id'], 'url') ?>" style="display:inline" onsubmit="return confirm('Delete this customer?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
