<?php
// This file processes the payment form
// It handles different payment methods and updates the booking status

// Check if user is logged in
session_start();
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: login.php'); // Send to login page if not logged in
    exit;
}
require('includes/db_config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Get form data
        $booking_id = isset($_POST['booking_id']) ? $_POST['booking_id'] : null;
        $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'credit_card';

        // Validate booking ID
        if (empty($booking_id) || !is_numeric($booking_id)) {
            throw new Exception("Invalid booking ID.");
        }

        // Get booking details to calculate the correct amount
        $booking_query = "SELECT b.*, r.price as room_price, r.name as room_name, r.image as room_image
                         FROM bookings b
                         JOIN rooms r ON b.room_ID = r.room_ID
                         WHERE b.booking_ID = ?";
        $booking_stmt = $conn->prepare($booking_query);

        if (!$booking_stmt) {
            throw new Exception("Error preparing booking query: " . $conn->error);
        }

        $booking_stmt->bind_param("i", $booking_id);
        $booking_stmt->execute();
        $booking_result = $booking_stmt->get_result();

        if ($booking_result->num_rows == 0) {
            throw new Exception("Booking not found.");
        }

        $booking = $booking_result->fetch_assoc();

        // Validate and format check-in and check-out dates
        if (empty($booking['check_in']) || strtotime($booking['check_in']) === false) {
            $booking['check_in'] = date('Y-m-d'); // Set to today if invalid

            // Update the database with the corrected check-in date
            $update_checkin = "UPDATE bookings SET check_in = ? WHERE booking_ID = ?";
            $update_stmt = $conn->prepare($update_checkin);
            $update_stmt->bind_param("si", $booking['check_in'], $booking_id);
            $update_stmt->execute();
        }

        if (empty($booking['check_out']) || strtotime($booking['check_out']) === false) {
            // Set check-out to check-in + 1 day if invalid
            $check_in_date = new DateTime($booking['check_in']);
            $check_in_date->modify('+1 day');
            $booking['check_out'] = $check_in_date->format('Y-m-d');

            // Update the database with the corrected check-out date
            $update_checkout = "UPDATE bookings SET check_out = ? WHERE booking_ID = ?";
            $update_stmt = $conn->prepare($update_checkout);
            $update_stmt->bind_param("si", $booking['check_out'], $booking_id);
            $update_stmt->execute();
        }

        // Create DateTime objects for calculations
        $check_in = new DateTime($booking['check_in']);
        $check_out = new DateTime($booking['check_out']);

        // Ensure check-out is after check-in
        if ($check_out <= $check_in) {
            $check_out = clone $check_in;
            $check_out->modify('+1 day');
            $booking['check_out'] = $check_out->format('Y-m-d');

            // Update the database with the corrected check-out date
            $update_checkout = "UPDATE bookings SET check_out = ? WHERE booking_ID = ?";
            $update_stmt = $conn->prepare($update_checkout);
            $update_stmt->bind_param("si", $booking['check_out'], $booking_id);
            $update_stmt->execute();
        }

        // Calculate the exact difference in days
        $interval = $check_in->diff($check_out);
        $num_nights = $interval->days;

        // Validate number of nights (1-30 nights is reasonable)
        if ($num_nights < 1) {
            $num_nights = 1;
        } elseif ($num_nights > 30) {
            $num_nights = 30; // Cap at 30 nights for reasonability
        }

        // Calculate amount with validation
        if (!isset($booking['room_price']) || !is_numeric($booking['room_price']) || $booking['room_price'] <= 0) {
            $booking['room_price'] = 1000; // Default price if invalid
        }

        $amount = $num_nights * $booking['room_price'];

        // Ensure amount is positive and reasonable
        if ($amount <= 0) {
            $amount = $booking['room_price']; // Default to at least one night's cost
        }

        // Store the number of nights for the receipt
        $nights = $num_nights;

        // Create price calculation string for display only (not stored in database)
        $price_calculation = "₱" . number_format($booking['room_price'], 2) . " × " . $nights . " night" . ($nights > 1 ? "s" : "");

        // Initialize variables
        $transaction_id = 'TXN' . time() . rand(1000, 9999);
        $payment_date = date('Y-m-d H:i:s');
        $payment_status = 'Completed';
        $masked_card_number = '';
        $cardholder_name = '';
        $account_name = '';
        $expiry_date = '';
        $payment_method_display = '';

        // Process based on payment method
        if ($payment_method === 'credit_card') {
            // Get credit card specific data
            $card_number = isset($_POST['card_number']) ? $_POST['card_number'] : '';
            $expiry_date = isset($_POST['expiry_date']) ? $_POST['expiry_date'] : '';
            $cvv = isset($_POST['cvv']) ? $_POST['cvv'] : '';
            $cardholder_name = isset($_POST['cardholder_name']) ? $_POST['cardholder_name'] : '';
            $card_account_name = isset($_POST['card_account_name']) ? $_POST['card_account_name'] : '';
            $billing_address = isset($_POST['billing_address']) ? $_POST['billing_address'] : '';

            // Validate credit card details
            if (empty($card_number) || empty($expiry_date) || empty($cvv) || empty($cardholder_name) || empty($card_account_name)) {
                throw new Exception("Please fill out all credit card details.");
            }

            // Basic card number validation (remove spaces and check length)
            $card_number = str_replace(' ', '', $card_number);
            if (strlen($card_number) < 13 || strlen($card_number) > 16 || !ctype_digit($card_number)) {
                throw new Exception("Invalid card number format.");
            }

            // Basic CVV validation
            if (strlen($cvv) < 3 || strlen($cvv) > 4 || !ctype_digit($cvv)) {
                throw new Exception("Invalid CVV format.");
            }

            // Check if expiry date is in the future
            $current_date = new DateTime();
            $expiry = new DateTime($expiry_date . '-01');
            if ($expiry <= $current_date) {
                throw new Exception("Card has expired. Please use a valid card.");
            }

            // Mask the card number for storage
            $masked_card_number = substr_replace($card_number, str_repeat('*', strlen($card_number) - 4), 0, strlen($card_number) - 4);
            $account_name = $card_account_name; // Set account name for credit card
            $payment_method_display = 'Credit/Debit Card';

        } elseif ($payment_method === 'gcash') {
            // Get GCash specific data
            $gcash_number = isset($_POST['gcash_number']) ? $_POST['gcash_number'] : '';
            $gcash_name = isset($_POST['gcash_name']) ? $_POST['gcash_name'] : '';

            // Validate GCash details
            if (empty($gcash_number) || empty($gcash_name)) {
                throw new Exception("Please fill out all GCash details.");
            }

            // Basic phone number validation - remove all non-digit characters
            $gcash_number = preg_replace('/\D/', '', $gcash_number);
            if (strlen($gcash_number) < 10 || !ctype_digit($gcash_number)) {
                throw new Exception("Invalid GCash mobile number format.");
            }

            // Set payment details
            $masked_card_number = '+63' . substr_replace($gcash_number, '****', 3, 4);
            $account_name = $gcash_name;
            $cardholder_name = ''; // Clear cardholder_name for GCash payments
            $payment_method_display = 'GCash';

        } elseif ($payment_method === 'paymaya') {
            // Get PayMaya specific data
            $paymaya_number = isset($_POST['paymaya_number']) ? $_POST['paymaya_number'] : '';
            $paymaya_name = isset($_POST['paymaya_name']) ? $_POST['paymaya_name'] : '';

            // Validate PayMaya details
            if (empty($paymaya_number) || empty($paymaya_name)) {
                throw new Exception("Please fill out all PayMaya details.");
            }

            // Basic phone number validation - remove all non-digit characters
            $paymaya_number = preg_replace('/\D/', '', $paymaya_number);
            if (strlen($paymaya_number) < 10 || !ctype_digit($paymaya_number)) {
                throw new Exception("Invalid PayMaya mobile number format.");
            }

            // Set payment details
            $masked_card_number = '+63' . substr_replace($paymaya_number, '****', 3, 4);
            $account_name = $paymaya_name;
            $cardholder_name = ''; // Clear cardholder_name for PayMaya payments
            $payment_method_display = 'PayMaya';

        } else {
            throw new Exception("Invalid payment method selected.");
        }

        // In a real application, you would integrate with a payment gateway here
        // For this demo, we'll simulate a successful payment

        // Start a transaction
        $conn->begin_transaction();

        try {
            // Create price calculation string (for display only, not stored in database)
            $price_calculation = "₱" . number_format($booking['room_price'], 2) . " × " . $num_nights . " night" . ($num_nights > 1 ? "s" : "");

            // Update booking status to "Confirmed" and ensure nights and total_price are set
            $update_query = "UPDATE bookings SET status = 'Confirmed', nights = ?, total_price = ? WHERE booking_ID = ?";
            $update_stmt = $conn->prepare($update_query);

            if (!$update_stmt) {
                throw new Exception("Database query preparation failed: " . $conn->error);
            }

            $update_stmt->bind_param("idi", $num_nights, $amount, $booking_id);

            if (!$update_stmt->execute()) {
                throw new Exception("Error updating booking status: " . $update_stmt->error);
            }

            // Get user ID from the booking
            $user_query = "SELECT u.user_ID FROM users u
                          JOIN bookings b ON u.email = b.user_email
                          WHERE b.booking_ID = ?";
            $user_stmt = $conn->prepare($user_query);

            if (!$user_stmt) {
                throw new Exception("Database query preparation failed: " . $conn->error);
            }

            $user_stmt->bind_param("i", $booking_id);

            if (!$user_stmt->execute()) {
                throw new Exception("Error fetching user information: " . $user_stmt->error);
            }

            $user_result = $user_stmt->get_result();
            $user_data = $user_result->fetch_assoc();
            $user_id = $user_data['user_ID'] ?? null;

            if (!$user_id) {
                throw new Exception("User information not found for this booking.");
            }

            // Save payment information to the database
            $payment_query = "INSERT INTO payments (booking_ID, user_ID, amount, payment_method, transaction_id, card_number, cardholder_name, account_name, expiry_date, payment_date, status)
                             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $payment_stmt = $conn->prepare($payment_query);

            if (!$payment_stmt) {
                throw new Exception("Database query preparation failed: " . $conn->error);
            }

            $payment_stmt->bind_param("iidssssssss", $booking_id, $user_id, $amount, $payment_method_display, $transaction_id, $masked_card_number, $cardholder_name, $account_name, $expiry_date, $payment_date, $payment_status);

            if (!$payment_stmt->execute()) {
                throw new Exception("Error saving payment information: " . $payment_stmt->error);
            }

            // Commit the transaction
            $conn->commit();

            // Store payment information in session for the success page
            $_SESSION['payment_info'] = [
                'booking_id' => $booking_id,
                'amount' => $amount,
                'card_number' => $masked_card_number,
                'cardholder_name' => $cardholder_name,
                'account_name' => $account_name,
                'payment_date' => $payment_date,
                'transaction_id' => $transaction_id,
                'payment_method' => $payment_method_display,
                'room_price' => $booking['room_price'],
                'nights' => $num_nights,
                'price_calculation' => $price_calculation,
                'room_name' => $booking['room_name'],
                'room_image' => $booking['room_image'],
                'check_in' => $booking['check_in'],
                'check_out' => $booking['check_out']
            ];

            // Redirect to payment success page
            header("Location: payment_success.php?booking_id=$booking_id");
            exit;
        } catch (Exception $e) {
            // Rollback the transaction if an error occurs
            $conn->rollback();
            throw $e;
        }
    } catch (Exception $e) {
        // Store the error message and payment method for debugging
        $_SESSION['payment_error'] = $e->getMessage();
        $_SESSION['payment_debug'] = [
            'method' => $payment_method,
            'error' => $e->getMessage(),
            'time' => date('Y-m-d H:i:s')
        ];

        header("Location: payment.php?booking_id=$booking_id");
        exit;
    }
} else {
    // If not a POST request, redirect to home
    header('Location: index.php');
    exit;
}
?>