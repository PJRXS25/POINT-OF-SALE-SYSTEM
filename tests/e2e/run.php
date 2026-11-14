<?php

declare(strict_types=1);

define('E2E_BASE_URL', rtrim(getenv('E2E_APP_BASE_URL') ?: 'http://127.0.0.1:8099', '/'));

$staffCookie = __DIR__ . '/../../writable/cache/e2e-staff.cookies';
$customerCookie = __DIR__ . '/../../writable/cache/e2e-customer.cookies';
$imagePath = __DIR__ . '/../../writable/cache/e2e-product.png';
$invalidImagePath = __DIR__ . '/../../writable/cache/e2e-invalid.php';
$oversizedImagePath = __DIR__ . '/../../writable/cache/e2e-oversized.png';
@unlink($staffCookie);
@unlink($customerCookie);
@unlink($imagePath);
@unlink($invalidImagePath);
@unlink($oversizedImagePath);

register_shutdown_function(static function () use ($staffCookie, $customerCookie, $imagePath, $invalidImagePath, $oversizedImagePath): void {
    @unlink($staffCookie);
    @unlink($customerCookie);
    @unlink($imagePath);
    @unlink($invalidImagePath);
    @unlink($oversizedImagePath);
});

function fail(string $message): never
{
    fwrite(STDERR, "FAIL: {$message}\n");
    exit(1);
}

function check(bool $condition, string $message): void
{
    if (!$condition) {
        fail($message);
    }

    fwrite(STDOUT, "PASS: {$message}\n");
}

/** @return array{status:int,body:string,url:string,headers:string} */
function request(string $method, string $path, string $cookieFile, array $data = [], bool $multipart = false): array
{
    $curl = curl_init(E2E_BASE_URL . $path);
    if ($curl === false) {
        fail('cURL could not be initialized.');
    }

    $headers = [];
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 8,
        CURLOPT_COOKIEJAR => $cookieFile,
        CURLOPT_COOKIEFILE => $cookieFile,
        CURLOPT_HEADERFUNCTION => static function ($handle, string $header) use (&$headers): int {
            $headers[] = trim($header);
            return strlen($header);
        },
        CURLOPT_TIMEOUT => 20,
    ]);

    if ($method === 'POST') {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $multipart ? $data : http_build_query($data));
        if (!$multipart) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        }
    }

    $body = curl_exec($curl);
    if ($body === false) {
        fail('Request failed for ' . $path . ': ' . curl_error($curl));
    }

    $result = [
        'status' => (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE),
        'body' => (string) $body,
        'url' => (string) curl_getinfo($curl, CURLINFO_EFFECTIVE_URL),
        'headers' => implode("\n", $headers),
    ];
    curl_close($curl);

    return $result;
}

/** @return array{name:string,value:string,response:array} */
function csrf(string $path, string $cookieFile): array
{
    $response = request('GET', $path, $cookieFile);
    check($response['status'] === 200, "GET {$path} returns 200");

    if (!preg_match('/<input[^>]+name="([^"]*csrf[^"]*)"[^>]+value="([^"]+)"/i', $response['body'], $match)) {
        fail("CSRF token was not found on {$path}.");
    }

    return ['name' => html_entity_decode($match[1]), 'value' => html_entity_decode($match[2]), 'response' => $response];
}

function postWithCsrf(string $formPath, string $postPath, string $cookieFile, array $data, bool $multipart = false): array
{
    $token = csrf($formPath, $cookieFile);
    $data[$token['name']] = $token['value'];
    return request('POST', $postPath, $cookieFile, $data, $multipart);
}

$db = new mysqli(
    getenv('E2E_DB_HOST') ?: '127.0.0.1',
    getenv('E2E_DB_USERNAME') ?: 'root',
    getenv('E2E_DB_PASSWORD') ?: '',
    getenv('E2E_DB_DATABASE') ?: 'pos_system_e2e',
    (int) (getenv('E2E_DB_PORT') ?: 3306),
);
if ($db->connect_errno) {
    fail('Test database connection failed: ' . $db->connect_error);
}

$health = request('GET', '/health', $staffCookie);
$healthPayload = json_decode($health['body'], true);
check($health['status'] === 200 && ($healthPayload['database'] ?? null) === 'connected', 'health endpoint confirms the isolated database');
check(str_contains(strtolower($health['headers']), 'x-frame-options: sameorigin'), 'secure response headers are active');

