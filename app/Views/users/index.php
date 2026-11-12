<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="topbar">
    <h2>Staff</h2>
    <a href="/users/new" class="btn">Add Staff</a>
</div>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Avatar</th>
                <th>Username</th>
                <th>Full Name</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td>
                        <?php if (!empty($user['avatar']) && file_exists(FCPATH . $user['avatar'])): ?>
                            <img src="/<?= esc($user['avatar']) ?>" alt="" style="width: 42px; height:42px; border-radius:50%; object-fit:cover;">
                        <?php else: ?>
                            <span>No avatar</span>
                        <?php endif; ?>
                    </td>
                    <td><?= esc($user['username']) ?></td>
                    <td><?= esc($user['full_name']) ?></td>
                    <td><?= esc(date('Y-m-d', strtotime($user['created_at']))) ?></td>
                    <td class="action-links">
                        <a href="/users/edit/<?= esc($user['id'], 'url') ?>" class="btn secondary">Edit</a>
                        <form method="post" action="/users/delete/<?= esc($user['id'], 'url') ?>" style="display:inline" onsubmit="return confirm('Delete this staff member?')">
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
