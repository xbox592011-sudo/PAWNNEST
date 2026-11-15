<?php
require_once 'config.php';
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
$primary = htmlspecialchars($store_settings['primary_color']);
$symbol = htmlspecialchars($store_settings['currency_symbol']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($store_settings['store_name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Stripe JS -->
    <script src="https://js.stripe.com/v3/"></script>
    <!-- PayPal JS SDK -->
    <script src="https://www.paypal.com/sdk/js?client-id=<?= htmlspecialchars($store_settings['paypal_client_id']) ?>&currency=<?= htmlspecialchars($store_settings['currency']) ?>"></script>
    <style>
        :root { --brand: <?= $primary ?>; }
        .bg-brand { background-color: var(--brand); }
        .text-brand { color: var(--brand); }
        .border-brand { border-color: var(--brand); }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

<!-- Navbar -->
<nav class="bg-white shadow-sm sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between gap-4">
        <a href="index.php" class="flex items-center gap-2 text-2xl font-extrabold text-brand">
            <i data-lucide="paw-print" class="w-8 h-8"></i>
            <span><?= htmlspecialchars($store_settings['store_name']) ?></span>
        </a>
        
        <div class="hidden md:flex flex-1 max-w-md mx-4">
            <div class="relative w-full">
                <input type="text" id="searchInput" onkeyup="filterProducts()" placeholder="Search dog food, cat toys, beds..." class="w-full pl-10 pr-4 py-2 border rounded-full focus:outline-none focus:border-brand">
                <i data-lucide="search" class="w-5 h-5 text-gray-400 absolute left-3 top-2.5"></i>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <a href="admin/index.php" class="text-sm font-medium text-gray-600 hover:text-brand flex items-center gap-1">
                <i data-lucide="shield-check" class="w-4 h-4"></i> Admin
            </a>
            <button onclick="toggleCart()" class="relative bg-brand text-white px-4 py-2 rounded-full font-semibold flex items-center gap-2 shadow hover:opacity-90 transition">
                <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                <span id="cartCount">0</span>
            </button>
        </div>
    </div>
</nav>

<!-- Hero Banner -->
<section class="bg-gradient-to-r from-amber-50 to-orange-100 py-14 px-4 border-b">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between gap-8">
        <div class="max-w-xl">
            <span class="bg-white text-brand text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider shadow-sm">Free Shipping on Orders $50+</span>
            <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mt-4 leading-tight">Everything Your Best Friend Needs to Thrive</h1>
            <p class="text-gray-600 mt-4 text-lg">Nutritious food, interactive toys, cozy beds, and vet-approved healthcare delivered straight to your door.</p>
            <a href="#catalog" class="inline-block mt-6 bg-brand text-white font-bold px-8 py-3.5 rounded-full shadow-lg hover:opacity-90 transition">Shop Collection</a>
        </div>
        <img src="https://images.unsplash.com/photo-1450778869180-41d0601e046e?auto=format&fit=crop&w=650&q=80" alt="Happy Dog and Cat" class="rounded-2xl shadow-xl w-full md:w-1/2 object-cover h-80">
    </div>
</section>

<!-- Category Quick Pills & Filters -->
<section id="catalog" class="max-w-7xl mx-auto px-4 py-10">
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div class="flex flex-wrap gap-2" id="categoryButtons">
            <button onclick="setCategory('All')" class="cat-btn active bg-brand text-white px-4 py-2 rounded-full text-sm font-semibold">All Supplies</button>
            <button onclick="setCategory('Dog Food')" class="cat-btn bg-white border px-4 py-2 rounded-full text-sm font-semibold hover:bg-gray-100">Dog Food</button>
            <button onclick="setCategory('Cat Toys')" class="cat-btn bg-white border px-4 py-2 rounded-full text-sm font-semibold hover:bg-gray-100">Cat Toys</button>
            <button onclick="setCategory('Grooming')" class="cat-btn bg-white border px-4 py-2 rounded-full text-sm font-semibold hover:bg-gray-100">Grooming</button>
            <button onclick="setCategory('Healthcare')" class="cat-btn bg-white border px-4 py-2 rounded-full text-sm font-semibold hover:bg-gray-100">Healthcare</button>
            <button onclick="setCategory('Accessories')" class="cat-btn bg-white border px-4 py-2 rounded-full text-sm font-semibold hover:bg-gray-100">Accessories</button>
        </div>

        <div class="flex items-center gap-3">
            <select id="petFilter" onchange="filterProducts()" class="bg-white border rounded-lg px-3 py-2 text-sm font-medium">
                <option value="All">All Pets</option>
                <option value="Dog">Dogs</option>
                <option value="Cat">Cats</option>
                <option value="Bird">Birds</option>
                <option value="Small Pet">Small Pets</option>
            </select>
        </div>
    </div>

    <!-- Product Grid -->
    <div id="productGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($products as $p): ?>
        <div class="product-card bg-white rounded-2xl shadow-sm border overflow-hidden flex flex-col justify-between hover:shadow-md transition"
             data-name="<?= strtolower(htmlspecialchars($p['name'])) ?>"
             data-category="<?= htmlspecialchars($p['category']) ?>"
             data-pet="<?= htmlspecialchars($p['pet_type']) ?>">
            <div>
                <div class="relative h-56 bg-gray-100">
                    <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="w-full h-full object-cover">
                    <span class="absolute top-3 left-3 bg-white/90 backdrop-blur px-2.5 py-1 rounded-full text-xs font-bold text-gray-700"><?= htmlspecialchars($p['pet_type']) ?> • <?= htmlspecialchars($p['category']) ?></span>
                    <?php if ($p['stock'] <= 0): ?>
                        <span class="absolute top-3 right-3 bg-red-600 text-white px-2.5 py-1 rounded-full text-xs font-bold">Out of Stock</span>
                    <?php elseif ($p['stock'] < 5): ?>
                        <span class="absolute top-3 right-3 bg-amber-500 text-white px-2.5 py-1 rounded-full text-xs font-bold">Only <?= $p['stock'] ?> left!</span>
                    <?php endif; ?>
                </div>
                <div class="p-5">
                    <h3 class="font-bold text-lg text-gray-900"><?= htmlspecialchars($p['name']) ?></h3>
                    <p class="text-gray-500 text-sm mt-1 line-clamp-2"><?= htmlspecialchars($p['description']) ?></p>
                </div>
            </div>
            <div class="px-5 pb-5 pt-2 flex items-center justify-between border-t mt-2">
                <span class="text-xl font-extrabold text-gray-900"><?= $symbol ?><?= number_format($p['price'], 2) ?></span>
                <button onclick='addToCart(<?= json_encode(["id" => $p["id"], "name" => $p["name"], "price" => floatval($p["price"]), "image" => $p["image_url"], "maxStock" => intval($p["stock"])]) ?>)'
                        <?= $p['stock'] <= 0 ? 'disabled' : '' ?>
                        class="bg-brand text-white px-4 py-2 rounded-xl font-semibold text-sm flex items-center gap-1.5 hover:opacity-90 disabled:opacity-40 transition">
                    <i data-lucide="plus" class="w-4 h-4"></i> Add to Cart
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Shopping Cart & Checkout Drawer -->
<div id="cartDrawer" class="fixed inset-0 bg-black/50 z-50 hidden justify-end">
    <div class="bg-white w-full max-w-md h-full flex flex-col justify-between p-6 overflow-y-auto shadow-2xl">
        <div>
            <div class="flex items-center justify-between border-b pb-4">
                <h2 class="text-xl font-extrabold flex items-center gap-2"><i data-lucide="shopping-bag" class="w-5 h-5 text-brand"></i> Your Cart</h2>
                <button onclick="toggleCart()" class="p-1 text-gray-400 hover:text-gray-700"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>
            <div id="cartItems" class="divide-y my-4"></div>
        </div>

        <div id="checkoutSection" class="border-t pt-4 hidden">
            <div class="flex justify-between text-lg font-extrabold mb-4">
                <span>Total:</span>
                <span id="cartTotal"><?= $symbol ?>0.00</span>
            </div>

            <div class="space-y-3 mb-4">
                <input type="text" id="custName" placeholder="Full Name *" class="w-full border rounded-lg px-3 py-2 text-sm" required>
                <input type="email" id="custEmail" placeholder="Email Address *" class="w-full border rounded-lg px-3 py-2 text-sm" required>
                <div class="flex gap-2">
                    <select id="phoneCode" class="border rounded-lg px-2 py-2 text-sm bg-gray-50">
                        <option value="+1">🇺🇸 +1</option>
                        <option value="+20">🇪🇬 +20</option>
                        <option value="+44">🇬🇧 +44</option>
                        <option value="+971">🇦🇪 +971</option>
                        <option value="+966">🇸🇦 +966</option>
                    </select>
                    <input type="tel" id="custPhone" placeholder="Phone Number *" class="w-full border rounded-lg px-3 py-2 text-sm" required>
                </div>
                <textarea id="custAddress" rows="2" placeholder="Full Shipping Address *" class="w-full border rounded-lg px-3 py-2 text-sm" required></textarea>
                <input type="text" id="custNotes" placeholder="Order Notes (Optional)" class="w-full border rounded-lg px-3 py-2 text-sm">
            </div>

            <!-- Payment Methods -->
            <div class="space-y-3">
                <p class="text-xs font-bold uppercase text-gray-500">Pay Securely With</p>
                
                <!-- Stripe Card Box -->
                <div class="border rounded-xl p-3 bg-gray-50">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-bold flex items-center gap-1"><i data-lucide="credit-card" class="w-4 h-4 text-indigo-600"></i> Credit / Debit Card (Stripe)</span>
                    </div>
                    <div id="stripe-card-element" class="bg-white p-2.5 rounded border mb-2"></div>
                    <button onclick="payWithStripe()" id="stripeBtn" class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-bold text-sm hover:bg-indigo-700 transition">
                        Pay with Card
                    </button>
                </div>

                <!-- PayPal Smart Buttons -->
                <div id="paypal-button-container" class="mt-2"></div>
            </div>
        </div>
    </div>
</div>

<script>
lucide.createIcons();
const SYMBOL = "<?= $symbol ?>";
const STRIPE_PK = "<?= htmlspecialchars($store_settings['stripe_public_key']) ?>";
let cart = JSON.parse(localStorage.getItem('paws_cart') || '[]');
let selectedCategory = 'All';

// Initialize Stripe
let stripe = null, cardElement = null;
if (STRIPE_PK && STRIPE_PK.startsWith('pk_')) {
    stripe = Stripe(STRIPE_PK);
    const elements = stripe.elements();
    cardElement = elements.create('card');
    cardElement.mount('#stripe-card-element');
} else {
    document.getElementById('stripe-card-element').innerHTML = '<p class="text-xs text-amber-600">Test Mode: Add your Stripe Public Key in Admin Settings for live card fields.</p>';
}

function setCategory(cat) {
    selectedCategory = cat;
    document.querySelectorAll('.cat-btn').forEach(btn => {
        btn.classList.remove('bg-brand', 'text-white');
        btn.classList.add('bg-white', 'border');
        if (btn.innerText.includes(cat) || (cat === 'All' && btn.innerText.includes('All'))) {
            btn.classList.add('bg-brand', 'text-white');
            btn.classList.remove('bg-white', 'border');
        }
    });
    filterProducts();
}

function filterProducts() {
    const query = document.getElementById('searchInput').value.toLowerCase();
    const pet = document.getElementById('petFilter').value;
    document.querySelectorAll('.product-card').forEach(card => {
        const matchName = card.dataset.name.includes(query);
        const matchCat = selectedCategory === 'All' || card.dataset.category === selectedCategory;
        const matchPet = pet === 'All' || card.dataset.pet === pet;
        card.style.display = (matchName && matchCat && matchPet) ? 'flex' : 'none';
    });
}

function addToCart(product) {
    const existing = cart.find(i => i.id === product.id);
    if (existing) {
        if (existing.qty < product.maxStock) existing.qty++;
        else return alert('Max available stock reached!');
    } else {
        cart.push({ ...product, qty: 1 });
    }
    saveCart();
    toggleCart(true);
}

function updateQty(id, delta) {
    const idx = cart.findIndex(i => i.id === id);
    if (idx > -1) {
        cart[idx].qty += delta;
        if (cart[idx].qty <= 0) cart.splice(idx, 1);
    }
    saveCart();
}

function saveCart() {
    localStorage.setItem('paws_cart', JSON.stringify(cart));
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cartItems');
    const checkoutSec = document.getElementById('checkoutSection');
    document.getElementById('cartCount').innerText = cart.reduce((sum, i) => sum + i.qty, 0);
    
    if (cart.length === 0) {
        container.innerHTML = '<p class="text-center text-gray-400 py-10">Your cart is empty.</p>';
        checkoutSec.classList.add('hidden');
        return;
    }
    checkoutSec.classList.remove('hidden');
    let total = 0;
    container.innerHTML = cart.map(item => {
        total += item.price * item.qty;
        return `<div class="py-3 flex items-center justify-between gap-3">
            <img src="${item.image}" class="w-14 h-14 rounded-lg object-cover">
            <div class="flex-1">
                <h4 class="font-bold text-sm">${item.name}</h4>
                <p class="text-xs text-gray-500">${SYMBOL}${item.price.toFixed(2)} x ${item.qty}</p>
            </div>
            <div class="flex items-center gap-1">
                <button onclick="updateQty(${item.id}, -1)" class="w-7 h-7 bg-gray-100 rounded font-bold">-</button>
                <span class="px-2 text-sm font-bold">${item.qty}</span>
                <button onclick="updateQty(${item.id}, 1)" class="w-7 h-7 bg-gray-100 rounded font-bold">+</button>
            </div>
        </div>`;
    }).join('');
    document.getElementById('cartTotal').innerText = SYMBOL + total.toFixed(2);
}

function toggleCart(forceOpen = null) {
    const drawer = document.getElementById('cartDrawer');
    if (forceOpen === true) drawer.classList.remove('hidden'), drawer.classList.add('flex');
    else if (forceOpen === false) drawer.classList.add('hidden'), drawer.classList.remove('flex');
    else drawer.classList.toggle('hidden'), drawer.classList.toggle('flex');
}

function getCustomerData() {
    const name = document.getElementById('custName').value.trim();
    const email = document.getElementById('custEmail').value.trim();
    const phone = document.getElementById('phoneCode').value + ' ' + document.getElementById('custPhone').value.trim();
    const address = document.getElementById('custAddress').value.trim();
    const notes = document.getElementById('custNotes').value.trim();
    if (!name || !email || !document.getElementById('custPhone').value.trim() || !address) {
        alert('Please fill in your Name, Email, Phone, and Shipping Address first.');
        return null;
    }
    const total = cart.reduce((sum, i) => sum + (i.price * i.qty), 0);
    return { name, email, phone, address, notes, total, items: cart };
}

async function finalizeOrder(customer, method, txnId) {
    const res = await fetch('api/checkout.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'save_order',
            ...customer,
            payment_method: method,
            payment_status: 'Paid',
            transaction_id: txnId
        })
    });
    const data = await res.json();
    if (data.success) {
        alert(`🎉 Order #${data.order_id} Confirmed! Thank you for shopping at Paws & Claws Emporium.`);
        cart = [];
        saveCart();
        location.reload();
    } else {
        alert('Error saving order: ' + data.error);
    }
}