$invalidLogin = postWithCsrf('/login', '/login', $staffCookie, [
    'username' => 'admin',
    'password' => 'definitely-wrong-password',
]);
check(str_ends_with($invalidLogin['url'], '/login') && str_contains($invalidLogin['body'], 'Invalid username or password.'), 'invalid staff credentials are rejected');

$login = postWithCsrf('/login', '/login', $staffCookie, [
    'username' => 'admin',
    'password' => getenv('E2E_ADMIN_PASSWORD') ?: 'E2E-Admin-Password-123!',
]);
if (!($login['status'] === 200 && str_ends_with($login['url'], '/dashboard'))) {
    fwrite(STDERR, "Login debug: status={$login['status']} url={$login['url']} body=" . substr(strip_tags($login['body']), 0, 600) . "\n");
}
check($login['status'] === 200 && str_ends_with($login['url'], '/dashboard'), 'staff can sign in and reach the dashboard');

$imageBytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=', true);
check($imageBytes !== false && file_put_contents($imagePath, $imageBytes) !== false, 'a temporary product image is available for upload testing');
$mime = mime_content_type($imagePath) ?: 'image/jpeg';
check(file_put_contents($invalidImagePath, '<?php echo "unsafe";') !== false, 'an unsafe upload fixture is available for rejection testing');
check(file_put_contents($oversizedImagePath, str_repeat('A', (2 * 1024 * 1024) + 1)) !== false, 'an oversized upload fixture is available for rejection testing');

$negativeProduct = postWithCsrf('/products/new', '/products/new', $staffCookie, [
    'name' => 'Negative Price Product',
    'price' => '-1.00',
    'stock_quantity' => '-2',
]);
$negativeProductCount = (int) $db->query("SELECT COUNT(*) AS count FROM products WHERE name = 'Negative Price Product'")->fetch_assoc()['count'];
check(str_ends_with($negativeProduct['url'], '/products/new') && $negativeProductCount === 0, 'negative price and stock are rejected');

$unsafeUpload = postWithCsrf('/products/new', '/products/new', $staffCookie, [
    'name' => 'Unsafe Upload Product',
    'price' => '10.00',
    'stock_quantity' => '1',
    'image' => new CURLFile($invalidImagePath, 'application/x-httpd-php', 'payload.php'),
], true);
$unsafeUploadCount = (int) $db->query("SELECT COUNT(*) AS count FROM products WHERE name = 'Unsafe Upload Product'")->fetch_assoc()['count'];
check(str_ends_with($unsafeUpload['url'], '/products/new') && $unsafeUploadCount === 0, 'unsafe product uploads are rejected');

$oversizedUpload = postWithCsrf('/products/new', '/products/new', $staffCookie, [
    'name' => 'Oversized Upload Product',
    'price' => '10.00',
    'stock_quantity' => '1',
    'image' => new CURLFile($oversizedImagePath, 'image/png', 'oversized.png'),
], true);
$oversizedUploadCount = (int) $db->query("SELECT COUNT(*) AS count FROM products WHERE name = 'Oversized Upload Product'")->fetch_assoc()['count'];
check(str_ends_with($oversizedUpload['url'], '/products/new') && $oversizedUploadCount === 0, 'oversized product uploads are rejected');

$productName = 'E2E Test Product';
$product = postWithCsrf('/products/new', '/products/new', $staffCookie, [
    'name' => $productName,
    'price' => '9.99',
    'stock_quantity' => '10',
    'image' => new CURLFile($imagePath, $mime, basename($imagePath)),
], true);
if (!($product['status'] === 200 && str_ends_with($product['url'], '/products'))) {
    $visibleBody = preg_replace('~<(script|style)\b[^>]*>.*?</\1>~is', '', $product['body']) ?? $product['body'];
    $visibleBody = preg_replace('/\s+/', ' ', strip_tags($visibleBody)) ?? $visibleBody;
    fwrite(STDERR, "Product debug: status={$product['status']} url={$product['url']} headers={$product['headers']} body=" . substr(trim($visibleBody), 0, 1200) . "\n");
}
check($product['status'] === 200 && str_ends_with($product['url'], '/products'), 'staff can create a product with an image');

