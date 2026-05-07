<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = isset($_GET['booked']) ? 'Booking created successfully!' : (isset($_GET['cancelled']) ? 'Booking cancelled successfully!' : '');

// Get user's bookings
$stmt = $pdo->prepare("
    SELECT b.*, s.location, s.price_per_hour, s.vehicle_type, s.image_path, s.user_id as owner_id
    FROM bookings b
    JOIN parking_slots s ON b.slot_id = s.id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();

// Handle cancellation
if (isset($_GET['cancel'])) {
    $booking_id = $_GET['cancel'];
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ? AND status = 'active'");
    $stmt->execute([$booking_id, $user_id]);
    $booking = $stmt->fetch();
    
    if ($booking) {
        // Update booking status
        $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$booking_id]);
        
        // Remove the user's vote for this slot
        $stmt = $pdo->prepare("DELETE FROM status_votes WHERE slot_id = ? AND user_id = ? AND vote_status = 'full'");
        $stmt->execute([$booking['slot_id'], $user_id]);
        
        header("Location: my_bookings.php?cancelled=1");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Parking System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .bookings-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
        }
        .page-header {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .page-header h2 {
            color: #333;
        }
        .success-message {
            background: #c6f6d5;
            color: #22543d;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .booking-card {
            background: white;
            border-radius: 15px;
            margin-bottom: 20px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .booking-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .booking-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .booking-status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-active {
            background: #48bb78;
            color: white;
        }
        .status-completed {
            background: #4299e1;
            color: white;
        }
        .status-cancelled {
            background: #f56565;
            color: white;
        }
        .booking-body {
            padding: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .booking-image {
            width: 150px;
            height: 150px;
            border-radius: 10px;
            overflow: hidden;
            background: #f0f0f0;
        }
        .booking-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .booking-image .no-image {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            font-size: 2rem;
            color: #999;
        }
        .booking-details {
            flex: 1;
        }
        .booking-details h3 {
            color: #333;
            margin-bottom: 10px;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }
        .detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
            font-size: 14px;
        }
        .detail-item i {
            width: 20px;
            color: #667eea;
        }
        .booking-footer {
            padding: 15px 20px;
            background: #f7f7f7;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .total-amount {
            font-size: 20px;
            font-weight: bold;
            color: #48bb78;
        }
        .btn-cancel {
            background: #f56565;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-cancel:hover {
            background: #e53e3e;
        }
        .no-bookings {
            text-align: center;
            padding: 60px;
            background: white;
            border-radius: 15px;
        }
        .no-bookings a {
            display: inline-block;
            margin-top: 15px;
            background: #667eea;
            color: white;
            padding: 10px 25px;
            border-radius: 8px;
            text-decoration: none;
        }
        @media (max-width: 768px) {
            .booking-body {
                flex-direction: column;
            }
            .booking-image {
                width: 100%;
                height: 180px;
            }
        }
    </style>
</head>
<body>
    <div class="bookings-container">
        <div class="page-header">
            <h2><i class="fas fa-calendar-check"></i> My Bookings</h2>
            <a href="dashboard.php" style="background: #667eea; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">← Back to Dashboard</a>
        </div>
        
        <?php if($success): ?>
            <div class="success-message">✅ <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if(count($bookings) > 0): ?>
            <?php foreach($bookings as $booking): ?>
                <div class="booking-card">
                    <div class="booking-header">
                        <strong>Booking #<?php echo $booking['id']; ?></strong>
                        <span class="booking-status status-<?php echo $booking['status']; ?>">
                            <?php echo strtoupper($booking['status']); ?>
                        </span>
                    </div>
                    
                    <div class="booking-body">
                        <div class="booking-image">
                            <?php if($booking['image_path'] && file_exists($booking['image_path'])): ?>
                                <img src="<?php echo $booking['image_path']; ?>" alt="Parking Space">
                            <?php else: ?>
                                <div class="no-image">📷</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="booking-details">
                            <h3>📍 <?php echo htmlspecialchars($booking['location']); ?></h3>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <i class="fas fa-calendar"></i>
                                    <span>Date: <?php echo date('d M Y', strtotime($booking['booking_date'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-clock"></i>
                                    <span>Time: <?php echo date('h:i A', strtotime($booking['booking_time'])); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-hourglass-half"></i>
                                    <span>Duration: <?php echo $booking['duration_hours']; ?> hours</span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-car"></i>
                                    <span>Vehicle: <?php echo strtoupper($booking['vehicle_type']); ?></span>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>Booked on: <?php echo date('d M Y, h:i A', strtotime($booking['created_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="booking-footer">
                        <div>
                            <span style="color: #666;">Total Amount: </span>
                            <span class="total-amount">₹<?php echo number_format($booking['total_price'], 2); ?></span>
                        </div>
                        <?php if($booking['status'] == 'active'): ?>
                            <a href="?cancel=<?php echo $booking['id']; ?>" class="btn-cancel" onclick="return confirm('Are you sure you want to cancel this booking?')">Cancel Booking</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-bookings">
                <div style="font-size: 4rem;">📅</div>
                <h3>No Bookings Yet</h3>
                <p>You haven't made any parking bookings yet.</p>
                <a href="dashboard.php">Browse Parking Slots</a>
            </div>
        <?php endif; ?>
    </div>
    
    <style>
        /* Add Font Awesome if not already included */
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css');
    </style>
</body>
</html>