async function payWithStripe() {
    const customer = getCustomerData();
    if (!customer) return;
    const btn = document.getElementById('stripeBtn');
    btn.disabled = true; btn.innerText = 'Processing...';

    const res = await fetch('api/checkout.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'create_stripe_intent', amount: customer.total })
    });
    const intent = await res.json();

    if (intent.demo_mode || !stripe) {
        await finalizeOrder(customer, 'Stripe (Test Mode)', intent.clientSecret || 'STRIPE_DEMO');
        return;
    }

    const result = await stripe.confirmCardPayment(intent.clientSecret, {
        payment_method: { card: cardElement, billing_details: { name: customer.name, email: customer.email } }
    });

    if (result.error) {
        alert(result.error.message);
        btn.disabled = false; btn.innerText = 'Pay with Card';
    } else if (result.paymentIntent.status === 'succeeded') {
        await finalizeOrder(customer, 'Stripe', result.paymentIntent.id);
    }
}

// Render PayPal Buttons
if (window.paypal) {
    paypal.Buttons({
        onClick: function(data, actions) {
            if (!getCustomerData()) return actions.reject();
            return actions.resolve();
        },
        createOrder: function(data, actions) {
            const customer = getCustomerData();
            return actions.order.create({
                purchase_units: [{ amount: { value: customer.total.toFixed(2) } }]
            });
        },
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                const customer = getCustomerData();
                finalizeOrder(customer, 'PayPal', details.id);
            });
        }
    }).render('#paypal-button-container');
}

renderCart();
</script>
</body>
</html>