$productRow = $db->query("SELECT id, image, stock_quantity FROM products WHERE name = 'E2E Test Product' LIMIT 1")->fetch_assoc();
check(is_array($productRow) && str_starts_with((string) $productRow['image'], 'media:'), 'uploaded product image is stored through persistent media storage');
$productId = (int) $productRow['id'];
$mediaToken = $db->real_escape_string(substr((string) $productRow['image'], 6));
$mediaCount = (int) $db->query("SELECT COUNT(*) AS count FROM media_assets WHERE token = '{$mediaToken}'")->fetch_assoc()['count'];
check($mediaCount === 1, 'uploaded image bytes exist in the media table');

$crudProductCreate = postWithCsrf('/products/new', '/products/new', $staffCookie, [
    'name' => 'E2E CRUD Product',
    'price' => '15.00',
    'stock_quantity' => '4',
]);
check(str_ends_with($crudProductCreate['url'], '/products'), 'staff can create a product without an image');
$crudProductId = (int) $db->query("SELECT id FROM products WHERE name = 'E2E CRUD Product'")->fetch_assoc()['id'];
$crudProductEdit = postWithCsrf("/products/edit/{$crudProductId}", "/products/edit/{$crudProductId}", $staffCookie, [
    'name' => 'E2E CRUD Product Updated',
    'price' => '17.50',
    'stock_quantity' => '6',
]);
$crudProductRow = $db->query("SELECT name, price, stock_quantity FROM products WHERE id = {$crudProductId}")->fetch_assoc();
check(str_ends_with($crudProductEdit['url'], '/products') && $crudProductRow['name'] === 'E2E CRUD Product Updated' && (int) $crudProductRow['stock_quantity'] === 6, 'staff can edit a product');
$crudProductDelete = postWithCsrf('/products', "/products/delete/{$crudProductId}", $staffCookie, []);
$crudProductCount = (int) $db->query("SELECT COUNT(*) AS count FROM products WHERE id = {$crudProductId}")->fetch_assoc()['count'];
check(str_ends_with($crudProductDelete['url'], '/products') && $crudProductCount === 0, 'staff can delete an unreferenced product');

$customerCreate = postWithCsrf('/customers/new', '/customers/new', $staffCookie, [
    'full_name' => 'E2E Walk-in Customer',
    'email' => 'walkin-e2e@example.test',
    'phone' => '09123450000',
]);
check($customerCreate['status'] === 200 && str_ends_with($customerCreate['url'], '/customers'), 'staff can create a customer record');
$walkInId = (int) $db->query("SELECT id FROM customers WHERE email = 'walkin-e2e@example.test'")->fetch_assoc()['id'];

$invalidCustomer = postWithCsrf('/customers/new', '/customers/new', $staffCookie, [
    'full_name' => 'Invalid Email Customer',
    'email' => 'not-an-email',
    'phone' => 'invalid',
]);
$invalidCustomerCount = (int) $db->query("SELECT COUNT(*) AS count FROM customers WHERE full_name = 'Invalid Email Customer'")->fetch_assoc()['count'];
check(str_ends_with($invalidCustomer['url'], '/customers/new') && $invalidCustomerCount === 0, 'invalid customer email and phone are rejected');

$crudCustomerCreate = postWithCsrf('/customers/new', '/customers/new', $staffCookie, [
    'full_name' => 'E2E CRUD Customer',
    'email' => 'crud-e2e@example.test',
    'phone' => '09120000000',
]);
check(str_ends_with($crudCustomerCreate['url'], '/customers'), 'staff can create a disposable customer');
$crudCustomerId = (int) $db->query("SELECT id FROM customers WHERE email = 'crud-e2e@example.test'")->fetch_assoc()['id'];
$crudCustomerEdit = postWithCsrf("/customers/edit/{$crudCustomerId}", "/customers/edit/{$crudCustomerId}", $staffCookie, [
    'full_name' => 'E2E CRUD Customer Updated',
    'email' => 'crud-e2e-updated@example.test',
    'phone' => '09120000001',
]);
$crudCustomerName = $db->query("SELECT full_name FROM customers WHERE id = {$crudCustomerId}")->fetch_assoc()['full_name'];
check(str_ends_with($crudCustomerEdit['url'], '/customers') && $crudCustomerName === 'E2E CRUD Customer Updated', 'staff can edit a customer');
$crudCustomerDelete = postWithCsrf('/customers', "/customers/delete/{$crudCustomerId}", $staffCookie, []);
$crudCustomerCount = (int) $db->query("SELECT COUNT(*) AS count FROM customers WHERE id = {$crudCustomerId}")->fetch_assoc()['count'];
check(str_ends_with($crudCustomerDelete['url'], '/customers') && $crudCustomerCount === 0, 'staff can delete an unreferenced customer');

