<?php
require_once '../config.php';

// Handle Login / Logout
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_logged_in']);
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_btn'])) {
    if ($_POST['username'] === $store_settings['admin_user'] && $_POST['password'] === $store_settings['admin_pass']) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $login_error = "Invalid username or password!";
    }
}

if (empty($_SESSION['admin_logged_in'])):
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Admin Login - Paws & Claws</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 flex items-center justify-center min-h-screen">
    <form method="POST" class="bg-white p-8 rounded-2xl shadow-xl w-full max-w-sm">
        <h2 class="text-2xl font-extrabold text-center mb-6">Admin Control Panel</h2>
        <?php if (!empty($login_error)): ?><p class="bg-red-100 text-red-700 p-2 rounded text-sm mb-4"><?= $login_error ?></p><?php endif; ?>
        <input type="text" name="username" placeholder="Username (admin)" class="w-full border rounded-lg px-3 py-2 mb-3" required>
        <input type="password" name="password" placeholder="Password (admin123)" class="w-full border rounded-lg px-3 py-2 mb-4" required>
        <button type="submit" name="login_btn" class="w-full bg-orange-500 text-white font-bold py-2.5 rounded-lg hover:bg-orange-600">Login to Dashboard</button>
        <a href="../index.php" class="block text-center text-xs text-gray-500 mt-4 hover:underline">← Back to Storefront</a>
    </form>
</body>
</html>
<?php exit; endif;

// Handle Admin Actions (Add/Delete Product, Update Order, Save Settings)
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $img_url = trim($_POST['image_url']);
        if (!empty($_FILES['image_file']['name'])) {
            $ext = pathinfo($_FILES['image_file']['name'], PATHINFO_EXTENSION);
            $filename = 'prod_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['image_file']['tmp_name'], '../uploads/' . $filename);
            $img_url = 'uploads/' . $filename;
        }
        if (empty($img_url)) $img_url = 'https://images.unsplash.com/photo-1583511655857-d19b40a7a54e?auto=format&fit=crop&w=600&q=80';
        
        $stmt = $pdo->prepare("INSERT INTO products (name, description, price, category, pet_type, stock, image_url) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$_POST['name'], $_POST['description'], $_POST['price'], $_POST['category'], $_POST['pet_type'], $_POST['stock'], $img_url]);
        $msg = "Product added successfully!";
    }
    if (isset($_POST['update_stock'])) {
        $stmt = $pdo->prepare("UPDATE products SET stock = ?, price = ? WHERE id = ?");
        $stmt->execute([$_POST['stock'], $_POST['price'], $_POST['product_id']]);
        $msg = "Product updated!";
    }
    if (isset($_POST['delete_product'])) {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$_POST['product_id']]);
        $msg = "Product deleted!";
    }
    if (isset($_POST['update_order_status'])) {
        $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
        $stmt->execute([$_POST['order_status'], $_POST['order_id']]);
        $msg = "Order status updated!";
    }
    if (isset($_POST['save_settings'])) {
        $stmt = $pdo->prepare("UPDATE settings SET store_name=?, currency=?, currency_symbol=?, primary_color=?, stripe_public_key=?, stripe_secret_key=?, paypal_client_id=?, admin_pass=? WHERE id=1");
        $stmt->execute([$_POST['store_name'], $_POST['currency'], $_POST['currency_symbol'], $_POST['primary_color'], $_POST['stripe_public_key'], $_POST['stripe_secret_key'], $_POST['paypal_client_id'], $_POST['admin_pass']]);
        $msg = "Store & Payment settings saved!";
        $store_settings = $pdo->query("SELECT * FROM settings WHERE id = 1")->fetch();
    }
}

