<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$slot_id = $_GET['id'] ?? 0;
$error = '';
$slot = null;

// Get slot details
$stmt = $pdo->prepare("
    SELECT s.*, u.name as owner_name, u.id as owner_id,
           (SELECT COUNT(*) FROM status_votes WHERE slot_id = s.id AND vote_status = 'parked') as parked_votes,
           (SELECT COUNT(*) FROM status_votes WHERE slot_id = s.id AND vote_status = 'full') as full_votes
    FROM parking_slots s
    LEFT JOIN users u ON s.user_id = u.id
    WHERE s.id = ?
");
$stmt->execute([$slot_id]);
$slot = $stmt->fetch();

if (!$slot) {
    header("Location: dashboard.php");
    exit();
}

// Check if slot is available
$full_votes = $slot['full_votes'];
$parked_votes = $slot['parked_votes'];
$is_available = ($full_votes <= $parked_votes);

if (!$is_available) {
    $error = "This parking slot is currently not available for booking!";
}

// Handle booking submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $is_available) {
    $duration_hours = floatval($_POST['duration_hours']);
    $total_price = $duration_hours * $slot['price_per_hour'];
    $booking_date = $_POST['booking_date'];
    $booking_time = $_POST['booking_time'];
    
    // Check if user already has an active booking for this slot
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE slot_id = ? AND user_id = ? AND status = 'active'");
    $stmt->execute([$slot_id, $_SESSION['user_id']]);
    if ($stmt->rowCount() > 0) {
        $error = "You already have an active booking for this slot!";
    } else {
        // Create booking
        $stmt = $pdo->prepare("
            INSERT INTO bookings (slot_id, user_id, duration_hours, total_price, booking_date, booking_time, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'active')
        ");
        $stmt->execute([$slot_id, $_SESSION['user_id'], $duration_hours, $total_price, $booking_date, $booking_time]);
        
        // Add a 'full' vote to mark as occupied
        $stmt = $pdo->prepare("
            INSERT INTO status_votes (slot_id, user_id, vote_status) 
            VALUES (?, ?, 'full')
            ON DUPLICATE KEY UPDATE vote_status = 'full'
        ");
        $stmt->execute([$slot_id, $_SESSION['user_id']]);
        
        header("Location: my_bookings.php?booked=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Parking Slot</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .booking-container {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .slot-preview {
            background: #f7f7f7;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        .slot-preview h3 {
            color: #667eea;
            margin-bottom: 15px;
        }
        .price-detail {
            font-size: 24px;
            font-weight: bold;
            color: #48bb78;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        .total-price {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin: 20px 0;
        }
        .total-price span {
            font-size: 28px;
            font-weight: bold;
        }
        .btn-book {
            width: 100%;
            padding: 14px;
            background: #48bb78;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-book:hover {
            background: #38a169;
            transform: translateY(-2px);
        }
        .btn-book:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        .error-message {
            background: #fed7d7;
            color: #c53030;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #667eea;
            text-decoration: none;
        }
        .slot-image-preview {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="booking-container">
        <h2>📅 Book Parking Slot</h2>
        
        <?php if($error): ?>
            <div class="error-message">❌ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="slot-preview">
            <h3>📍 <?php echo htmlspecialchars($slot['location']); ?></h3>
            
            <?php if($slot['image_path'] && file_exists($slot['image_path'])): ?>
                <img src="<?php echo $slot['image_path']; ?>" alt="Parking Space" class="slot-image-preview">
            <?php endif; ?>
            
            <div class="detail-row">
                <span>🚗 Vehicle Type:</span>
                <span><strong><?php echo strtoupper($slot['vehicle_type']); ?></strong></span>
            </div>
            <div class="detail-row">
                <span>💰 Price per Hour:</span>
                <span class="price-detail">₹<?php echo number_format($slot['price_per_hour'], 2); ?></span>
            </div>
            <div class="detail-row">
                <span>⏰ Available Time:</span>
                <span><?php echo htmlspecialchars($slot['time_availability']); ?></span>
            </div>
            <div class="detail-row">
                <span>👤 Provided by:</span>
                <span><?php echo htmlspecialchars($slot['owner_name']); ?></span>
            </div>
        </div>
        
        <?php if($is_available): ?>
        <form method="POST">
            <div class="form-group">
                <label>📅 Booking Date</label>
                <input type="date" name="booking_date" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            
            <div class="form-group">
                <label>⏰ Booking Time</label>
                <input type="time" name="booking_time" required>
            </div>
            
            <div class="form-group">
                <label>🕐 Duration (Hours)</label>
                <input type="number" name="duration_hours" id="duration" step="0.5" min="0.5" max="24" required onchange="calculateTotal()" onkeyup="calculateTotal()">
            </div>
            
            <div class="total-price">
                Total Amount: <span id="totalAmount">₹0.00</span>
            </div>
            
            <button type="submit" class="btn-book">✅ Confirm Booking</button>
        </form>
        <?php else: ?>
            <div class="error-message">❌ This slot is currently not available for booking. It may be full or occupied.</div>
        <?php endif; ?>
        
        <a href="dashboard.php" class="back-link">← Back to Dashboard</a>
    </div>
    
    <script>
        const pricePerHour = <?php echo $slot['price_per_hour']; ?>;
        
        function calculateTotal() {
            const duration = document.getElementById('duration').value;
            if(duration && duration > 0) {
                const total = duration * pricePerHour;
                document.getElementById('totalAmount').innerHTML = '₹' + total.toFixed(2);
            } else {
                document.getElementById('totalAmount').innerHTML = '₹0.00';
            }
        }
    </script>
</body>
</html>