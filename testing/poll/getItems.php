<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

include 'db.php';

$sql = "SELECT id, name, first_place_votes, second_place_votes, third_place_votes FROM items ORDER BY (3 * first_place_votes + 2 * second_place_votes + third_place_votes) DESC";
$result = $conn->query($sql);

$items = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
}
echo json_encode($items);

$conn->close();
?>