$total_sales = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE payment_status='Paid'")->fetchColumn();
$total_orders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$low_stock = $pdo->query("SELECT COUNT(*) FROM products WHERE stock < 5")->fetchColumn();
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
$orders = $pdo->query("SELECT * FROM orders ORDER BY id DESC")->fetchAll();
$symbol = htmlspecialchars($store_settings['currency_symbol']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Admin Control Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">

<div class="bg-gray-900 text-white px-6 py-4 flex justify-between items-center">
    <div class="font-extrabold text-xl">🐾 <?= htmlspecialchars($store_settings['store_name']) ?> — Admin</div>
    <div class="flex gap-4 text-sm">
        <a href="../index.php" target="_blank" class="bg-gray-700 px-3 py-1.5 rounded hover:bg-gray-600">View Live Store ↗</a>
        <a href="?logout=1" class="bg-red-600 px-3 py-1.5 rounded hover:bg-red-700">Logout</a>
    </div>
</div>

<div class="max-w-7xl mx-auto p-6 space-y-8">
    <?php if ($msg): ?>
        <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-xl font-semibold"><?= $msg ?></div>
    <?php endif; ?>

    <!-- Overview Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border">
            <p class="text-sm text-gray-500 font-semibold">Total Revenue</p>
            <p class="text-3xl font-extrabold text-green-600 mt-1"><?= $symbol ?><?= number_format($total_sales, 2) ?></p>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border">
            <p class="text-sm text-gray-500 font-semibold">Total Orders</p>
            <p class="text-3xl font-extrabold text-gray-900 mt-1"><?= $total_orders ?></p>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border">
            <p class="text-sm text-gray-500 font-semibold">Low Stock Alerts (&lt; 5 items)</p>
            <p class="text-3xl font-extrabold text-amber-600 mt-1"><?= $low_stock ?></p>
        </div>
    </div>

    <!-- Add New Product -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border">
        <h2 class="text-xl font-extrabold mb-4">➕ Add New Pet Product</h2>
        <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="text" name="name" placeholder="Product Name *" class="border rounded-lg px-3 py-2" required>
            <input type="number" step="0.01" name="price" placeholder="Price (e.g. 29.99) *" class="border rounded-lg px-3 py-2" required>
            <input type="number" name="stock" placeholder="Stock Quantity *" value="20" class="border rounded-lg px-3 py-2" required>
            
            <select name="category" class="border rounded-lg px-3 py-2">
                <option value="Dog Food">Dog Food</option>
                <option value="Cat Toys">Cat Toys</option>
                <option value="Grooming">Grooming</option>
                <option value="Healthcare">Healthcare</option>
                <option value="Accessories">Accessories</option>
            </select>
            
            <select name="pet_type" class="border rounded-lg px-3 py-2">
                <option value="Dog">Dog</option>
                <option value="Cat">Cat</option>
                <option value="Bird">Bird</option>
                <option value="Small Pet">Small Pet</option>
            </select>

            <input type="text" name="image_url" placeholder="Image URL (or upload file below)" class="border rounded-lg px-3 py-2">
            <div class="md:col-span-2">
                <input type="text" name="description" placeholder="Short Product Description" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <input type="file" name="image_file" accept="image/*" class="text-sm">
            </div>
            <div class="md:col-span-3">
                <button type="submit" name="add_product" class="bg-orange-500 text-white font-bold px-6 py-2.5 rounded-lg hover:bg-orange-600">Add Product to Store</button>
            </div>
        </form>
    </div>

    <!-- Manage Products & Inventory -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border overflow-x-auto">
        <h2 class="text-xl font-extrabold mb-4">📦 Products & Inventory Control</h2>
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b text-xs uppercase text-gray-500">
                    <th class="py-3">Product</th>
                    <th>Category / Pet</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y text-sm">
                <?php foreach ($products as $p): ?>
                <tr>
                    <td class="py-3 flex items-center gap-3">
                        <img src="../<?= htmlspecialchars($p['image_url']) ?>" onerror="this.src='<?= htmlspecialchars($p['image_url']) ?>'" class="w-12 h-12 rounded object-cover">
                        <span class="font-bold"><?= htmlspecialchars($p['name']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($p['category']) ?> (<?= htmlspecialchars($p['pet_type']) ?>)</td>
                    <td>
                        <form method="POST" class="flex items-center gap-2">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <input type="number" step="0.01" name="price" value="<?= $p['price'] ?>" class="w-20 border rounded px-2 py-1">
                    </td>
                    <td>
                            <input type="number" name="stock" value="<?= $p['stock'] ?>" class="w-16 border rounded px-2 py-1 <?= $p['stock'] < 5 ? 'bg-red-50 border-red-400 font-bold text-red-600' : '' ?>">
                    </td>
                    <td class="flex items-center gap-2 py-3">
                            <button type="submit" name="update_stock" class="bg-blue-600 text-white px-3 py-1 rounded text-xs font-bold">Save</button>
                        </form>
                        <form method="POST" onsubmit="return confirm('Delete this product?');">
                            <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                            <button type="submit" name="delete_product" class="bg-red-100 text-red-600 px-3 py-1 rounded text-xs font-bold">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Orders Management -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border overflow-x-auto">
        <h2 class="text-xl font-extrabold mb-4">🛒 Customer Orders</h2>
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b text-xs uppercase text-gray-500">
                    <th class="py-3">Order #</th>
                    <th>Customer</th>
                    <th>Items</th>
                    <th>Total & Gateway</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody class="divide-y text-sm">
                <?php foreach ($orders as $o): $items = json_decode($o['items_json'], true); ?>
                <tr>
                    <td class="py-3 font-bold">#<?= $o['id'] ?></td>
                    <td>
                        <div class="font-bold"><?= htmlspecialchars($o['customer_name']) ?></div>
                        <div class="text-xs text-gray-500"><?= htmlspecialchars($o['customer_phone']) ?> • <?= htmlspecialchars($o['shipping_address']) ?></div>
                    </td>
                    <td class="py-2">
                        <?php foreach ($items as $it) echo "<div>{$it['qty']}x " . htmlspecialchars($it['name']) . "</div>"; ?>
                    </td>
                    <td>
                        <div class="font-bold"><?= $symbol ?><?= number_format($o['total_amount'], 2) ?></div>
                        <div class="text-xs text-indigo-600 font-semibold"><?= htmlspecialchars($o['payment_method']) ?> (<?= htmlspecialchars($o['payment_status']) ?>)</div>
                    </td>
                    <td>
                        <form method="POST" class="flex gap-2">
                            <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                            <select name="order_status" class="border rounded px-2 py-1 text-xs">
                                <?php foreach (['Pending', 'Processing', 'Shipped', 'Delivered', 'Cancelled'] as $st): ?>
                                    <option value="<?= $st ?>" <?= $o['order_status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" name="update_order_status" class="bg-gray-800 text-white px-2.5 py-1 rounded text-xs">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Payment Gateways & Store Settings -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border">
        <h2 class="text-xl font-extrabold mb-4">⚙️ Store, Stripe & PayPal Settings</h2>
        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <label class="font-bold block mb-1">Store Name</label>
                <input type="text" name="store_name" value="<?= htmlspecialchars($store_settings['store_name']) ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div>
                    <label class="font-bold block mb-1">Currency</label>
                    <input type="text" name="currency" value="<?= htmlspecialchars($store_settings['currency']) ?>" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="font-bold block mb-1">Symbol</label>
                    <input type="text" name="currency_symbol" value="<?= htmlspecialchars($store_settings['currency_symbol']) ?>" class="w-full border rounded-lg px-3 py-2">
                </div>
                <div>
                    <label class="font-bold block mb-1">Theme Color</label>
                    <input type="color" name="primary_color" value="<?= htmlspecialchars($store_settings['primary_color']) ?>" class="w-full h-10 border rounded-lg">
                </div>
            </div>
            <div>
                <label class="font-bold block mb-1">Stripe Public Key (pk_live_... or pk_test_...)</label>
                <input type="text" name="stripe_public_key" value="<?= htmlspecialchars($store_settings['stripe_public_key']) ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="font-bold block mb-1">Stripe Secret Key (sk_live_... or sk_test_...)</label>
                <input type="text" name="stripe_secret_key" value="<?= htmlspecialchars($store_settings['stripe_secret_key']) ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="font-bold block mb-1">PayPal Client ID</label>
                <input type="text" name="paypal_client_id" value="<?= htmlspecialchars($store_settings['paypal_client_id']) ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="font-bold block mb-1">Admin Password</label>
                <input type="text" name="admin_pass" value="<?= htmlspecialchars($store_settings['admin_pass']) ?>" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div class="md:col-span-2">
                <button type="submit" name="save_settings" class="bg-green-600 text-white font-bold px-6 py-2.5 rounded-lg hover:bg-green-700">Save All Settings</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>