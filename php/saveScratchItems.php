<?php
require_once("assets/php/databaseConnection.php");

function saveScratchItems($scratchItems)
{
    $pdo = connect_to_database();
    $stmt = $pdo->prepare("INSERT INTO scratings (scratching_name, scratching_scratchDate, scratching_isScratched) VALUES (:name, :date, :isScratched)");

    foreach ($scratchItems as $item) {
        $stmt->execute([
            ':name' => $item,
            ':date' => null, // initially null
            ':isScratched' => 0 // initially not scratched
        ]);
    }
}

// Example usage
$scratchItems = [
    "Knuffel Night",
    "20 ChikkenNuggets",
    "Movie night",
    "Cook Night",
    "10 ChikkenNuggets",
    "mcdo date",
    "regular date",
    "1 burger",
    "mcdo date",
    "1 makeup date",
    "Knuffel Night",
    "1 supprise date",
    "1 Free candy",
    "Free kisses",
    "1 sexy date",
    "1 Free candy",
    "Free kisses",
    "1 sexy date",
    "20 ChikkenNuggets",
    "10 ChikkenNuggets",
    "1 burger",
    "regular date",
    "Cook Night",
    "pari daiza date",
    "1 makeup date",
    "1 tiktok video opname",
    "1 tiktok video opname",
];
saveScratchItems($scratchItems);
