<?php
// Database Configuration
$host = "127.0.0.1";
$user = "root";
$pass = "";
$db   = "theater_db"; // Yadi timro database 'theater_app' ho vane yeta 'theater_app' banau
$port = 3306;

// Connect without database first in case it needs to be created
$conn = @new mysqli($host, $user, $pass, "", $port);

if ($conn->connect_error) {
    die("<div style='font-family:sans-serif; background:#0b0f19; color:#f87171; padding:30px; text-align:center; min-height:100vh;'>
        <h2>Database Connection Error</h2>
        <p>Could not connect to MySQL server at {$host}:{$port}.</p>
        <p>Please ensure MySQL / XAMPP / WAMP is running.</p>
        <small style='color:#94a3b8;'>Error: " . htmlspecialchars($conn->connect_error) . "</small>
    </div>");
}

// Create database if not exists
$conn->query("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db($db);

// Helper function to check if column exists safely
function columnExists($conn, $table, $column) {
    $res = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $res && $res->num_rows > 0;
}

// Auto Schema & Migration Setup - Users table
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    profile_image VARCHAR(255) DEFAULT NULL,
    is_verified TINYINT(1) DEFAULT 1,
    otp VARCHAR(10) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Movies table
$conn->query("CREATE TABLE IF NOT EXISTS movies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    genre VARCHAR(100) NOT NULL,
    duration INT NOT NULL DEFAULT 120,
    price DECIMAL(10,2) NOT NULL DEFAULT 350.00,
    poster_image VARCHAR(500) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    rating DECIMAL(2,1) DEFAULT 4.5,
    trailer_url VARCHAR(500) DEFAULT NULL,
    show_times VARCHAR(255) DEFAULT '10:30 AM, 02:00 PM, 05:30 PM, 08:45 PM',
    status ENUM('now_showing', 'coming_soon') DEFAULT 'now_showing',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Bookings table with explicit primary key and columns to prevent 'Unknown column' errors
$conn->query("CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    movie_id INT NOT NULL,
    seat_number VARCHAR(100) NOT NULL,
    show_time VARCHAR(50) DEFAULT '05:30 PM',
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 350.00,
    payment_method VARCHAR(50) DEFAULT 'QR Code',
    payment_status VARCHAR(50) DEFAULT 'Success',
    transaction_id VARCHAR(100) DEFAULT NULL,
    ticket_code VARCHAR(50) DEFAULT NULL,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (movie_id) REFERENCES movies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Ensure default rich sample movies exist if database is fresh
$check_movies = $conn->query("SELECT COUNT(*) as cnt FROM movies");
if ($check_movies && $check_movies->fetch_assoc()['cnt'] == 0) {
    $conn->query("INSERT INTO movies (title, genre, duration, price, poster_image, description, rating, trailer_url, show_times, status) VALUES 
    ('Oppenheimer', 'Biography / Drama / History', 180, 400.00, 'https://images.unsplash.com/photo-1536440136628-849c177e76a1?w=800&auto=format&fit=crop&q=80', 'The story of American scientist J. Robert Oppenheimer and his role in the development of the atomic bomb.', 8.9, 'https://www.youtube.com/watch?v=uYPbbksJxIg', '11:00 AM, 02:45 PM, 06:30 PM, 10:00 PM', 'now_showing'),
    ('Dune: Part Two', 'Action / Adventure / Sci-Fi', 166, 450.00, 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?w=800&auto=format&fit=crop&q=80', 'Paul Atreides unites with Chani and the Fremen while seeking revenge against the conspirators who destroyed his family.', 8.8, 'https://www.youtube.com/watch?v=Way9Dexny3w', '10:00 AM, 01:30 PM, 05:00 PM, 08:30 PM', 'now_showing'),
    ('Spider-Man: Across the Spider-Verse', 'Animation / Action / Adventure', 140, 380.00, 'https://images.unsplash.com/photo-1635805737707-575885ab0820?w=800&auto=format&fit=crop&q=80', 'Miles Morales catapults across the Multiverse, where he encounters a team of Spider-People charged with protecting its very existence.', 8.7, 'https://www.youtube.com/watch?v=cqGjhVJWtEg', '11:30 AM, 03:00 PM, 06:15 PM, 09:15 PM', 'now_showing'),
    ('Interstellar Remastered', 'Adventure / Drama / Sci-Fi', 169, 420.00, 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=800&auto=format&fit=crop&q=80', 'When Earth becomes uninhabitable in the future, a farmer and ex-NASA pilot is tasked to pilot a spacecraft to find a new planet.', 8.7, 'https://www.youtube.com/watch?v=zSWdZVtXT7E', '12:00 PM, 04:00 PM, 07:45 PM', 'now_showing')
    ");
}
?>