$unsafeAvatar = postWithCsrf('/users/new', '/users/new', $staffCookie, [
    'username' => 'unsafeavatar',
    'full_name' => 'Unsafe Avatar Staff',
    'password' => 'E2E-Staff-Password-789!',
    'avatar' => new CURLFile($invalidImagePath, 'application/x-httpd-php', 'avatar.php'),
], true);
$unsafeAvatarCount = (int) $db->query("SELECT COUNT(*) AS count FROM users WHERE username = 'unsafeavatar'")->fetch_assoc()['count'];
check(str_ends_with($unsafeAvatar['url'], '/users/new') && $unsafeAvatarCount === 0, 'unsafe staff avatar uploads are rejected');

$staffCreate = postWithCsrf('/users/new', '/users/new', $staffCookie, [
    'username' => 'e2estaff',
    'full_name' => 'E2E Staff Member',
    'password' => 'E2E-Staff-Password-123!',
]);
check(str_ends_with($staffCreate['url'], '/users'), 'staff can create another staff account');
$staffRow = $db->query("SELECT id, password FROM users WHERE username = 'e2estaff'")->fetch_assoc();
$staffId = (int) $staffRow['id'];
check(password_verify('E2E-Staff-Password-123!', $staffRow['password']), 'new staff passwords are hashed');
$duplicateStaff = postWithCsrf('/users/new', '/users/new', $staffCookie, [
    'username' => 'e2estaff',
    'full_name' => 'Duplicate Staff',
    'password' => 'E2E-Staff-Password-456!',
]);
$duplicateStaffCount = (int) $db->query("SELECT COUNT(*) AS count FROM users WHERE username = 'e2estaff'")->fetch_assoc()['count'];
check(str_ends_with($duplicateStaff['url'], '/users/new') && $duplicateStaffCount === 1, 'duplicate staff usernames are rejected');
$staffEdit = postWithCsrf("/users/edit/{$staffId}", "/users/edit/{$staffId}", $staffCookie, [
    'username' => 'e2estaff',
    'full_name' => 'E2E Staff Updated',
    'password' => '',
]);
$updatedStaff = $db->query("SELECT full_name, password FROM users WHERE id = {$staffId}")->fetch_assoc();
check(str_ends_with($staffEdit['url'], '/users') && $updatedStaff['full_name'] === 'E2E Staff Updated' && $updatedStaff['password'] === $staffRow['password'], 'staff can be edited without changing a blank password');

$adminId = (int) $db->query("SELECT id FROM users WHERE username = 'admin'")->fetch_assoc()['id'];
$selfDelete = postWithCsrf('/users', "/users/delete/{$adminId}", $staffCookie, []);
$adminCount = (int) $db->query("SELECT COUNT(*) AS count FROM users WHERE id = {$adminId}")->fetch_assoc()['count'];
check(str_ends_with($selfDelete['url'], '/users') && $adminCount === 1, 'the currently logged-in staff account cannot delete itself');

$sale = postWithCsrf('/sales/new', '/sales/new', $staffCookie, [
    'product_id' => (string) $productId,
    'customer_id' => (string) $walkInId,
    'quantity' => '2',
]);
check($sale['status'] === 200 && str_ends_with($sale['url'], '/sales'), 'staff can record an in-person sale');
$stockAfterSale = (int) $db->query("SELECT stock_quantity FROM products WHERE id = {$productId}")->fetch_assoc()['stock_quantity'];
check($stockAfterSale === 8, 'in-person sale reduces stock atomically');

$referencedProductDelete = postWithCsrf('/products', "/products/delete/{$productId}", $staffCookie, []);
$referencedProductCount = (int) $db->query("SELECT COUNT(*) AS count FROM products WHERE id = {$productId}")->fetch_assoc()['count'];
check(str_ends_with($referencedProductDelete['url'], '/products') && $referencedProductCount === 1, 'products referenced by sales cannot be deleted');

