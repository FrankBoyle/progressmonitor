<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $newItemId = $_POST['newItemId']; // The ID of the item receiving the new vote
    $medalType = $_POST['medalType']; // 'first', 'second', or 'third'

    // Begin transaction to ensure atomicity
    $conn->begin_transaction();

    try {
        // Update the vote count for the new item
        $voteColumn = $medalType . "_place_votes";
        $sql = "UPDATE items SET $voteColumn = $voteColumn + 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $newItemId);
        $stmt->execute();

        // Commit the transaction
        $conn->commit();
        echo "Vote recorded successfully";
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }

    // Fetch and output the updated list of items
    $sql = "SELECT id, name, first_place_votes, second_place_votes, third_place_votes FROM items ORDER BY (3*first_place_votes + 2*second_place_votes + third_place_votes) DESC";
    $result = $conn->query($sql);

    $items = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
    }
    echo json_encode($items);

    $conn->close();
}
?>

