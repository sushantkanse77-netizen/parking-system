<?php
require_once 'config.php';
// config.php already handles session_start()
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Create uploads directory if not exists
$upload_dir = 'uploads/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $location = $_POST['location'];
    $google_maps_link = $_POST['google_maps_link'];
    $vehicle_type = $_POST['vehicle_type'];
    $price_per_hour = $_POST['price_per_hour'];
    $time_availability = $_POST['time_availability'];
    $landmark = $_POST['landmark'];
    $address_details = $_POST['address_details'];
    $contact_number = $_POST['contact_number'];
    $user_id = $_SESSION['user_id'];
    
    // Handle image upload
    $image_path = '';
    if (isset($_FILES['slot_image']) && $_FILES['slot_image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['slot_image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            $new_filename = time() . '_' . rand(1000, 9999) . '.' . $ext;
            $destination = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['slot_image']['tmp_name'], $destination)) {
                $image_path = $destination;
            }
        }
    }
    
    $stmt = $pdo->prepare("INSERT INTO parking_slots (user_id, location, google_maps_link, vehicle_type, price_per_hour, time_availability, image_path, landmark, address_details, contact_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $location, $google_maps_link, $vehicle_type, $price_per_hour, $time_availability, $image_path, $landmark, $address_details, $contact_number]);
    
    header("Location: dashboard.php?added=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Parking Slot with Image</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="form-container large-form">
            <h2>📸 Add Parking Slot with Photo</h2>
            <form method="POST" enctype="multipart/form-data">
                <!-- Basic Information -->
                <div class="form-section">
                    <h3>📍 Location Information</h3>
                    <input type="text" name="location" placeholder="Main Location / Area Name" required>
                    <input type="text" name="landmark" placeholder="Nearby Landmark (e.g., Near City Mall)">
                    <textarea name="address_details" rows="3" placeholder="Complete Address Details..."></textarea>
                    <input type="url" name="google_maps_link" placeholder="Google Maps Link (optional)">
                    <input type="text" name="contact_number" placeholder="Contact Number (optional)">
                </div>
                
                <div class="form-section">
                    <h3>🖼️ Parking Space Photo</h3>
                    <div class="image-upload-area">
                        <input type="file" name="slot_image" accept="image/*" id="imageUpload" onchange="previewImage(this)">
                        <div id="imagePreview" class="image-preview">
                            <span class="preview-placeholder">📷 Click to upload parking space photo</span>
                        </div>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3>🚗 Parking Details</h3>
                    <select name="vehicle_type" required>
                        <option value="car">🚗 Car</option>
                        <option value="bike">🛵 Bike</option>
                        <option value="auto">🛺 Auto</option>
                        <option value="truck">🚚 Truck</option>
                    </select>
                    <input type="number" step="0.01" name="price_per_hour" placeholder="Price per Hour (₹)" required>
                    <input type="text" name="time_availability" placeholder="Available Time (e.g., 9 AM - 6 PM)" required>
                </div>
                
                <button type="submit">✅ Add Parking Slot</button>
            </form>
            <a href="dashboard.php">← Back to Dashboard</a>
        </div>
    </div>
    
    <script>
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" style="max-width: 100%; max-height: 200px; border-radius: 8px;">`;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    </script>
</body>
</html>