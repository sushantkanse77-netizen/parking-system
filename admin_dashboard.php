<?php
session_start();
require_once 'config.php';

// Check admin authentication
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Get statistics
$stats = [];

// Total users
$stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
$stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total slots
$stmt = $pdo->query("SELECT COUNT(*) as count FROM parking_slots");
$stats['total_slots'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total bookings
$stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings");
$stats['total_bookings'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Total votes
$stmt = $pdo->query("SELECT COUNT(*) as count FROM status_votes");
$stats['total_votes'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Active bookings
$stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'active'");
$stats['active_bookings'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

// Available slots (based on vote logic)
$stmt = $pdo->query("
    SELECT s.*, 
           (SELECT COUNT(*) FROM status_votes WHERE slot_id = s.id AND vote_status = 'parked') as parked_votes,
           (SELECT COUNT(*) FROM status_votes WHERE slot_id = s.id AND vote_status = 'full') as full_votes
    FROM parking_slots s
");
$slots = $stmt->fetchAll(PDO::FETCH_ASSOC);
$available_slots = 0;
foreach($slots as $slot) {
    if($slot['full_votes'] <= $slot['parked_votes']) {
        $available_slots++;
    }
}
$stats['available_slots'] = $available_slots;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Parking System</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f0f2f5;
        }
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            transition: all 0.3s;
        }
        .sidebar-header {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-header h3 {
            font-size: 1.5rem;
        }
        .sidebar-header h3 i {
            margin-right: 10px;
            color: #667eea;
        }
        .sidebar-header p {
            font-size: 0.8rem;
            opacity: 0.7;
            margin-top: 8px;
        }
        .sidebar-nav {
            padding: 20px 0;
        }
        .nav-item {
            padding: 12px 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            cursor: pointer;
        }
        .nav-item:hover, .nav-item.active {
            background: rgba(102, 126, 234, 0.3);
            color: white;
            border-left: 3px solid #667eea;
        }
        .nav-item i {
            width: 25px;
            font-size: 1.2rem;
        }
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 20px;
        }
        /* Top Bar */
        .top-bar {
            background: white;
            padding: 15px 25px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .top-bar h2 {
            color: #333;
        }
        .admin-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .admin-info span {
            color: #666;
        }
        .logout-btn {
            background: #f56565;
            color: white;
            padding: 8px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s;
        }
        .logout-btn:hover {
            background: #e53e3e;
        }
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-info h4 {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 8px;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
        }
        .stat-icon {
            width: 55px;
            height: 55px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .stat-icon i {
            font-size: 1.8rem;
            color: white;
        }
        /* Section Cards */
        .section-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        .section-header h3 {
            color: #333;
        }
        .section-header h3 i {
            margin-right: 10px;
            color: #667eea;
        }
        .btn-refresh {
            background: #667eea;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 8px;
            cursor: pointer;
        }
        /* Tables */
        .data-table {
            width: 100%;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
        }
        .status-active {
            background: #c6f6d5;
            color: #22543d;
        }
        .status-pending {
            background: #fefcbf;
            color: #744210;
        }
        .status-completed {
            background: #e2e8f0;
            color: #2d3748;
        }
        .action-btn {
            padding: 5px 10px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 12px;
            margin: 0 3px;
            cursor: pointer;
            border: none;
        }
        .btn-delete {
            background: #f56565;
            color: white;
        }
        .btn-view {
            background: #4299e1;
            color: white;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
        }
        .modal-content h3 {
            margin-bottom: 20px;
        }
        .modal-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: flex-end;
        }
        .toast {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #333;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            display: none;
            z-index: 1100;
        }
        @media (max-width: 768px) {
            .sidebar {
                margin-left: -280px;
            }
            .main-content {
                margin-left: 0;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h3><i class="fas fa-crown"></i> ParkEase Admin</h3>
                <p>Parking System Management</p>
            </div>
            <div class="sidebar-nav">
                <a class="nav-item active" data-section="dashboard">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a class="nav-item" data-section="users">
                    <i class="fas fa-users"></i> Manage Users
                </a>
                <a class="nav-item" data-section="slots">
                    <i class="fas fa-parking"></i> Manage Slots
                </a>
                <a class="nav-item" data-section="bookings">
                    <i class="fas fa-calendar-check"></i> Manage Bookings
                </a>
                <a class="nav-item" data-section="votes">
                    <i class="fas fa-vote-yea"></i> Manage Votes
                </a>
                <a class="nav-item" data-section="settings">
                    <i class="fas fa-cog"></i> Admin Settings
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="top-bar">
                <h2><i class="fas fa-crown"></i> Admin Control Panel</h2>
                <div class="admin-info">
                    <span><i class="fas fa-user-shield"></i> <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="admin_logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            
            <!-- Dashboard Section -->
            <div id="dashboard-section">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <h4>Total Users</h4>
                            <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                        </div>
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h4>Parking Slots</h4>
                            <div class="stat-number"><?php echo $stats['total_slots']; ?></div>
                        </div>
                        <div class="stat-icon"><i class="fas fa-parking"></i></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h4>Available Slots</h4>
                            <div class="stat-number"><?php echo $stats['available_slots']; ?></div>
                        </div>
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h4>Total Bookings</h4>
                            <div class="stat-number"><?php echo $stats['total_bookings']; ?></div>
                        </div>
                        <div class="stat-icon"><i class="fas fa-calendar-check"></i></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h4>Active Bookings</h4>
                            <div class="stat-number"><?php echo $stats['active_bookings']; ?></div>
                        </div>
                        <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <h4>Total Votes</h4>
                            <div class="stat-number"><?php echo $stats['total_votes']; ?></div>
                        </div>
                        <div class="stat-icon"><i class="fas fa-vote-yea"></i></div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="section-card">
                    <div class="section-header">
                        <h3><i class="fas fa-history"></i> Recent Activity</h3>
                        <button class="btn-refresh" onclick="refreshData()"><i class="fas fa-sync-alt"></i> Refresh</button>
                    </div>
                    <div class="data-table">
                        <table id="recentActivityTable">
                            <thead>
                                <tr><th>Type</th><th>Details</th><th>Time</th></tr>
                            </thead>
                            <tbody>
                                <tr><td colspan="3" style="text-align:center;">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Users Section -->
            <div id="users-section" style="display: none;">
                <div class="section-card">
                    <div class="section-header">
                        <h3><i class="fas fa-users"></i> All Registered Users</h3>
                        <button class="btn-refresh" onclick="loadUsers()"><i class="fas fa-sync-alt"></i> Refresh</button>
                    </div>
                    <div class="data-table">
                        <table id="usersTable">
                            <thead>
                                <tr><th>ID</th><th>Name</th><th>Email</th><th>Registered On</th><th>Slots</th><th>Votes</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <tr><td colspan="7" style="text-align:center;">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Slots Section -->
            <div id="slots-section" style="display: none;">
                <div class="section-card">
                    <div class="section-header">
                        <h3><i class="fas fa-parking"></i> All Parking Slots</h3>
                        <button class="btn-refresh" onclick="loadSlots()"><i class="fas fa-sync-alt"></i> Refresh</button>
                    </div>
                    <div class="data-table">
                        <table id="slotsTable">
                            <thead>
                                <tr><th>ID</th><th>Location</th><th>Owner</th><th>Vehicle Type</th><th>Price</th><th>Status</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <tr><td colspan="7" style="text-align:center;">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Bookings Section -->
            <div id="bookings-section" style="display: none;">
                <div class="section-card">
                    <div class="section-header">
                        <h3><i class="fas fa-calendar-check"></i> All Bookings</h3>
                        <button class="btn-refresh" onclick="loadBookings()"><i class="fas fa-sync-alt"></i> Refresh</button>
                    </div>
                    <div class="data-table">
                        <table id="bookingsTable">
                            <thead>
                                <tr><th>ID</th><th>Slot</th><th>User</th><th>Booking Time</th><th>Status</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <tr><td colspan="6" style="text-align:center;">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Votes Section -->
            <div id="votes-section" style="display: none;">
                <div class="section-card">
                    <div class="section-header">
                        <h3><i class="fas fa-vote-yea"></i> All Status Votes</h3>
                        <button class="btn-refresh" onclick="loadVotes()"><i class="fas fa-sync-alt"></i> Refresh</button>
                    </div>
                    <div class="data-table">
                        <table id="votesTable">
                            <thead>
                                <tr><th>ID</th><th>Slot</th><th>User</th><th>Vote Type</th><th>Voted At</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <tr><td colspan="6" style="text-align:center;">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Settings Section -->
            <div id="settings-section" style="display: none;">
                <div class="section-card">
                    <div class="section-header">
                        <h3><i class="fas fa-cog"></i> Admin Settings</h3>
                    </div>
                    <div class="data-table">
                        <form id="changePasswordForm">
                            <h4>Change Admin Password</h4>
                            <input type="password" id="currentPassword" placeholder="Current Password" style="width:100%; padding:12px; margin:10px 0; border:1px solid #ddd; border-radius:8px;">
                            <input type="password" id="newPassword" placeholder="New Password" style="width:100%; padding:12px; margin:10px 0; border:1px solid #ddd; border-radius:8px;">
                            <input type="password" id="confirmPassword" placeholder="Confirm New Password" style="width:100%; padding:12px; margin:10px 0; border:1px solid #ddd; border-radius:8px;">
                            <button type="submit" class="btn-refresh" style="margin-top:10px;">Update Password</button>
                        </form>
                    </div>
                </div>
                
                <div class="section-card">
                    <div class="section-header">
                        <h3><i class="fas fa-database"></i> Database Statistics</h3>
                    </div>
                    <div class="data-table" id="dbStats">
                        Loading...
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Delete</h3>
            <p id="deleteMessage">Are you sure you want to delete this item?</p>
            <div class="modal-buttons">
                <button onclick="confirmDelete()" class="btn-delete" style="padding:8px 20px;">Delete</button>
                <button onclick="closeDeleteModal()" style="padding:8px 20px; background:#ccc; border:none; border-radius:5px;">Cancel</button>
            </div>
        </div>
    </div>
    
    <div id="toast" class="toast"></div>
    
    <script>
        let currentDeleteType = null;
        let currentDeleteId = null;
        
        // Navigation
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
                this.classList.add('active');
                
                const section = this.dataset.section;
                document.getElementById('dashboard-section').style.display = section === 'dashboard' ? 'block' : 'none';
                document.getElementById('users-section').style.display = section === 'users' ? 'block' : 'none';
                document.getElementById('slots-section').style.display = section === 'slots' ? 'block' : 'none';
                document.getElementById('bookings-section').style.display = section === 'bookings' ? 'block' : 'none';
                document.getElementById('votes-section').style.display = section === 'votes' ? 'block' : 'none';
                document.getElementById('settings-section').style.display = section === 'settings' ? 'block' : 'none';
                
                // Load data for the section
                if(section === 'users') loadUsers();
                if(section === 'slots') loadSlots();
                if(section === 'bookings') loadBookings();
                if(section === 'votes') loadVotes();
                if(section === 'settings') loadDbStats();
            });
        });
        
        function showToast(message) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.style.display = 'block';
            setTimeout(() => toast.style.display = 'none', 3000);
        }
        
        function refreshData() {
            loadRecentActivity();
            showToast('Data refreshed!');
        }
        
        function loadRecentActivity() {
            fetch('admin_api.php?action=recent_activity')
                .then(res => res.json())
                .then(data => {
                    const tbody = document.querySelector('#recentActivityTable tbody');
                    if(data.success && data.activities.length) {
                        tbody.innerHTML = data.activities.map(act => `
                            <tr>
                                <td><span class="status-badge">${act.type}</span></td>
                                <td>${act.details}</td>
                                <td>${act.time}</td>
                            </tr>
                        `).join('');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="3" style="text-align:center;">No recent activity</td></tr>';
                    }
                });
        }
        
        function loadUsers() {
            fetch('admin_api.php?action=get_users')
                .then(res => res.json())
                .then(data => {
                    const tbody = document.querySelector('#usersTable tbody');
                    if(data.success && data.users.length) {
                        tbody.innerHTML = data.users.map(user => `
                            <tr>
                                <td>${user.id}</td>
                                <td>${escapeHtml(user.name)}</td>
                                <td>${escapeHtml(user.email)}</td>
                                <td>${user.created_at}</td>
                                <td>${user.slots_count || 0}</td>
                                <td>${user.votes_count || 0}</td>
                                <td>
                                    <button class="action-btn btn-delete" onclick="showDeleteModal('user', ${user.id})">Delete</button>
                                </td>
                            </tr>
                        `).join('');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">No users found</td></tr>';
                    }
                });
        }
        
        function loadSlots() {
            fetch('admin_api.php?action=get_slots')
                .then(res => res.json())
                .then(data => {
                    const tbody = document.querySelector('#slotsTable tbody');
                    if(data.success && data.slots.length) {
                        tbody.innerHTML = data.slots.map(slot => `
                            <tr>
                                <td>${slot.id}</td>
                                <td>${escapeHtml(slot.location)}</td>
                                <td>${escapeHtml(slot.owner_name || 'Unknown')}</td>
                                <td>${slot.vehicle_type}</td>
                                <td>₹${slot.price_per_hour}</td>
                                <td><span class="status-badge ${slot.status === 'available' ? 'status-active' : 'status-pending'}">${slot.status}</span></td>
                                <td>
                                    <button class="action-btn btn-delete" onclick="showDeleteModal('slot', ${slot.id})">Delete</button>
                                </td>
                            </tr>
                        `).join('');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">No slots found</td></tr>';
                    }
                });
        }
        
        function loadBookings() {
            fetch('admin_api.php?action=get_bookings')
                .then(res => res.json())
                .then(data => {
                    const tbody = document.querySelector('#bookingsTable tbody');
                    if(data.success && data.bookings.length) {
                        tbody.innerHTML = data.bookings.map(booking => `
                            <tr>
                                <td>${booking.id}</td>
                                <td>${escapeHtml(booking.slot_location || 'N/A')}</td>
                                <td>${escapeHtml(booking.user_name || 'N/A')}</td>
                                <td>${booking.booking_time}</td>
                                <td><span class="status-badge ${booking.status === 'active' ? 'status-active' : 'status-completed'}">${booking.status}</span></td>
                                <td>
                                    <button class="action-btn btn-delete" onclick="showDeleteModal('booking', ${booking.id})">Delete</button>
                                </td>
                            </tr>
                        `).join('');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No bookings found</td></tr>';
                    }
                });
        }
        
        function loadVotes() {
            fetch('admin_api.php?action=get_votes')
                .then(res => res.json())
                .then(data => {
                    const tbody = document.querySelector('#votesTable tbody');
                    if(data.success && data.votes.length) {
                        tbody.innerHTML = data.votes.map(vote => `
                            <tr>
                                <td>${vote.id}</td>
                                <td>${escapeHtml(vote.slot_location || 'N/A')}</td>
                                <td>${escapeHtml(vote.user_name || 'N/A')}</td>
                                <td><span class="status-badge ${vote.vote_status === 'parked' ? 'status-active' : 'status-pending'}">${vote.vote_status}</span></td>
                                <td>${vote.voted_at}</td>
                                <td>
                                    <button class="action-btn btn-delete" onclick="showDeleteModal('vote', ${vote.id})">Delete</button>
                                </td>
                            </tr>
                        `).join('');
                    } else {
                        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No votes found</td></tr>';
                    }
                });
        }
        
        function loadDbStats() {
    fetch('admin_api.php?action=db_stats')
        .then(res => res.json())
        .then(data => {
            const container = document.getElementById('dbStats');
            if(data.success) {
                container.innerHTML = `
                    <table style="width:100%">
                        <tr><th>Table</th><th>Row Count</th><th>Size (MB)</th></tr>
                        <tr><td>users</td><td>${data.stats.users.rows}</td><td>${data.stats.users.size}</td></tr>
                        <tr><td>parking_slots</td><td>${data.stats.slots.rows}</td><td>${data.stats.slots.size}</td></tr>
                        <tr><td>bookings</td><td>${data.stats.bookings.rows}</td><td>${data.stats.bookings.size}</td></tr>
                        <tr><td>status_votes</td><td>${data.stats.votes.rows}</td><td>${data.stats.votes.size}</td></tr>
                    </table>
                `;
            } else {
                container.innerHTML = '<p style="color:red;">Error loading statistics: ' + (data.error || 'Unknown error') + '</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('dbStats').innerHTML = '<p style="color:red;">Failed to load database statistics. Check console for details.</p>';
        });
}
        
        function showDeleteModal(type, id) {
            currentDeleteType = type;
            currentDeleteId = id;
            const messages = {
                user: 'Are you sure you want to delete this user? All their slots and votes will also be deleted.',
                slot: 'Are you sure you want to delete this parking slot?',
                booking: 'Are you sure you want to delete this booking?',
                vote: 'Are you sure you want to delete this vote?'
            };
            document.getElementById('deleteMessage').textContent = messages[type];
            document.getElementById('deleteModal').style.display = 'flex';
        }
        
        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            currentDeleteType = null;
            currentDeleteId = null;
        }
        
        function confirmDelete() {
            if(!currentDeleteType || !currentDeleteId) return;
            
            fetch(`admin_api.php?action=delete_${currentDeleteType}&id=${currentDeleteId}`, {
                method: 'DELETE'
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    showToast(data.message);
                    closeDeleteModal();
                    // Reload current section
                    if(currentDeleteType === 'user') loadUsers();
                    if(currentDeleteType === 'slot') loadSlots();
                    if(currentDeleteType === 'booking') loadBookings();
                    if(currentDeleteType === 'vote') loadVotes();
                    refreshData();
                } else {
                    showToast('Error: ' + data.message);
                }
            });
        }
        
        // Password change
        document.getElementById('changePasswordForm')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const current = document.getElementById('currentPassword').value;
            const newPass = document.getElementById('newPassword').value;
            const confirm = document.getElementById('confirmPassword').value;
            
            if(newPass !== confirm) {
                showToast('New passwords do not match!');
                return;
            }
            
            fetch('admin_api.php?action=change_password', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({current_password: current, new_password: newPass})
            })
            .then(res => res.json())
            .then(data => {
                showToast(data.message);
                if(data.success) {
                    document.getElementById('currentPassword').value = '';
                    document.getElementById('newPassword').value = '';
                    document.getElementById('confirmPassword').value = '';
                }
            });
        });
        
        function escapeHtml(str) {
            if(!str) return '';
            return str.replace(/[&<>]/g, function(m) {
                if(m === '&') return '&amp;';
                if(m === '<') return '&lt;';
                if(m === '>') return '&gt;';
                return m;
            });
        }
        
        // Load initial data
        loadRecentActivity();
        
        // Auto-refresh every 10 seconds
        setInterval(() => {
            if(document.getElementById('dashboard-section').style.display !== 'none') {
                loadRecentActivity();
            }
        }, 10000);
    </script>
</body>
</html>