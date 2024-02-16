<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the medal type and the new and old item IDs from the request
    $newItemId = $_POST['newItemId'];
    $oldItemId = $_POST['oldItemId'];
    $medal = $_POST['medal']; // Expecting 'gold', 'silver', or 'bronze'

    // Start transaction
    $conn->begin_transaction();

    try {
        // Decrement the vote count for the old item if it exists
        if ($oldItemId) {
            $oldVoteColumn = $medal . "_place_votes";
            $sql = "UPDATE items SET $oldVoteColumn = $oldVoteColumn - 1 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $oldItemId);
            $stmt->execute();
        }

        // Increment the vote count for the new item
        $newVoteColumn = $medal . "_place_votes";
        $sql = "UPDATE items SET $newVoteColumn = $newVoteColumn + 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $newItemId);
        $stmt->execute();

        // If we reach this point without errors, commit the transaction
        $conn->commit();
        echo "Vote updated successfully";
    } catch (mysqli_sql_exception $exception) {
        // An error occurred, roll back the transaction
        $conn->rollback();
        echo "Error updating vote: " . $exception->getMessage();
    }

    // Close connection
    $conn->close();
}
?>
