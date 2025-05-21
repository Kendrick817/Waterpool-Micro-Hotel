<?php
// This file creates a PDF receipt for a booking
// It uses the mPDF library to generate the PDF

// Start session and include required files
session_start();
require('includes/db_config.php');
require __DIR__ . '/vendor/autoload.php';

// Make sure we have a booking ID
if (!isset($_GET['booking_id']) || empty($_GET['booking_id'])) {
    die("Error: No booking ID provided.");
}

// Get the booking ID from the URL
$booking_id = $_GET['booking_id'];
$booking = null;
$payment_info = null;

try {
    // Get booking information
    $booking_query = "SELECT b.*, r.name as room_name, r.price as room_price, b.user_email
                     FROM bookings b
                     JOIN rooms r ON b.room_ID = r.room_ID
                     WHERE b.booking_ID = ?";

    $stmt = $conn->prepare($booking_query);

    if (!$stmt) {
        throw new Exception("Error preparing booking query: " . $conn->error);
    }

    $stmt->bind_param("s", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Booking not found.");
    }

    $booking = $result->fetch_assoc();

    // Get payment information if available
    $payment_query = "SELECT * FROM payments WHERE booking_id = ? ORDER BY payment_date DESC LIMIT 1";
    $payment_stmt = $conn->prepare($payment_query);

    if ($payment_stmt) {
        $payment_stmt->bind_param("s", $booking_id);
        $payment_stmt->execute();
        $payment_result = $payment_stmt->get_result();

        if ($payment_result->num_rows > 0) {
            $payment_info = $payment_result->fetch_assoc();
        }
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Create new mPDF instance with smaller margins to fit on one page
$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 10,
    'margin_bottom' => 10,
    'margin_header' => 0,
    'margin_footer' => 0
]);

// Set document information
$mpdf->SetTitle('Booking Receipt #' . $booking['booking_ID']);
$mpdf->SetAuthor('Hotel Booking System');
$mpdf->SetCreator('Hotel Booking System');

// Ensure everything fits on one page
$mpdf->shrink_tables_to_fit = 1;
$mpdf->use_kwt = true; // Keep with table to avoid splitting tables across pages

// Get the logo path and convert to data URI
$logo_path = __DIR__ . '/images/logo/logo.png';
$logo_data = base64_encode(file_get_contents($logo_path));
$logo_data_uri = 'data:image/png;base64,' . $logo_data;

// Calculate dates and nights
$check_in_date = isset($payment_info['check_in']) && !empty($payment_info['check_in'])
    ? $payment_info['check_in'] : $booking['check_in'];
$check_out_date = isset($payment_info['check_out']) && !empty($payment_info['check_out'])
    ? $payment_info['check_out'] : $booking['check_out'];
$created_timestamp = isset($booking['created_at']) ? strtotime($booking['created_at']) : time();
$receipt_number = date('Ymd', $created_timestamp) . '-' . $booking['booking_ID'];

// Increase PCRE backtrack limit
ini_set('pcre.backtrack_limit', '5000000');

// Write HTML to the PDF in smaller chunks
// 1. CSS Styles
$css = '
<style>
    body {
        font-family: Arial, sans-serif;
        font-size: 10pt;
        line-height: 1.2;
        color: #333;
    }
    .receipt-header {
        text-align: center;
        margin-bottom: 10px;
        padding-bottom: 5px;
        border-bottom: 1px solid #333;
    }
    .logo {
        width: 60px;
        height: 60px;
        margin: 0 auto;
        border-radius: 50%;
        display: block;
    }
    .receipt-title {
        font-size: 16pt;
        font-weight: bold;
        margin: 5px 0;
    }
    .receipt-subtitle {
        font-size: 9pt;
        color: #555;
        margin: 2px 0;
    }
    .receipt-section {
        margin-bottom: 10px;
    }
    .receipt-section-title {
        font-size: 12pt;
        font-weight: bold;
        border-bottom: 1px solid #ddd;
        padding-bottom: 3px;
        margin-bottom: 5px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 8px;
    }
    table td {
        padding: 3px;
        vertical-align: top;
    }
    table td:first-child {
        width: 40%;
        font-weight: 500;
        color: #555;
    }
    .total-row {
        font-weight: bold;
        border-top: 1px solid #333;
        padding-top: 3px;
    }
    .footer {
        text-align: center;
        font-size: 8pt;
        color: #777;
        margin-top: 10px;
        padding-top: 5px;
        border-top: 1px solid #ddd;
    }
    /* Two-column layout for sections */
    .columns {
        display: table;
        width: 100%;
    }
    .column {
        display: table-cell;
        width: 48%;
        padding-right: 2%;
    }
</style>';

// 2. Header Section
$header = '
<div class="receipt-header">
    <table style="width:100%">
        <tr>
            <td style="width:20%; text-align:center; vertical-align:middle;">
                <img src="' . $logo_data_uri . '" class="logo" alt="Hotel Logo">
            </td>
            <td style="width:80%; text-align:center; vertical-align:middle;">
                <h1 class="receipt-title">Booking Receipt</h1>
                <p class="receipt-subtitle">Sayre Hwy, Malaybalay, Bukidnon | Tel: +1-234-567-8900</p>
                <p class="receipt-subtitle">Receipt #: ' . $receipt_number . '</p>
            </td>
        </tr>
    </table>
</div>';

