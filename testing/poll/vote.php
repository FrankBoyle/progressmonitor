<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $position = $_POST['position']; // Expecting 'first', 'second', or 'third'

    $voteColumn = $position . "_place_votes";
    $sql = "UPDATE items SET $voteColumn = $voteColumn + 1 WHERE id = $id";

    if ($conn->query($sql) === TRUE) {
        echo "Vote recorded successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
