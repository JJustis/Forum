<?php
include 'config.php';

// Handle PayPal IPN
$raw_post_data = file_get_contents('php://input');
$raw_post_array = explode('&', $raw_post_data);

$myPost = array();
foreach ($raw_post_array as $keyval) {
    $keyval = explode('=', $keyval);
    if (count($keyval) == 2) {
        $myPost[$keyval[0]] = urldecode($keyval[1]);
    }
}

// Validate the IPN
if (array_key_exists('payment_status', $myPost) && $myPost['payment_status'] == 'Completed') {
    // Process the payment
    $sql = "UPDATE orders SET status = 'Completed' WHERE transaction_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $myPost['txn_id']);
    $stmt->execute();
    $stmt->close();
}

http_response_code(200);