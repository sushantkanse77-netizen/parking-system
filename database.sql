

CREATE DATABASE IF NOT EXISTS parking_system;
USE parking_system;

-- Table: users
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: parking_slots
CREATE TABLE parking_slots (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    location TEXT NOT NULL,
    google_maps_link TEXT,
    vehicle_type ENUM('car', 'bike', 'auto', 'truck') NOT NULL,
    price_per_hour DECIMAL(10,2) NOT NULL,
    time_availability TEXT NOT NULL,
    status ENUM('available', 'occupied', 'pending') DEFAULT 'available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table: bookings
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slot_id INT NOT NULL,
    user_id INT NOT NULL,
    booking_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    FOREIGN KEY (slot_id) REFERENCES parking_slots(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table: status_votes
CREATE TABLE status_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slot_id INT NOT NULL,
    user_id INT NOT NULL,
    vote_status ENUM('parked', 'full') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_vote (slot_id, user_id),
    FOREIGN KEY (slot_id) REFERENCES parking_slots(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);