<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Assuming you've received the item IDs for the 1st, 2nd, and 3rd place votes
        $firstPlaceVote = $_POST['first'];
        $secondPlaceVote = $_POST['second'];
        $thirdPlaceVote = $_POST['third'];

        // Update vote counts as needed (shown here as an example)
        // Note: Actual vote updating logic should be implemented based on your application's requirements

        // Placeholder for updating votes (not shown for brevity)

        // Update total_votes for each affected item
        $affectedItems = [$firstPlaceVote, $secondPlaceVote, $thirdPlaceVote];
        foreach ($affectedItems as $itemId) {
            if ($itemId) { // Check if itemId is not null or empty
                $sql = "UPDATE items SET total_votes = (first_place_votes * 3 + second_place_votes * 2 + third_place_votes) WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $itemId);
                $stmt->execute();
            }
        }

        // Commit the transaction
        $conn->commit();
        echo "Votes updated successfully";
    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }

    $conn->close();
}
?>


