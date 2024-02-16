<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Extract the item IDs for the 1st, 2nd, and 3rd place votes from the POST data
    $firstPlaceVote = $_POST['first'] ?? null;
    $secondPlaceVote = $_POST['second'] ?? null;
    $thirdPlaceVote = $_POST['third'] ?? null;

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Update first_place_votes
        if ($firstPlaceVote) {
            $sql = "UPDATE items SET first_place_votes = first_place_votes + 1 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $firstPlaceVote);
            $stmt->execute();
        }

        // Update second_place_votes
        if ($secondPlaceVote) {
            $sql = "UPDATE items SET second_place_votes = second_place_votes + 1 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $secondPlaceVote);
            $stmt->execute();
        }

        // Update third_place_votes
        if ($thirdPlaceVote) {
            $sql = "UPDATE items SET third_place_votes = third_place_votes + 1 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $thirdPlaceVote);
            $stmt->execute();
        }

        // Optionally, update total_votes here if you're not calculating it dynamically in getItems.php
        $affectedItems = array_filter([$firstPlaceVote, $secondPlaceVote, $thirdPlaceVote]);
        foreach ($affectedItems as $itemId) {
            $sql = "UPDATE items SET total_votes = (first_place_votes * 3 + second_place_votes * 2 + third_place_votes) WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $itemId);
            $stmt->execute();
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


