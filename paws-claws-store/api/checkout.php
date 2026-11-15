<?php
header('Content-Type: application/json');
require_once '../config.php';

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if ($action === 'create_stripe_intent') {
    $amount = round(floatval($input['amount']) * 100); // cents
    $secret_key = $store_settings['stripe_secret_key'];
    
    if (strpos($secret_key, 'sk_') !== 0 || strpos($secret_key, 'your_stripe') !== false) {
        // Demo fallback if user hasn't entered real Stripe keys yet
        echo json_encode(['demo_mode' => true, 'clientSecret' => 'demo_secret_' . uniqid()]);
        exit;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/payment_intents');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'amount' => $amount,
        'currency' => strtolower($store_settings['currency']),
        'automatic_payment_methods[enabled]' => 'true'
    ]));
    curl_setopt($ch, CURLOPT_USERPWD, $secret_key . ':');
    $result = curl_exec($ch);
    curl_close($ch);
    echo $result;
    exit;
}

if ($action === 'save_order') {
    try {
        $pdo->beginTransaction();
        
        $items = $input['items'];
        // Verify & decrement stock
        foreach ($items as $item) {
            $stmt = $pdo->prepare("UPDATE products SET stock = GREATEST(0, stock - ?) WHERE id = ?");
            $stmt->execute([$item['qty'], $item['id']]);
        }

        $stmt = $pdo->prepare("INSERT INTO orders (customer_name, customer_email, customer_phone, shipping_address, order_notes, items_json, total_amount, payment_method, payment_status, transaction_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $input['name'],
            $input['email'],
            $input['phone'],
            $input['address'],
            $input['notes'] ?? '',
            json_encode($items),
            $input['total'],
            $input['payment_method'],
            $input['payment_status'] ?? 'Paid',
            $input['transaction_id'] ?? ('TXN-' . strtoupper(uniqid()))
        ]);
        
        $order_id = $pdo->lastInsertId();
        $pdo->commit();
        echo json_encode(['success' => true, 'order_id' => $order_id]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}
?>