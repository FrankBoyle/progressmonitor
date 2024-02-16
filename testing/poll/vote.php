<?php
include 'db.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log POST data
error_log("Received POST data: " . print_r($_POST, true));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn->begin_transaction();
    
    try {
        // Initialize $affectedItems as an empty array to avoid undefined variable errors
        $affectedItems = [];

        // Assuming you've received the item IDs for the 1st, 2nd, and 3rd place votes
        $firstPlaceVote = $_POST['first'] ?? null;
        $secondPlaceVote = $_POST['second'] ?? null;
        $thirdPlaceVote = $_POST['third'] ?? null;

        // Add received votes to $affectedItems only if they are not null
        if ($firstPlaceVote !== null) {
            $affectedItems[] = $firstPlaceVote;
        }
        if ($secondPlaceVote !== null) {
            $affectedItems[] = $secondPlaceVote;
        }
        if ($thirdPlaceVote !== null) {
            $affectedItems[] = $thirdPlaceVote;
        }

        // Now $affectedItems is guaranteed to be an array, so array_unique() will work
        foreach (array_unique($affectedItems) as $itemId) {
            // Update total_votes for each affected item
            // Ensure $itemId is not null before attempting to update
            if ($itemId) {
                $sql = "UPDATE items SET total_votes = (first_place_votes * 3 + second_place_votes * 2 + third_place_votes) WHERE id = ?";
                $stmt = $conn->prepare($sql);
                if ($stmt === false) {
                    error_log("Prepare error: " . $conn->error);
                } else {
                    $stmt->bind_param("i", $itemId);
                    if (!$stmt->execute()) {
                        error_log("Execute error: " . $stmt->error);
                    }
                }
            }
        }

        $conn->commit();
        echo "Votes updated successfully";
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
        // Log exception message
        error_log("Transaction rollback due to exception: " . $e->getMessage());
    }

    $conn->close();
}
?>