$referencedCustomerDelete = postWithCsrf('/customers', "/customers/delete/{$walkInId}", $staffCookie, []);
$referencedCustomerCount = (int) $db->query("SELECT COUNT(*) AS count FROM customers WHERE id = {$walkInId}")->fetch_assoc()['count'];
check(str_ends_with($referencedCustomerDelete['url'], '/customers') && $referencedCustomerCount === 1, 'customers referenced by sales cannot be deleted');

$saleId = (int) $db->query('SELECT id FROM sales ORDER BY id DESC LIMIT 1')->fetch_assoc()['id'];
$db->query("UPDATE sales SET sold_by = {$staffId} WHERE id = {$saleId}");
$referencedStaffDelete = postWithCsrf('/users', "/users/delete/{$staffId}", $staffCookie, []);
$referencedStaffCount = (int) $db->query("SELECT COUNT(*) AS count FROM users WHERE id = {$staffId}")->fetch_assoc()['count'];
check(str_ends_with($referencedStaffDelete['url'], '/users') && $referencedStaffCount === 1, 'staff accounts referenced by sales cannot be deleted');
$db->query("UPDATE sales SET sold_by = {$adminId} WHERE id = {$saleId}");
$staffDelete = postWithCsrf('/users', "/users/delete/{$staffId}", $staffCookie, []);
$staffCount = (int) $db->query("SELECT COUNT(*) AS count FROM users WHERE id = {$staffId}")->fetch_assoc()['count'];
check(str_ends_with($staffDelete['url'], '/users') && $staffCount === 0, 'staff can delete another account after references are removed');

$salesBeforeRejection = (int) $db->query('SELECT COUNT(*) AS count FROM sales')->fetch_assoc()['count'];
$insufficientSale = postWithCsrf('/sales/new', '/sales/new', $staffCookie, [
    'product_id' => (string) $productId,
    'customer_id' => '',
    'quantity' => '999',
]);
$salesAfterRejection = (int) $db->query('SELECT COUNT(*) AS count FROM sales')->fetch_assoc()['count'];
$stockAfterRejection = (int) $db->query("SELECT stock_quantity FROM products WHERE id = {$productId}")->fetch_assoc()['stock_quantity'];
check(str_ends_with($insufficientSale['url'], '/sales/new') && $salesAfterRejection === $salesBeforeRejection && $stockAfterRejection === 8, 'insufficient stock rejects the sale without changing inventory');

$zeroSale = postWithCsrf('/sales/new', '/sales/new', $staffCookie, [
    'product_id' => (string) $productId,
    'customer_id' => '',
    'quantity' => '0',
]);
$salesAfterZero = (int) $db->query('SELECT COUNT(*) AS count FROM sales')->fetch_assoc()['count'];
check(str_ends_with($zeroSale['url'], '/sales/new') && $salesAfterZero === $salesBeforeRejection, 'zero-quantity sales are rejected');

$register = postWithCsrf('/customer/register', '/customer/register', $customerCookie, [
    'full_name' => 'E2E Portal Customer',
    'email' => 'portal-e2e@example.test',
    'phone' => '09987654321',
    'password' => 'Portal-E2E-Password-123!',
    'password_confirmation' => 'Portal-E2E-Password-123!',
]);
check($register['status'] === 200 && str_contains($register['url'], '/shop'), 'customer can register and enter the portal');
$portalCustomer = $db->query("SELECT id, password FROM customers WHERE email = 'portal-e2e@example.test'")->fetch_assoc();
check(is_array($portalCustomer) && password_verify('Portal-E2E-Password-123!', $portalCustomer['password']), 'customer password is securely hashed');

$addCart = postWithCsrf('/shop/products', '/shop/cart/add', $customerCookie, [
    'product_id' => (string) $productId,
    'quantity' => '3',
]);
check($addCart['status'] === 200 && str_contains($addCart['body'], 'View cart (3)'), 'customer can add an available product to the cart');

$checkout = postWithCsrf('/shop/checkout', '/shop/checkout', $customerCookie, [
    'pickup_notes' => 'E2E pickup verification',
]);
check($checkout['status'] === 200 && preg_match('~/shop/orders/\d+$~', $checkout['url']) === 1, 'customer can place a cash-on-pickup order');
$orderRow = $db->query('SELECT id, status, total_amount FROM orders ORDER BY id DESC LIMIT 1')->fetch_assoc();
$orderId = (int) $orderRow['id'];
check($orderRow['status'] === 'pending' && abs((float) $orderRow['total_amount'] - 29.97) < 0.001, 'checkout stores the correct pending order total');
$stockAfterCheckout = (int) $db->query("SELECT stock_quantity FROM products WHERE id = {$productId}")->fetch_assoc()['stock_quantity'];
check($stockAfterCheckout === 5, 'online checkout reduces the same live inventory');

