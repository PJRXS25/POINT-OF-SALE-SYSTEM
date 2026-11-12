<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'POS System') ?></title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #1f2937;
        }
        a { text-decoration: none; color: inherit; }
        .app-shell {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: #111827;
            color: #fff;
            padding: 20px 16px;
            box-sizing: border-box;
        }
        .brand {
            font-size: 1.4rem;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .nav-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .nav-link {
            display: block;
            padding: 10px 12px;
            border-radius: 8px;
            background: rgba(255,255,255,0.04);
        }
        .nav-link:hover { background: rgba(255,255,255,0.1); }
        .nav-button {
            width: 100%;
            border: 0;
            color: inherit;
            text-align: left;
            font: inherit;
            cursor: pointer;
        }
        .content {
            flex: 1;
            padding: 26px;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            background: #fff;
            border-radius: 10px;
            padding: 14px 18px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin: 20px 0;
        }
        .stat-card {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            padding: 18px;
            border-radius: 12px;
        }
        .stat-card:nth-child(2) { background: linear-gradient(135deg, #059669, #047857); }
        .stat-card:nth-child(3) { background: linear-gradient(135deg, #dc2626, #b91c1c); }
        .stat-card:nth-child(4) { background: linear-gradient(135deg, #7c3aed, #6d28d9); }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        .table th, .table td {
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
            padding: 12px 8px;
        }
        .btn {
            display: inline-block;
            border: none;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.95rem;
            cursor: pointer;
            background: #2563eb;
            color: white;
        }
        .btn.secondary { background: #6b7280; }
        .btn.danger { background: #dc2626; }
        .btn.success { background: #16a34a; }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }
        .field {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 14px;
        }
        label { font-weight: 600; }
        .field-error { color: #b91c1c; font-size: 0.85rem; }
        input, select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-sizing: border-box;
        }
        .alert {
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-weight: 600;
        }
        .alert.success { background: #dcfce7; color: #166534; }
        .alert.error { background: #fee2e2; color: #991b1b; }
        .action-links a { margin-right: 10px; }
        .search-form { display: flex; gap: 8px; margin-bottom: 16px; }
        .search-form input { max-width: 360px; }
        .pagination { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 18px; padding: 0; list-style: none; }
        .pagination li a, .pagination li span { display: block; padding: 8px 11px; border: 1px solid #d1d5db; border-radius: 6px; background: #fff; }
        .pagination .active a, .pagination .active span { background: #2563eb; border-color: #2563eb; color: #fff; }
        .login-box {
            max-width: 420px;
            margin: 80px auto;
            background: #fff;
            padding: 26px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        @media (max-width: 768px) {
            .app-shell { flex-direction: column; }
            .sidebar { width: 100%; }
            .content { padding: 18px; }
        }
    </style>
</head>
<body>
    <div class="app-shell">
        <?php if (session()->get('user_id')): ?>
            <aside class="sidebar">
                <div class="brand">POS Panel</div>
                <div class="nav-group">
                    <a class="nav-link" href="/dashboard">Dashboard</a>
                    <a class="nav-link" href="/products">Products</a>
                    <a class="nav-link" href="/customers">Customers</a>
                    <a class="nav-link" href="/users">Staff</a>
                    <a class="nav-link" href="/sales/new">Record Sale</a>
                    <a class="nav-link" href="/sales">Sales History</a>
                    <form method="post" action="/logout">
                        <?= csrf_field() ?>
                        <button class="nav-link nav-button" type="submit">Logout</button>
                    </form>
                </div>
            </aside>
        <?php endif; ?>

        <main class="content">
            <?php if (session()->get('user_id')): ?>
                <div class="topbar">
                    <div>
                        <strong><?= esc(session()->get('user_name') ?? 'Staff') ?></strong>
                    </div>
                    <form method="post" action="/logout">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn secondary">Logout</button>
                    </form>
                </div>
            <?php endif; ?>

            <?php $success = session()->getFlashdata('success'); if ($success): ?>
                <div class="alert success"><?= esc($success) ?></div>
            <?php endif; ?>

            <?php $error = session()->getFlashdata('error'); if ($error): ?>
                <div class="alert error"><?= esc($error) ?></div>
            <?php endif; ?>

            <?php $errors = session()->getFlashdata('errors'); if (!empty($errors) && is_array($errors)): ?>
                <div class="alert error">
                    <ul>
                        <?php foreach ($errors as $message): ?>
                            <li><?= esc($message) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>
        </main>
    </div>
</body>
</html>
