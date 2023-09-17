This is the only part in view_student_data.php can I simplify this code somehow?


<?php
$studentId = $_GET['student_id'];

$stmt = $pdo->prepare("SELECT * FROM Performance WHERE student_id = ? ORDER BY week_start_date DESC LIMIT 41");  // you can change the LIMIT as needed
$stmt->execute([$studentId]);

$performanceData = $stmt->fetchAll();

echo "<table border='1'>";
echo "<tr><th>Week Start Date</th><th>Score1</th><th>Score2</th>...<th>Score10</th></tr>";  // Add more headers if needed

foreach ($performanceData as $data) {
    echo "<tr>";
    echo "<td>" . $data['week_start_date'] . "</td>";
    echo "<td>" . $data['score1'] . "</td>";
    echo "<td>" . $data['score2'] . "</td>";
	echo "<td>" . $data['score3'] . "</td>";
	echo "<td>" . $data['score4'] . "</td>";
	echo "<td>" . $data['score5'] . "</td>";
	echo "<td>" . $data['score6'] . "</td>";
	echo "<td>" . $data['score7'] . "</td>";
	echo "<td>" . $data['score8'] . "</td>";
	echo "<td>" . $data['score9'] . "</td>";
	echo "<td>" . $data['score10'] . "</td>";
    // ... continue for other scores
    echo "</tr>";
}

echo "</table>";
?>