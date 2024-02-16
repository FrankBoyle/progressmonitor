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
        $firstPlaceVote = $_POST['first'];
        $secondPlaceVote = $_POST['second'];
        $thirdPlaceVote = $_POST['third'];

        // IDs for updating total_votes later
        $affectedItems = [$firstPlaceVote, $secondPlaceVote, $thirdPlaceVote];
        
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

        // Update total_votes for affected items
        foreach (array_unique($affectedItems) as $itemId) {
            $sql = "UPDATE items SET total_votes = (first_place_votes * 3 + second_place_votes * 2 + third_place_votes) WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $itemId);
            $stmt->execute();
        }

        $conn->commit();
        echo "Votes updated successfully";
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }

    $conn->close();
}
?>