foreach (['confirmed', 'ready', 'completed'] as $status) {
    $update = postWithCsrf("/orders/{$orderId}", "/orders/{$orderId}/status", $staffCookie, ['status' => $status]);
    check($update['status'] === 200 && str_ends_with($update['url'], "/orders/{$orderId}"), "staff can move an order to {$status}");
}
$completedStatus = $db->query("SELECT status FROM orders WHERE id = {$orderId}")->fetch_assoc()['status'];
check($completedStatus === 'completed', 'order lifecycle persists the completed state');

$dashboard = request('GET', '/dashboard', $staffCookie);
check($dashboard['status'] === 200 && str_contains($dashboard['body'], '$49.95'), 'dashboard revenue combines the staff sale and completed online order');
check(str_contains($dashboard['body'], 'E2E Portal Customer'), 'dashboard recent orders use current customer data');

$secondAdd = postWithCsrf('/shop/products', '/shop/cart/add', $customerCookie, [
    'product_id' => (string) $productId,
    'quantity' => '1',
]);
check($secondAdd['status'] === 200, 'customer can start a second order');
$secondCheckout = postWithCsrf('/shop/checkout', '/shop/checkout', $customerCookie, ['pickup_notes' => 'Cancel test']);
check($secondCheckout['status'] === 200, 'second order is created for cancellation testing');
$secondOrderId = (int) $db->query('SELECT id FROM orders ORDER BY id DESC LIMIT 1')->fetch_assoc()['id'];
$stockBeforeCancel = (int) $db->query("SELECT stock_quantity FROM products WHERE id = {$productId}")->fetch_assoc()['stock_quantity'];
$cancel = postWithCsrf("/orders/{$secondOrderId}", "/orders/{$secondOrderId}/status", $staffCookie, ['status' => 'cancelled']);
check($cancel['status'] === 200, 'staff can cancel a pending online order');
$cancelled = $db->query("SELECT status FROM orders WHERE id = {$secondOrderId}")->fetch_assoc()['status'];
$stockAfterCancel = (int) $db->query("SELECT stock_quantity FROM products WHERE id = {$productId}")->fetch_assoc()['stock_quantity'];
check($cancelled === 'cancelled' && $stockAfterCancel === $stockBeforeCancel + 1, 'cancellation restores inventory exactly once');

$badCsrf = request('POST', '/products/new', $staffCookie, [
    'name' => 'Must Not Save',
    'price' => '1.00',
    'stock_quantity' => '1',
    'csrf_test_name' => 'invalid',
]);
check($badCsrf['status'] === 403, 'invalid CSRF submissions are rejected');
$notSaved = (int) $db->query("SELECT COUNT(*) AS count FROM products WHERE name = 'Must Not Save'")->fetch_assoc()['count'];
check($notSaved === 0, 'rejected request makes no database change');

$sessionCount = (int) $db->query('SELECT COUNT(*) AS count FROM ci_sessions')->fetch_assoc()['count'];
check($sessionCount >= 2, 'staff and customer sessions are persisted in the database');

$logout = postWithCsrf('/dashboard', '/logout', $staffCookie, []);
check($logout['status'] === 200 && str_ends_with($logout['url'], '/login'), 'staff can log out');
$protectedAfterLogout = request('GET', '/dashboard', $staffCookie);
check($protectedAfterLogout['status'] === 200 && str_ends_with($protectedAfterLogout['url'], '/login'), 'protected pages remain inaccessible after logout');

$customerLogout = postWithCsrf('/shop', '/customer/logout', $customerCookie, []);
check($customerLogout['status'] === 200 && str_ends_with($customerLogout['url'], '/shop'), 'customers can log out');
$profileAfterLogout = request('GET', '/customer/profile', $customerCookie);
check($profileAfterLogout['status'] === 200 && str_ends_with($profileAfterLogout['url'], '/customer/login'), 'customer profile remains inaccessible after logout');

fwrite(STDOUT, "E2E workflow completed successfully.\n");
