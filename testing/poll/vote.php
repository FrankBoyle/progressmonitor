<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Extract the votes from the POST data
    $goldVote = $_POST['gold'];
    $silverVote = $_POST['silver'];
    $bronzeVote = $_POST['bronze'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update vote counts in a single transaction
        $conn->query("UPDATE items SET first_place_votes = first_place_votes + 1 WHERE id = $goldVote");
        $conn->query("UPDATE items SET second_place_votes = second_place_votes + 1 WHERE id = $silverVote");
        $conn->query("UPDATE items SET third_place_votes = third_place_votes + 1 WHERE id = $bronzeVote");

        // Commit the transaction
        $conn->commit();
        echo "Votes updated successfully";
    } catch (mysqli_sql_exception $exception) {
        // Rollback the transaction in case of an error
        $conn->rollback();
        echo "Error updating votes: " . $exception->getMessage();
    }

    $conn->close();
}
?>


