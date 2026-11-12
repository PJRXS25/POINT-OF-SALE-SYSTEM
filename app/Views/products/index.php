<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="module-toolbar">
    <div class="module-intro">
        <h2>Product catalog</h2>
        <p>Manage pricing, images, and available inventory.</p>
    </div>
    <div class="module-actions"><a href="/products/new" class="btn">Add product</a></div>
</div>

<div class="card">
    <table class="table">
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td>
                        <div class="record-image">
                            <?php if (!empty($product['image']) && file_exists(FCPATH . $product['image'])): ?>
                                <img src="/<?= esc($product['image']) ?>" alt="<?= esc($product['name']) ?>">
                            <?php else: ?><?= esc(strtoupper(mb_substr($product['name'], 0, 2))) ?><?php endif; ?>
                        </div>
                    </td>
                    <td><div class="record-details"><strong><?= esc($product['name']) ?></strong><small>Product #<?= esc($product['id']) ?></small></div></td>
                    <td><strong>$<?= esc(number_format((float) $product['price'], 2)) ?></strong></td>
                    <td><span class="badge <?= (int) $product['stock_quantity'] === 0 ? 'danger' : ((int) $product['stock_quantity'] <= 10 ? 'warning' : 'success') ?>"><?= esc($product['stock_quantity']) ?> in stock</span></td>
                    <td><?= esc(date('Y-m-d', strtotime($product['created_at']))) ?></td>
                    <td><div class="action-group">
                        <form method="post" action="/products/image/<?= esc($product['id'], 'url') ?>" enctype="multipart/form-data" class="quick-image-form">
                            <?= csrf_field() ?>
                            <label class="btn image-action">
                                <?= !empty($product['image']) ? 'Replace image' : 'Upload image' ?>
                                <input type="file" name="image" accept="image/jpeg,image/png,image/webp" onchange="if (this.files.length) this.form.submit()" required>
                            </label>
                        </form>
                        <a href="/products/edit/<?= esc($product['id'], 'url') ?>" class="btn secondary">Edit</a>
                        <form method="post" action="/products/delete/<?= esc($product['id'], 'url') ?>" style="display:inline" onsubmit="return confirm('Delete this product?')">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn danger">Delete</button>
                        </form>
                    </div></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($products)): ?><tr><td colspan="6" class="empty-state">No products yet. Add your first product to get started.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>
