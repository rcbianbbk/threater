<?php
include 'db.php';

// Disable time limit for heavy script execution
set_time_limit(300);

$genres = ['Action', 'Romance', 'Drama', 'Comedy', 'Thriller', 'Sci-Fi', 'Horror', 'Adventure', 'Animation', 'Biography'];
$statuses = ['now_showing', 'coming_soon'];

$english_titles = ['Starlight Odyssey', 'Shadow of the Empire', 'Echoes of Time', 'Neon City Nights', 'The Last Horizon', 'Beyond the Stars', 'Silent Witness', 'Cybernetic Dawn', 'Fading Memories', 'The Rogue Agent'];
$hindi_titles = ['Safar', 'Dil Ka Rishta', 'Aashiqui Forever', 'Zindagi Ke Rang', 'Raat Ka Saudagar', 'Kismat Ka Khel', 'Junoon', 'Khamoshiyan', 'Pyar Ka Safar', 'Toofaan'];
$nepali_titles = ['Himalayan Tale', 'Gauko Katha', 'Sunaulo Bihani', 'Pahad Ko Maya', 'Bandaki', 'Karma', 'Mero Desh', 'Sano Sansar', 'Ujhyalo', 'Bagmati Blues'];

$inserted_count = 0;

echo "<h2>Starting to insert 1,000 movies... Please wait.</h2>";

// Loop to generate 1,000 movies dynamically without duplicates
for ($i = 1; $i <= 1000; $i++) {
    // Pick category style
    $cat = $i % 3;
    if ($cat === 0) {
        $title = $english_titles[array_rand($english_titles)] . " " . $i;
        $desc = "An epic English cinematic drama filled with unexpected twists, intense emotions, and brilliant performances.";
    } elseif ($cat === 1) {
        $title = $hindi_titles[array_rand($hindi_titles)] . " " . $i;
        $desc = "A passionate and blockbuster Hindi romantic and emotional drama that captures the heart of viewers.";
    } else {
        $title = $nepali_titles[array_rand($nepali_titles)] . " " . $i;
        $desc = "A native Nepali masterpiece reflecting local culture, traditional values, and deep human struggles.";
    }

    $genre = $genres[array_rand($genres)] . ' / ' . $genres[array_rand($genres)];
    $duration = rand(100, 190);
    $price = rand(300, 500);
    $rating = round(rand(70, 95) / 10, 1);
    $poster = 'https://images.unsplash.com/photo-' . rand(1485000000000, 1550000000000) . '?w=600';
    $trailer = 'https://www.youtube.com/watch?v=d9MyW72ELq0';
    $status = $statuses[array_rand($statuses)];

    // Use INSERT IGNORE to prevent duplicate title errors
    $stmt = $conn->prepare("INSERT IGNORE INTO movies (title, description, genre, duration, price, rating, poster_image, trailer_url, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiidsss", $title, $description, $genre, $duration, $price, $rating, $poster, $trailer, $status);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $inserted_count++;
        }
    }
}

echo "<h3 style='color: green;'>Successfully added " . $inserted_count . " unique movies to your database!</h3>";
echo "<br><a href='index.php' style='padding: 10px 20px; background: #6366f1; color: white; text-decoration: none; border-radius: 5px;'>Go to Home Page</a>";
?>