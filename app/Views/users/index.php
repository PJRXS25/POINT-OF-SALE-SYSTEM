<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="module-toolbar">
    <div class="module-intro">
        <h2>Staff accounts</h2>
        <p>Manage the people who can access this system.</p>
    </div>
    <div class="module-actions"><a href="/users/new" class="btn">Add staff member</a></div>
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
                        <span class="record-image round">
                            <?php if (!empty($user['avatar']) && file_exists(FCPATH . $user['avatar'])): ?>
                                <img src="/<?= esc($user['avatar']) ?>" alt="<?= esc($user['full_name']) ?>">
                            <?php else: ?><?= esc(strtoupper(mb_substr($user['full_name'], 0, 2))) ?><?php endif; ?>
                        </span>
                    </td>
                    <td><?= esc($user['username']) ?></td>
                    <td><?= esc($user['full_name']) ?></td>
                    <td><?= esc(date('Y-m-d', strtotime($user['created_at']))) ?></td>
                    <td><div class="action-group">
                        <a href="/users/edit/<?= esc($user['id'], 'url') ?>" class="btn secondary">Edit</a>
                        <form method="post" action="/users/delete/<?= esc($user['id'], 'url') ?>" style="display:inline" onsubmit="return confirm('Delete this staff member?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn danger">Delete</button>
                        </form>
                    </div></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($users)): ?><tr><td colspan="5" class="empty-state">No staff accounts found.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
