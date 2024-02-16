<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Extract the votes from the POST data
    $firstPlaceVote = isset($_POST['first']) ? intval($_POST['first']) : null;
    $secondPlaceVote = isset($_POST['second']) ? intval($_POST['second']) : null;
    $thirdPlaceVote = isset($_POST['third']) ? intval($_POST['third']) : null;

    // Begin transaction to ensure atomicity
    $conn->begin_transaction();

    try {
        // Increment the vote count for the first place vote
        if ($firstPlaceVote) {
            $sql = "UPDATE items SET first_place_votes = first_place_votes + 1 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $firstPlaceVote);
            $stmt->execute();
        }

        // Increment the vote count for the second place vote
        if ($secondPlaceVote) {
            $sql = "UPDATE items SET second_place_votes = second_place_votes + 1 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $secondPlaceVote);
            $stmt->execute();
        }

        // Increment the vote count for the third place vote
        if ($thirdPlaceVote) {
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
    }

    $conn->close();
}
?>

