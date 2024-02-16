<?php
include 'db.php';

$sql = "SELECT id, name, first_place_votes, second_place_votes, third_place_votes, (first_place_votes * 3 + second_place_votes * 2 + third_place_votes) AS total_votes FROM items ORDER BY total_votes DESC";
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

