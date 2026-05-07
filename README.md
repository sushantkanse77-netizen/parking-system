Parking Slot Sharing System

A comprehensive web-based parking slot sharing system that allows users to list, find, and book parking spaces. The system features community-driven status voting, Google Maps integration, and an admin dashboard for complete management.
🚀 Features
For Users

    User Authentication - Secure registration and login system

    Parking Slot Management - Add, view, and manage parking slots with photos

    Advanced Search - Search by location, landmark, address, and vehicle type

    Real-time Status - Community voting system to mark slots as "Parked" or "Full"

    Booking System - Book parking slots with duration and price calculation

    My Bookings - View and cancel your bookings

    Image Preview - Upload and view parking space photos

For Admins

    Admin Dashboard - Complete overview of system statistics

    User Management - View and delete user accounts

    Slot Management - Manage all parking slots across the system

    Booking Management - Monitor and manage all bookings

    Vote Management - Track community votes

    Database Statistics - View table sizes and row counts

    Activity Monitoring - Real-time recent activity feed

🛠️ Tech Stack

    Backend: PHP (Native)

    Database: MySQL

    Frontend: HTML5, CSS3, JavaScript

    Additional: Google Maps API integration, Font Awesome icons

📋 Prerequisites

    PHP 7.4 or higher

    MySQL 5.7 or higher

    Web server (Apache/XAMPP/WAMP/MAMP)

    Web browser with JavaScript enabled

🔧 Installation

    Clone the repository
    bash

    git clone https://github.com/yourusername/parking-slot-sharing-system.git
    cd parking-slot-sharing-system

    Set up the database

        Create a new MySQL database

        Import the database.sql file:
    bash

    mysql -u root -p your_database_name < database.sql

    Configure database connection

        Open config.php

        Update database credentials:
    php

    $host = 'localhost';
    $dbname = 'your_database_name';
    $username = 'your_username';
    $password = 'your_password';

    Set up directory permissions

        Create an uploads/ directory in the project root

        Set proper permissions (755 or 777):
    bash

    mkdir uploads
    chmod 755 uploads

    Configure web server

        Place the project in your web server directory (e.g., htdocs for XAMPP)

        Ensure PHP sessions are enabled

    Access the application

        Open your browser and navigate to http://localhost/parking-system/

🔐 Default Admin Credentials
Username	Password
admin	admin123
📁 Project Structure
text

parking-system/
├── admin_api.php          # Admin API endpoints
├── admin_dashboard.php    # Admin control panel
├── admin_login.php        # Admin login page
├── admin_logout.php       # Admin logout handler
├── api.php                # User API endpoints
├── book_slot.php          # Booking page
├── config.php             # Database configuration
├── dashboard.php          # User dashboard
├── database.sql           # Database schema
├── index.php              # Landing page
├── login.php              # User login
├── logout.php             # User logout
├── my_bookings.php        # User bookings page
├── register.php           # User registration
├── script.js              # Frontend JavaScript
├── style.css              # Stylesheet
├── add_slot.php           # Add parking slot form
└── uploads/               # Uploaded images directory

💡 Usage Guide
User Flow

    Register/Login - Create an account or login with existing credentials

    Browse Slots - Search for parking slots by location and vehicle type

    View Details - Click on slots to see images, location, pricing, and availability

    Book Slot - Select duration and confirm booking

    Vote - After parking, mark slots as "Parked" or "Full" to help others

    Manage Bookings - View and cancel bookings from "My Bookings" page

    Add Slot - List your own parking space to earn money

Admin Flow

    Admin Login - Access /admin_login.php with admin credentials

    Dashboard - View system statistics and recent activities

    Manage Users - View all users and delete problematic accounts

    Manage Slots - Monitor all parking slots across the system

    Manage Bookings - Track and manage all booking activities

    Manage Votes - Monitor community voting activity

    Settings - Change admin password and view database statistics

🎯 Key Features Explained
Community Voting System

    Users can vote "Parked" or "Full" for any slot

    Real-time status updates based on vote counts

    Prevents double-voting by same user

    Automatic removal of votes when booking is cancelled

Booking System

    Calculate total price based on duration

    Prevent double booking conflicts

    Automatic vote addition when booking confirmed

    Cancellation removes associated votes

Image Management

    Upload parking space photos

    Preview before upload

    Click to enlarge images

    Automatic file naming to prevent conflicts

Search & Filter

    Search across location, landmark, and address

    Filter by vehicle type (Car, Bike, Auto, Truck)

    Real-time results update

🔒 Security Features

    Password hashing using password_hash()

    Prepared statements to prevent SQL injection

    Session-based authentication

    File upload validation (type and size)

    XSS protection with htmlspecialchars()

    CSRF protection through session validation

📱 Responsive Design

    Fully responsive layout for mobile, tablet, and desktop

    Mobile-first CSS approach

    Touch-friendly buttons and interactions

    Adaptive grid layouts

🤝 Contributing

    Fork the repository

    Create your feature branch (git checkout -b feature/AmazingFeature)

    Commit your changes (git commit -m 'Add some AmazingFeature')

    Push to the branch (git push origin feature/AmazingFeature)

    Open a Pull Request

📝 License

This project is licensed under the MIT License - see the LICENSE file for details.
🙏 Acknowledgments

    Google Maps API for location integration

    Font Awesome for icons

    Unsplash for demo images

📧 Contact

For support or queries, please contact:

    Email: support@parkease.com

    Project Link: https://github.com/yourusername/parking-slot-sharing-system

🚧 Future Enhancements

    Payment gateway integration

    Email/SMS notifications

    QR code check-in/out

    Real-time chat between users

    Mobile app (React Native)

    Advanced analytics dashboard

    Vehicle number plate recognition

    Multi-language support

    Time-based pricing (peak hours)

    User rating system

    Parking space verification system
