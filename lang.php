<?php
// कुकिजबाट भाषा चेक गर्ने, छैन भने डिफल्ट 'en' (English) राख्ने
$lang = isset($_COOKIE['user_lang']) ? $_COOKIE['user_lang'] : 'en';

$translations = [
    'en' => [
        'welcome' => 'Welcome to Cinema World',
        'select_lang' => 'Please select your language.',
        'featured_premiere' => 'FEATURED PREMIERE',
        'book_tickets' => 'Book Tickets Now',
        'watch_trailer' => 'Watch Trailer',
        'laser_cinema' => '4K Laser Cinema',
        'laser_desc' => 'Crystal ultra-sharp projection',
        'dolby_atmos' => 'Dolby Atmos 360°',
        'dolby_desc' => 'Spatial surround soundscape',
        'luxury_recliners' => 'Luxury Recliners',
        'recliners_desc' => 'Ergonomic VIP comfort',
        'instant_tickets' => 'Instant E-Tickets',
        'tickets_desc' => 'Fast QR contactless check-in',
        'explore_movies' => 'Explore Movies',
        'explore_desc' => 'Select your movie, choose your seats, and enjoy the show',
        'search_placeholder' => 'Search by title, genre...',
        'now_showing' => 'NOW SHOWING',
        'coming_soon' => 'COMING SOON',
        'upcoming_blockbusters' => 'Upcoming Blockbusters',
        'upcoming_desc' => 'Get ready for the most anticipated releases',
        'no_movies' => 'No Movies Currently Showing',
        'check_back' => 'Please check back soon or explore our upcoming titles.',
        'no_match' => 'No matching movies found',
        'try_searching' => 'Try searching with different keywords or clearing the genre filter.',
        'all' => 'All'
    ],
    'np' => [
        'welcome' => 'सिनेमा वर्ल्डमा स्वागत छ',
        'select_lang' => 'कृपया आफ्नो भाषा छान्नुहोस्।',
        'featured_premiere' => 'विशेष चलचित्र (Featured)',
        'book_tickets' => 'टिकट बुक गर्नुहोस्',
        'watch_trailer' => 'ट्रेलर हेर्नुहोस्',
        'laser_cinema' => '४के लेजर सिनेमा',
        'laser_desc' => 'अति स्पष्ट र सफा पर्दा',
        'dolby_atmos' => 'डल्बी एट्मस ३६०°',
        'dolby_desc' => 'चारैतिरबाट सुनिने उत्कृष्ट साउन्ड',
        'luxury_recliners' => 'आरामदायी सिटहरू',
        'recliners_desc' => 'भिआइपी आरामको अनुभव',
        'instant_tickets' => 'छिटो इ-टिकट',
        'tickets_desc' => 'सजिलो क्युआर (QR) चेक-इन',
        'explore_movies' => 'चलचित्रहरू हेर्नुहोस्',
        'explore_desc' => 'आफ्नो मनपर्ने चलचित्र छान्नुहोस् र सिट बुक गर्नुहोस्',
        'search_placeholder' => 'चलचित्रको नाम वा विधा खोज्नुहोस्...',
        'now_showing' => 'अहिले प्रदर्शन भइरहेको',
        'coming_soon' => 'छिट्टै आउँदैछ',
        'upcoming_blockbusters' => 'आगामी चलचित्रहरू',
        'upcoming_desc' => 'सबैभन्दा प्रतिक्षित चलचित्रहरूको लागि तयार हुनुहोस्',
        'no_movies' => 'हाल कुनै चलचित्र उपलब्ध छैन',
        'check_back' => 'कृपया पछि फेरि हेर्नुहोला वा आउँदै गरेका चलचित्रहरू हेर्नुहोस्।',
        'no_match' => 'कुनै चलचित्र फेला परेन',
        'try_searching' => 'कृपया अर्को शब्द राखेर खोजी गर्नुहोस्।',
        'all' => 'सबै'
    ]
];

// शब्द छान्ने फंक्सन
function t($key) {
    global $lang, $translations;
    return isset($translations[$lang][$key]) ? $translations[$lang][$key] : $translations['en'][$key];
}
?>