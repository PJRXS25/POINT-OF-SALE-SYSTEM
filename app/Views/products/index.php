<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="topbar">
    <h2>Products</h2>
    <a href="/products/new" class="btn">Add Product</a>
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
                        <?php if (!empty($product['image']) && file_exists(FCPATH . $product['image'])): ?>
                            <img src="/<?= esc($product['image']) ?>" alt="" style="width: 50px; height:50px; object-fit:cover; border-radius:6px;">
                        <?php else: ?>
                            <span>No image</span>
                        <?php endif; ?>
                    </td>
                    <td><?= esc($product['name']) ?></td>
                    <td>$<?= esc(number_format((float) $product['price'], 2)) ?></td>
                    <td><?= esc($product['stock_quantity']) ?></td>
                    <td><?= esc(date('Y-m-d', strtotime($product['created_at']))) ?></td>
                    <td class="action-links">
                        <a href="/products/edit/<?= esc($product['id'], 'url') ?>" class="btn secondary">Edit</a>
                        <form method="post" action="/products/delete/<?= esc($product['id'], 'url') ?>" style="display:inline" onsubmit="return confirm('Delete this product?')">
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
