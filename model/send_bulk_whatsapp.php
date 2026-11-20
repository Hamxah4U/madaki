<?php
header('Content-Type: application/json');
require_once 'Database.php';

if (!isset($_GET['transport_id']) || empty($_GET['transport_id'])) {
    echo json_encode(['status' => false, 'message' => 'Invalid transport ID.']);
    exit();
}

$transport_id = (int)$_GET['transport_id'];

// Fetch all customers linked to this transportation record
$stmt = $db->conn->prepare("SELECT te.*, c.fullname AS customer_name, c.phone AS customer_phone, c.phone 
                            FROM transportation_expenses te 
                            LEFT JOIN customers_tbl c ON c.id = te.customer_id 
                            WHERE te.transportation_id = ?");
$stmt->execute([$transport_id]);
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$records) {
    echo json_encode(['status' => false, 'message' => 'No customer records found for this transport.']);
    exit();
}

// Meta Cloud API Credentials (Replace with your actual Meta token, phone number ID, or config)
$accessToken = 'EAAQSPCcrrzoBS1lX6RASsXNeVuGtJ2IqV7lZBYJz9VZAxrqcUodBZCBvmqD4DqBOhEt8Gg1DYhhbt6COEewIZC86IwL00IboUhj3G7ezdRjZBh7ClJLzKYBy2wYYytfZCWC5CJC7jEL2YDCHfCXVjq4eoU75QHMgm5qUUQH82YpWlpF6WMNxVDgIKQRA8lK7F4yC7HsXEdLI2QWYe7qZBbOpFO6JGxhu1ZAf72f6EIrmFoDmI3j4mzSdFigZCZBDzakz49AHTdZBDa7QsWlZAh1FW912T1wZD';//'YOUR_META_ACCESS_TOKEN'; 
$phoneNumberId = '1459771103876106';//'YOUR_META_PHONE_NUMBER_ID';

$successCount = 0;

foreach ($records as $row) {
    $phone = $row['customer_phone'] ?? $row['phone'] ?? '';
    if (empty($phone)) continue;

    // Format phone number for international standards (e.g. Nigeria +234)
    $formattedPhone = trim($phone);
    if (strpos($formattedPhone, '0') === 0) {
      $formattedPhone = '+1' . substr($formattedPhone, 1); // Fixed '+' to '.'
    }

    $customerName = $row['customer_name'] ?? $row['fullname'] ?? 'Valued Customer';
    $receiptLink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]/receipt?id={$row['id']}&tid={$transport_id}";

    $message = "Hello *{$customerName}*,\n\nYour transportation and payment receipt from *Bashir Madaki Transport* is ready.\n\n📄 View or download your receipt here:\n{$receiptLink}\n\nThank you for your patronage!";

    // Payload for Meta Cloud API
    $data = [
        'messaging_product' => 'whatsapp',
        'to' => $formattedPhone,
        'type' => 'text',
        'text' => [
            'body' => $message
        ]
    ];

    $ch = curl_init("https://graph.facebook.com/v20.0/{$phoneNumberId}/messages");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer {$accessToken}",
        "Content-Type: application/json"
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        $successCount++;
    }
}

echo json_encode([
    'status' => true, 
    'message' => "Successfully sent {$successCount} out of " . count($records) . " receipts via WhatsApp API!"
]);