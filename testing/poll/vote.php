<?php
include 'db.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log POST data
error_log("Received POST data: " . print_r($_POST, true));

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Assuming you've received the item IDs for the 1st, 2nd, and 3rd place votes
        $firstPlaceVote = $_POST['first'] ?? null;
        $secondPlaceVote = $_POST['second'] ?? null;
        $thirdPlaceVote = $_POST['third'] ?? null;

        // Update first_place_votes
        if (!empty($firstPlaceVote)) {
            $sql = "UPDATE items SET first_place_votes = first_place_votes + 1 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $firstPlaceVote);
            $stmt->execute();
        }

        // Update second_place_votes
        if (!empty($secondPlaceVote)) {
            $sql = "UPDATE items SET second_place_votes = second_place_votes + 1 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $secondPlaceVote);
            $stmt->execute();
        }

        // Update third_place_votes
        if (!empty($thirdPlaceVote)) {
            $sql = "UPDATE items SET third_place_votes = third_place_votes + 1 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $thirdPlaceVote);
            $stmt->execute();
        }

        // Commit the transaction
        $conn->commit();
        echo "Votes updated successfully";
    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
        // Log exception message
        error_log("Transaction rollback due to exception: " . $e->getMessage());
    }

    $conn->close(); // Close the database connection
}
?>



