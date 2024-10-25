<?php
require_once("databaseConnection.php");

function saveScratchItems($scratchItems) {
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
    "Knuffel Night", "20 ChikkenNuggets", "Movie night", "Cook Night", // etc.
];
saveScratchItems($scratchItems);
?>