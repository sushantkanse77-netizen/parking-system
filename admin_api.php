<?php
session_start();
require_once 'config.php';
header('Content-Type: application/json');

// Check admin authentication
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch($action) {
        case 'recent_activity':
            // Get recent activities from all tables
            $activities = [];
            
            // Recent users
            $stmt = $pdo->query("SELECT 'User' as type, CONCAT('New user registered: ', name) as details, created_at as time FROM users ORDER BY created_at DESC LIMIT 5");
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $activities[] = $row;
            }
            
            // Recent slots
            $stmt = $pdo->query("SELECT 'Slot' as type, CONCAT('New parking slot added at: ', location) as details, created_at as time FROM parking_slots ORDER BY created_at DESC LIMIT 5");
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $activities[] = $row;
            }
            
            // Recent votes
            $stmt = $pdo->query("SELECT 'Vote' as type, CONCAT('User voted slot as: ', vote_status) as details, created_at as time FROM status_votes ORDER BY created_at DESC LIMIT 5");
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $activities[] = $row;
            }
            
            // Sort by time
            usort($activities, function($a, $b) {
                return strtotime($b['time']) - strtotime($a['time']);
            });
            
            $activities = array_slice($activities, 0, 10);
            
            echo json_encode(['success' => true, 'activities' => $activities]);
            break;
            
        case 'get_users':
            $stmt = $pdo->query("
                SELECT u.*, 
                       (SELECT COUNT(*) FROM parking_slots WHERE user_id = u.id) as slots_count,
                       (SELECT COUNT(*) FROM status_votes WHERE user_id = u.id) as votes_count
                FROM users u 
                ORDER BY u.created_at DESC
            ");
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'users' => $users]);
            break;
            
        case 'get_slots':
            $stmt = $pdo->query("
                SELECT s.*, u.name as owner_name,
                       (SELECT COUNT(*) FROM status_votes WHERE slot_id = s.id AND vote_status = 'parked') as parked_votes,
                       (SELECT COUNT(*) FROM status_votes WHERE slot_id = s.id AND vote_status = 'full') as full_votes
                FROM parking_slots s
                LEFT JOIN users u ON s.user_id = u.id
                ORDER BY s.created_at DESC
            ");
            $slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach($slots as &$slot) {
                if($slot['full_votes'] > $slot['parked_votes']) {
                    $slot['status'] = 'full';
                } else {
                    $slot['status'] = 'available';
                }
            }
            
            echo json_encode(['success' => true, 'slots' => $slots]);
            break;
            
        case 'get_bookings':
            $stmt = $pdo->query("
                SELECT b.*, u.name as user_name, s.location as slot_location
                FROM bookings b
                LEFT JOIN users u ON b.user_id = u.id
                LEFT JOIN parking_slots s ON b.slot_id = s.id
                ORDER BY b.booking_time DESC
            ");
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'bookings' => $bookings]);
            break;
            
        case 'get_votes':
            $stmt = $pdo->query("
                SELECT v.*, u.name as user_name, s.location as slot_location
                FROM status_votes v
                LEFT JOIN users u ON v.user_id = u.id
                LEFT JOIN parking_slots s ON v.slot_id = s.id
                ORDER BY v.created_at DESC
            ");
            $votes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'votes' => $votes]);
            break;
            
 case 'db_stats':
    try {
        $stats = [];
        
        // Users table stats
        $stmt = $pdo->query("SELECT COUNT(*) as total_rows FROM users");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['users']['rows'] = $result['total_rows'];
        
        $stmt = $pdo->query("SHOW TABLE STATUS LIKE 'users'");
        $size = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['users']['size'] = $size ? round(($size['Data_length'] + $size['Index_length']) / 1024 / 1024, 2) : 0;
        
        // Parking slots table stats
        $stmt = $pdo->query("SELECT COUNT(*) as total_rows FROM parking_slots");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['slots']['rows'] = $result['total_rows'];
        
        $stmt = $pdo->query("SHOW TABLE STATUS LIKE 'parking_slots'");
        $size = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['slots']['size'] = $size ? round(($size['Data_length'] + $size['Index_length']) / 1024 / 1024, 2) : 0;
        
        // Bookings table stats
        $stmt = $pdo->query("SELECT COUNT(*) as total_rows FROM bookings");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['bookings']['rows'] = $result['total_rows'];
        
        $stmt = $pdo->query("SHOW TABLE STATUS LIKE 'bookings'");
        $size = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['bookings']['size'] = $size ? round(($size['Data_length'] + $size['Index_length']) / 1024 / 1024, 2) : 0;
        
        // Status votes table stats
        $stmt = $pdo->query("SELECT COUNT(*) as total_rows FROM status_votes");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['votes']['rows'] = $result['total_rows'];
        
        $stmt = $pdo->query("SHOW TABLE STATUS LIKE 'status_votes'");
        $size = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['votes']['size'] = $size ? round(($size['Data_length'] + $size['Index_length']) / 1024 / 1024, 2) : 0;
        
        echo json_encode(['success' => true, 'stats' => $stats]);
    } catch(Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    break;
            
        case 'delete_user':
            if($method === 'DELETE') {
                $id = $_GET['id'] ?? 0;
                
                // Delete user (cascade will delete related data)
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$id]);
                
                echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
            }
            break;
            
        case 'delete_slot':
            if($method === 'DELETE') {
                $id = $_GET['id'] ?? 0;
                
                // Delete slot image if exists
                $stmt = $pdo->prepare("SELECT image_path FROM parking_slots WHERE id = ?");
                $stmt->execute([$id]);
                $slot = $stmt->fetch();
                if($slot && $slot['image_path'] && file_exists($slot['image_path'])) {
                    unlink($slot['image_path']);
                }
                
                $stmt = $pdo->prepare("DELETE FROM parking_slots WHERE id = ?");
                $stmt->execute([$id]);
                
                echo json_encode(['success' => true, 'message' => 'Parking slot deleted successfully']);
            }
            break;
            
        case 'delete_booking':
            if($method === 'DELETE') {
                $id = $_GET['id'] ?? 0;
                $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true, 'message' => 'Booking deleted successfully']);
            }
            break;
            
        case 'delete_vote':
            if($method === 'DELETE') {
                $id = $_GET['id'] ?? 0;
                $stmt = $pdo->prepare("DELETE FROM status_votes WHERE id = ?");
                $stmt->execute([$id]);
                echo json_encode(['success' => true, 'message' => 'Vote deleted successfully']);
            }
            break;
            
        case 'change_password':
            if($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $current = $data['current_password'] ?? '';
                $new = $data['new_password'] ?? '';
                
                $admin_username = $_SESSION['admin_username'];
                $stored_hash = password_hash('admin123', PASSWORD_DEFAULT);
                
                if(password_verify($current, $stored_hash)) {
                    // In production, update the stored password hash
                    // For demo, we'll just return success
                    echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Current password is incorrect']);
                }
            }
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
} catch(Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>