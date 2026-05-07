<?php
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parking Slot Sharing System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>🅿️ Parking Slot Sharing System</h1>
            <div class="user-info">
    Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?> | 
    <a href="add_slot.php">➕ Add Slot</a> |
    <a href="my_bookings.php">📅 My Bookings</a> | 
    <a href="logout.php">🚪 Logout</a>
</div>
        </header>
        
        <!-- User Stats -->
        <div class="stats-dashboard">
            <div class="stat-card">
                <div class="stat-value" id="mySlots">0</div>
                <div class="stat-label"><p style="color: black;">My Parking Slots</p></div>
            </div>
            <div class="stat-card">
                <div class="stat-value" id="myVotes">0</div>
                <div class="stat-label"><p style="color: black;">My Votes Cast</p></div>
            </div>
        </div>
        
        
        
        <div class="search-section">
            <h3>🔍 Find Available Parking</h3>
            <div class="search-form">
                <input type="text" id="searchLocation" placeholder="Search by location, landmark, or address...">
                <select id="vehicleType">
                    <option value="">All Vehicles</option>
                    <option value="car">🚗 Car</option>
                    <option value="bike">🛵 Bike</option>
                    <option value="auto">🛺 Auto</option>
                    <option value="truck">🚚 Truck</option>
                </select>
                <button id="searchBtn">🔍 Search</button>
            </div>
        </div>
        
        <div id="parkingSlots" class="parking-grid"></div>
    </div>
    
    <!-- Image Modal for Full View -->
    <div id="imageModal" class="modal">
        <div class="modal-content image-modal">
            <span class="close" onclick="closeImageModal()">&times;</span>
            <img id="modalImage" src="" alt="Parking Space">
        </div>
    </div>
    
    <script>
        // Pass PHP session data to JavaScript
        const CURRENT_USER_ID = <?php echo $_SESSION['user_id']; ?>;
    </script>
    <script src="script.js"></script>
</body>
</html>