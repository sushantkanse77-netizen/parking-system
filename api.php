<?php
require_once 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

try {
    switch($action) {
        case 'getSlots':
            $location = $_GET['location'] ?? '';
            $vehicle_type = $_GET['vehicle_type'] ?? '';
            
            $query = "SELECT s.*, 
                      (SELECT COUNT(*) FROM status_votes WHERE slot_id = s.id AND vote_status = 'parked') as parked_votes,
                      (SELECT COUNT(*) FROM status_votes WHERE slot_id = s.id AND vote_status = 'full') as full_votes
                      FROM parking_slots s 
                      WHERE 1=1";
            $params = [];
            
            if($location) {
                $query .= " AND (s.location LIKE ? OR s.google_maps_link LIKE ? OR s.landmark LIKE ? OR s.address_details LIKE ?)";
                $params[] = "%$location%";
                $params[] = "%$location%";
                $params[] = "%$location%";
                $params[] = "%$location%";
            }
            if($vehicle_type) {
                $query .= " AND s.vehicle_type = ?";
                $params[] = $vehicle_type;
            }
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach($slots as &$slot) {
                // Voting-based status logic
                if($slot['full_votes'] > $slot['parked_votes']) {
                    $slot['display_status'] = 'full';
                    $slot['status_message'] = '🈵 Full';
                    $slot['status_color'] = '#f56565';
                    $slot['available'] = false;
                } else if($slot['parked_votes'] > 0) {
                    $slot['display_status'] = 'occupied';
                    $slot['status_message'] = '🚗 Occupied';
                    $slot['status_color'] = '#ecc94b';
                    $slot['available'] = false;
                } else {
                    $slot['display_status'] = 'available';
                    $slot['status_message'] = '✅ Available';
                    $slot['status_color'] = '#48bb78';
                    $slot['available'] = true;
                }
                
                // Fix image path
                if($slot['image_path'] && file_exists($slot['image_path'])) {
                    $slot['image_url'] = $slot['image_path'];
                } else {
                    $slot['image_url'] = null;
                }
            }
            
            echo json_encode(['success' => true, 'slots' => $slots]);
            break;
            
        case 'vote':
            $data = json_decode(file_get_contents('php://input'), true);
            $slot_id = $data['slot_id'];
            $vote = $data['vote'];
            
            $stmt = $pdo->prepare("SELECT * FROM status_votes WHERE slot_id = ? AND user_id = ?");
            $stmt->execute([$slot_id, $user_id]);
            $existing = $stmt->fetch();
            
            if($existing) {
                if($existing['vote_status'] == $vote) {
                    $stmt = $pdo->prepare("DELETE FROM status_votes WHERE slot_id = ? AND user_id = ?");
                    $stmt->execute([$slot_id, $user_id]);
                    $message = "Vote removed";
                } else {
                    $stmt = $pdo->prepare("UPDATE status_votes SET vote_status = ? WHERE slot_id = ? AND user_id = ?");
                    $stmt->execute([$vote, $slot_id, $user_id]);
                    $message = "Vote updated";
                }
            } else {
                $stmt = $pdo->prepare("INSERT INTO status_votes (slot_id, user_id, vote_status) VALUES (?, ?, ?)");
                $stmt->execute([$slot_id, $user_id, $vote]);
                $message = "Vote added";
            }
            
            echo json_encode(['success' => true, 'message' => $message]);
            break;
            
        case 'getUserStats':
            $stmt = $pdo->prepare("SELECT 
                                  (SELECT COUNT(*) FROM parking_slots WHERE user_id = ?) as my_slots,
                                  (SELECT COUNT(*) FROM status_votes WHERE user_id = ?) as my_votes");
            $stmt->execute([$user_id, $user_id]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'stats' => $stats]);
            break;
    }
} catch(Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>