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
        $firstPlaceVote = $_POST['first'] ?? null;
        $secondPlaceVote = $_POST['second'] ?? null;
        $thirdPlaceVote = $_POST['third'] ?? null;

        // Log the IDs for debugging
        error_log("Voting IDs - First: $firstPlaceVote, Second: $secondPlaceVote, Third: $thirdPlaceVote");
        
        // Update first_place_votes
        if ($firstPlaceVote) {
            $sql = "UPDATE items SET first_place_votes = first_place_votes + 1 WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("i", $firstPlaceVote);
                if (!$stmt->execute()) {
                    // Log SQL execution error
                    error_log("Execute error for first_place_votes: " . $stmt->error);
                }
            } else {
                // Log SQL preparation error
                error_log("Prepare error for first_place_votes: " . $conn->error);
            }
        }
        
        // Similar blocks for secondPlaceVote and thirdPlaceVote...

        // Update total_votes for affected items
        foreach (array_unique($affectedItems) as $itemId) {
            if ($itemId) {
                $sql = "UPDATE items SET total_votes = (first_place_votes * 3 + second_place_votes * 2 + third_place_votes) WHERE id = ?";
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("i", $itemId);
                    if (!$stmt->execute()) {
                        // Log SQL execution error for total_votes
                        error_log("Execute error for total_votes: " . $stmt->error);
                    }
                } else {
                    // Log SQL preparation error for total_votes
                    error_log("Prepare error for total_votes: " . $conn->error);
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