// 3. Booking Information Section
$booking_info = '
<div class="receipt-section">
    <h3 class="receipt-section-title">Booking Information</h3>
    <table>
        <tr>
            <td>Booking ID:</td>
            <td>' . htmlspecialchars($booking['booking_ID']) . '</td>
        </tr>
        <tr>
            <td>Booking Date:</td>
            <td>' . date('F j, Y', $created_timestamp) . '</td>
        </tr>
        <tr>
            <td>Guest Email:</td>
            <td>' . htmlspecialchars($booking['user_email']) . '</td>
        </tr>
        <tr>
            <td>Guests:</td>
            <td>' . htmlspecialchars($booking['adults']) . ' Adults, ' . htmlspecialchars($booking['children']) . ' Children</td>
        </tr>
    </table>
</div>';

// 4. Room Details Section
$room_details = '
<div class="receipt-section">
    <h3 class="receipt-section-title">Room Details</h3>
    <table>
        <tr>
            <td>Room:</td>
            <td>' . htmlspecialchars($booking['room_name']) . '</td>
        </tr>
        <tr>
            <td>Check-in:</td>
            <td>' . date('F j, Y', strtotime($check_in_date)) . '</td>
        </tr>
        <tr>
            <td>Check-out:</td>
            <td>' . date('F j, Y', strtotime($check_out_date)) . '</td>
        </tr>
        <tr>
            <td>Nights:</td>
            <td>' . $booking['nights'] . '</td>
        </tr>
    </table>
</div>';

// 5. Payment Details Section
$payment_details = '
<div class="receipt-section">
    <h3 class="receipt-section-title">Payment Details</h3>
    <table>';

if ($payment_info) {
    $payment_details .= '
        <tr>
            <td>Payment Method:</td>
            <td>' . htmlspecialchars($payment_info['payment_method']) . '</td>
        </tr>
        <tr>
            <td>Transaction ID:</td>
            <td>' . htmlspecialchars($payment_info['transaction_id']) . '</td>
        </tr>
        <tr>
            <td>Payment Date:</td>
            <td>' . date('F j, Y', strtotime($payment_info['payment_date'])) . '</td>
        </tr>';

    // Add payment method specific details
    if (strpos($payment_info['payment_method'], 'Card') !== false) {
        $payment_details .= '
        <tr>
            <td>Card Number:</td>
            <td>' . htmlspecialchars($payment_info['card_number']) . '</td>
        </tr>';

        if (!empty($payment_info['cardholder_name'])) {
            $payment_details .= '
            <tr>
                <td>Cardholder Name:</td>
                <td>' . htmlspecialchars($payment_info['cardholder_name']) . '</td>
            </tr>';
        }

        if (!empty($payment_info['account_name'])) {
            $payment_details .= '
            <tr>
                <td>Account Name:</td>
                <td>' . htmlspecialchars($payment_info['account_name']) . '</td>
            </tr>';
        }
    } elseif ($payment_info['payment_method'] === 'GCash') {
        $payment_details .= '
        <tr>
            <td>GCash Number:</td>
            <td>' . htmlspecialchars($payment_info['card_number']) . '</td>
        </tr>';

        if (!empty($payment_info['account_name'])) {
            $payment_details .= '
            <tr>
                <td>Account Name:</td>
                <td>' . htmlspecialchars($payment_info['account_name']) . '</td>
            </tr>';
        }
    } elseif ($payment_info['payment_method'] === 'PayMaya') {
        $payment_details .= '
        <tr>
            <td>PayMaya Number:</td>
            <td>' . htmlspecialchars($payment_info['card_number']) . '</td>
        </tr>';

        if (!empty($payment_info['account_name'])) {
            $payment_details .= '
            <tr>
                <td>Account Name:</td>
                <td>' . htmlspecialchars($payment_info['account_name']) . '</td>
            </tr>';
        }
    }
} else {
    $payment_details .= '
        <tr>
            <td>Payment Date:</td>
            <td>' . date('F j, Y', $created_timestamp) . '</td>
        </tr>
        <tr>
            <td>Payment Method:</td>
            <td>Credit Card</td>
        </tr>';
}

$payment_details .= '
    </table>
</div>';

// 6. Stay Summary Section
$stay_summary = '
<div class="receipt-section">
    <h3 class="receipt-section-title">Stay Summary</h3>
    <table>
        <tr>
            <td>Room Rate:</td>
            <td>₱' . number_format($booking['room_price'], 2) . ' per night</td>
        </tr>
        <tr>
            <td>Number of Nights:</td>
            <td>' . $booking['nights'] . '</td>
        </tr>
        <tr>
            <td>Calculation:</td>
            <td>';

// Always calculate the price calculation string on-the-fly
$stay_summary .= '₱' . number_format($booking['room_price'], 2) . ' × ' . $booking['nights'] . ' night' . ($booking['nights'] > 1 ? 's' : '');

$stay_summary .= '
            </td>
        </tr>
        <tr class="total-row">
            <td>Total Amount:</td>
            <td>₱' . number_format($booking['total_price'], 2) . '</td>
        </tr>
    </table>
</div>';

// 7. Footer Section
$footer = '
<div class="footer">
    <p>Thank you for your booking!</p>
</div>';

// Combine sections into a two-column layout
$columns_start = '<div class="columns">';
$column1_start = '<div class="column">';
$column2_start = '<div class="column">';
$column1_end = '</div>';
$column2_end = '</div>';
$columns_end = '</div>';

// Combine the sections into a single HTML string with columns
$combined_html = $columns_start .
                 $column1_start . $booking_info . $payment_details . $column1_end .
                 $column2_start . $room_details . $stay_summary . $column2_end .
                 $columns_end;

// Write HTML to the PDF in smaller chunks
$mpdf->WriteHTML($css);
$mpdf->WriteHTML($header);
$mpdf->WriteHTML($combined_html);
$mpdf->WriteHTML($footer);

// Output the PDF
$mpdf->Output('Receipt_' . $booking['booking_ID'] . '.pdf', 'I'); // 'I' sends the file inline to the browser
